<?php


namespace common\services;

use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\Order;
use common\utils\StringUtils;
use yii\db\Query;

class GroupRoomOrderService
{

    /**
     * 根据订单号查团订单
     * @param $orderNo
     * @param null $companyId
     * @param false $model
     * @return array|bool|GroupRoomOrder|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($orderNo,$companyId=null,$model=false){
        $conditions = ['order_no' => $orderNo];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return GroupRoomOrder::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GroupRoomOrder::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getActiveModels($orderNoes,$companyId=null){
        $conditions = ['order_no' => $orderNoes];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        return (new Query())->from(GroupRoomOrder::tableName())->where($conditions)->all();
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
        $conditions = ['active_no'=>$activeNo];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $roomList = GroupRoom::find()->where($conditions)->with('groupRoomOrders');
        if (StringUtils::isNotBlank($pageNo)&&StringUtils::isNotBlank($pageSize)){
            $roomList->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $roomList->orderBy('id desc');
        return $roomList->asArray()->all();
    }

    /**
     *
     * @param $roomNo
     * @param null $companyId
     * @param null $orderStatus
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRoomOrders($roomNo, $companyId=null, $orderStatus=null){
        $orderTable = Order::tableName();
        $groupRoomOrderTable = GroupRoomOrder::tableName();
        $conditions = ["{$groupRoomOrderTable}.room_no"=>$roomNo];
        if (StringUtils::isNotBlank($companyId)){
            $conditions["{$groupRoomOrderTable}.company_id"] = $companyId;
        }
        if (StringUtils::isNotBlank($orderStatus)){
            $conditions["{$orderTable}.order_status"] = $orderStatus;
        }
        $orderList = GroupRoomOrder::find()->select(["{$orderTable}.*","{$groupRoomOrderTable}.*"])
            ->leftJoin($orderTable,"{$orderTable}.order_no={$groupRoomOrderTable}.order_no")
            ->where($conditions)->asArray()->all();
        return $orderList;
    }

}