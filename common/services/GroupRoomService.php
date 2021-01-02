<?php


namespace common\services;


use common\models\GoodsConstantEnum;
use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\Order;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GroupRoomService
{

    public static function getActiveModel($id=null,$roomNo=null,$companyId=null,$model=false){
        $conditions = [];
        if (StringUtils::isNotBlank($id)){
            $conditions['id'] = $id;
        }
        if (StringUtils::isNotBlank($roomNo)){
            $conditions['room_no'] = $roomNo;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return GroupRoom::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GroupRoom::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }



    /**
     * 房间列表&订单详情
     * @param $activeNo
     * @param $companyId
     * @param int $pageNo
     * @param int $pageSize
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRoomListAndOrders($activeNo, $companyId, $pageNo=1, $pageSize=20)
    {
        $conditions = ['and',['active_no'=>$activeNo]];
        if (StringUtils::isNotBlank($companyId)){
            $conditions[] = ['company_id'=>$companyId];
        }
        //必须团长支付了才能Join
        $conditions[] = ['>=','paid_order_count',1];
        $roomListQuery = GroupRoom::find()->where($conditions)->with('groupRoomOrders');
        if (StringUtils::isNotBlank($pageNo)&&StringUtils::isNotBlank($pageSize)){
            $roomListQuery->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $roomList = $roomListQuery->orderBy('id desc')->asArray()->all();
        $roomList = self::batchDisplayVO($roomList);
        $roomList = self::completeTeamInfo($roomList);
        return $roomList;
    }




    /**
     *
     * @param $roomNo
     * @param $companyId
     * @return mixed|null
     */
    public static function getRoomDetail($roomNo, $companyId=null)
    {
        $conditions = ['room_no'=>$roomNo];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $roomListQuery = GroupRoom::find()->where($conditions)->with(['groupRoomOrders']);
        $roomList = $roomListQuery->asArray()->all();
        $roomList = self::batchDisplayVO($roomList);
        $roomList = self::completeTeamInfo($roomList);
        $roomList = self::completeTeamMemberInfo($roomList);
        $roomList = self::completeSchedulePrice($roomList);
        return count($roomList)>0?$roomList[0]:null;
    }

    /**
     * @param $roomList
     * @return array
     */
    public static function completeSchedulePrice($roomList){
        if (empty($roomList)){
            return [];
        }
        $roomNos = ArrayUtils::getColumnWithoutNull('room_no',$roomList);
        $roomScheduleMap = self::getSchedulePrice($roomNos);
        foreach ($roomList as &$v){
            $v['schedule_amount'] = ArrayUtils::getArrayValue($v['room_no'],$roomScheduleMap,0);
        }
        return $roomList;
    }

    public static function getSchedulePrice($roomNos){
        if (empty($roomNos)){
            return [];
        }
        $groupRoomTable = GroupRoom::tableName();
        $groupRoomOrderTable = GroupRoomOrder::tableName();
        $res = (new Query())->from($groupRoomTable)->leftJoin($groupRoomOrderTable,"{$groupRoomTable}.room_no={$groupRoomOrderTable}.room_no")
            ->select(["{$groupRoomTable}.room_no","{$groupRoomOrderTable}.schedule_amount"])
            ->where(["{$groupRoomTable}.room_no"=>$roomNos])->groupBy("{$groupRoomTable}.room_no")->all();
        $res = ArrayUtils::map($res,'room_no','schedule_amount');
        $resMap = [];
        foreach ($roomNos as $roomNo){
            $resMap[$roomNo] = ArrayUtils::getArrayValue($roomNo,$res,0);
        }
        return $resMap;
    }


    /**
     * @param $roomNoes
     * @param null $companyId
     * @return array
     */
    public static function getRoomsDetail($roomNoes, $companyId=null)
    {
        $conditions = ['room_no'=>$roomNoes];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $roomListQuery = GroupRoom::find()->where($conditions)->with(['groupRoomOrders']);
        $roomList = $roomListQuery->asArray()->all();
        $roomList = self::batchDisplayVO($roomList);
        $roomList = self::completeTeamInfo($roomList);
        //$roomList = self::completeTeamMemberInfo($roomList);
        return $roomList;
    }


    /**
     * 补全团长头像信息
     * @param $roomList
     * @return array
     */
    public static function completeTeamInfo($roomList){
        if (empty($roomList)){
            return[];
        }
        $customerIds = ArrayUtils::getColumnWithoutNull('team_id',$roomList);
        $customerIds = array_unique($customerIds);
        $customerBaseInfo = CustomerService::getBaseInfoWithHeadImageUrl($customerIds);
        foreach ($roomList as &$room){
            $room['team_head_img_url'] = ArrayUtils::getArrayValue('head_img_url',ArrayUtils::getArrayValue($room['team_id'],$customerBaseInfo,[]),'');
        }
        return $roomList;
    }

    /**
     * 补全团员基本信息
     * @param $roomList
     * @return array
     */
    public static function completeTeamMemberInfo($roomList){
        if (empty($roomList)){
            return[];
        }
        $customerIds = [];
        foreach ($roomList as $room){
            if (key_exists('groupRoomOrders',$room)){
                $tempCustomerIds = ArrayUtils::getColumnWithoutNull('customer_id',$room['groupRoomOrders']);
                $customerIds = array_merge($customerIds,$tempCustomerIds);
            }
        }
        $customerIds = array_unique($customerIds);
        $customerBaseInfo = CustomerService::getBaseInfoWithHeadImageUrl($customerIds);
        foreach ($roomList as &$room){
            if (key_exists('groupRoomOrders',$room)){
                foreach ($room['groupRoomOrders'] as &$groupRoomOrder){
                    if (key_exists($groupRoomOrder['customer_id'],$customerBaseInfo)){
                        $groupRoomOrder['customer_nickname'] = $customerBaseInfo[$groupRoomOrder['customer_id']]['nickname'];
                        $groupRoomOrder['customer_head_img_url'] =  $customerBaseInfo[$groupRoomOrder['customer_id']]['head_img_url'];
                    }
                    else{
                        $groupRoomOrder['customer_nickname'] = '';
                        $groupRoomOrder['customer_head_img_url'] = '';
                    }
                }
            }
        }
        return $roomList;
    }


    public static function batchDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=> $v){
            $list[$k] = self::displayVO($v);
        }
        return $list;
    }



    public static function displayVO($model){
        if (empty($model)){
            return [];
        }
        $model = self::setDisplayStatus($model);
        $model = self::defineCanJoin($model);
        $model = self::defineCanShare($model);
        return $model;
    }


    private static function setDisplayStatus($model){
        if ($model['status'] == GroupRoom::DISPLAY_STATUS_SUCCESS) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_SUCCESS;
        }
        else if ($model['status'] == GroupRoom::DISPLAY_STATUS_FAILED) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_FAILED;
        }
        else if ($model['paid_order_count'] < 1) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_REMAINING;
        }
        else if ($model['paid_order_count'] >= $model['max_level']) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_SUCCESS;
        } else if ($model['place_count'] >= $model['max_level']) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_REMAINING;
        } else if (self::roomIsNotTimeOut($model['expect_finished_at'])) {
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_PROCESSING;
        } else if ($model['paid_order_count'] >= $model['min_level']){
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_SUCCESS;
        }
        else{
            $model['displayStatus'] = GroupRoom::DISPLAY_STATUS_FAILED;
        }
        if (StringUtils::isBlank($model['finished_at'])){
            $model['finished_at'] = DateTimeUtils::plusMinute($model['created_at'],$model['continued']);
        }
        $model['displayStatusTextForReadyBuyer'] = ArrayUtils::getArrayValue($model['displayStatus'],GroupRoom::$displayStatusTextForReadyBuyer);
        return $model;
    }


    private static function defineCanJoin($model){
        if (in_array($model['displayStatus'],[GroupRoom::DISPLAY_STATUS_PROCESSING])){
            $model['canJoin'] = GroupRoom::CAN_SHARE;
        }
        else{
            $model['canJoin'] = GroupRoom::CAN_NOT_SHARE;
        }
        return $model;
    }

    /**
     * 定义是否还有必要分享
     * @param $model
     * @return mixed
     */
    private static function defineCanShare($model){
        if (in_array($model['displayStatus'],[GroupRoom::DISPLAY_STATUS_PROCESSING,GroupRoom::DISPLAY_STATUS_REMAINING])){
            $model['canShare'] = GroupRoom::CAN_SHARE;
        }
        else{
            $model['canShare'] = GroupRoom::CAN_NOT_SHARE;
        }
        return $model;
    }


    public static function roomIsNotTimeOut($expectFinishedAt){
        return strtotime($expectFinishedAt)>=time();
    }

    /**
     * 判断是否能下单
     * @param $model
     * @return array
     */
    public static function canMoreOrder($model){
        if ($model['status'] == GroupRoom::DISPLAY_STATUS_SUCCESS) {
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_SUCCESS,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
        else if ($model['status'] == GroupRoom::DISPLAY_STATUS_FAILED) {
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_FAILED,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
        else if ($model['paid_order_count'] < 1) {
            return [false,'团长还未支付，暂不能参团'];
        }
        else if ($model['paid_order_count'] >= $model['max_level']) {
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_SUCCESS,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
        else if ($model['place_count'] >= $model['max_level']) {
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_REMAINING,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
        else if (self::roomIsNotTimeOut($model['expect_finished_at'])) {
            return [true,''];
        }
        else if ($model['paid_order_count'] >= $model['min_level']){
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_SUCCESS,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
        else{
            return [false,ArrayUtils::getArrayValue( GroupRoom::DISPLAY_STATUS_FAILED,GroupRoom::$displayStatusTextForReadyBuyer)];
        }
    }


    /**
     * @param $orderNo
     * @param null $companyId
     * @return array
     */
    public static function getRoomAndActiveByOrderNo($orderNo, $companyId=null){
        $roomOrder = GroupRoomOrderService::getActiveModel($orderNo,$companyId);
        if (empty($roomOrder)){
            return [];
        }
        $groupRoom = self::getRoomDetail($roomOrder['room_no'],$companyId);
        $groupActive = GroupActiveService::getActiveModelWithSchedule(null,$groupRoom['active_no'],$companyId);
        return [
            'groupRoom'=>$groupRoom,
            'groupActive' => $groupActive
        ];
    }


    /**
     * @param $activeNo
     * @return array
     */
    public static function getRoomStatistic($activeNo){
        $success = GroupRoom::find()->where(['active_no'=>$activeNo,'status'=>GroupRoom::GROUP_STATUS_SUCCESSFUL])->count();
        $processing = GroupRoom::find()->where([
            'and',
            [
                'active_no'=>$activeNo,
                'status'=>GroupRoom::GROUP_STATUS_PROCESSING,
            ],
            ['>','paid_order_count',0]
        ])->count();
        return [
            'success'=>$success,
            'processing'=>$processing,
        ];
    }

    /**
     * 校验只允许下一个有效单
     * @param $roomNo
     * @param $customerId
     * @param $orderNo
     * @return array
     */
    public static function validateJustOrderOneOrder($roomNo, $customerId,$orderNo){
        $roomOrderTable = GroupRoomOrder::tableName();
        $orderTable = Order::tableName();
        $orders = (new Query())->from($roomOrderTable)->leftJoin($orderTable,"{$roomOrderTable}.order_no={$orderTable}.order_no")
            ->select(["{$orderTable}.*"])
            ->where(['and',
                [
                    "{$roomOrderTable}.customer_id"=>$customerId,
                    "{$roomOrderTable}.room_no"=>$roomNo
                ]
            ])->all();
        if (empty($orders)){
            return [true,""];
        }
        foreach ($orders as $order){
            if ($orderNo==$order['order_no']){
                continue;
            }
            if ($order['order_status']==Order::ORDER_STATUS_UN_PAY){
                return [false,"有待支付的订单，不能重复参团"];
            }
            else if (in_array($order['order_status'],[Order::ORDER_STATUS_CANCELED,Order::ORDER_STATUS_CANCELING])){
                if ($order['pay_status']!=Order::PAY_STATUS_UN_PAY){
                    return [false,"支付后取消不能重复参团"];
                }
            }
            else{
                return [false,"已成功参团，不能重复参团"];
            }
        }
        return [true,""];
    }


    public static function batchSetRoomOwnerTagForOrder($list){
        if (empty($list)){
            return[];
        }
        foreach ($list as $k=>$v){
            $v = self::setRoomOwnerTagForOrder($v);
            $list[$k] = $v;
        }
        return $list;
    }

    public static function setRoomOwnerTagForOrder($order){
        if ($order['order_type']==GoodsConstantEnum::TYPE_GROUP_ACTIVE){
            if (!empty($order['room'])){
                $order['room']['isRoomOwner'] = $order['customer_id']==$order['room']['team_id'];
            }
        }
        return $order;
    }


}