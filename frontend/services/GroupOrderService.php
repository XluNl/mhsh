<?php


namespace frontend\services;


use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\GroupActive;
use common\models\GroupRoom;
use common\models\GroupRoomOrder;
use common\models\GroupRoomWaitRefundOrder;
use common\models\Order;
use common\models\OrderLogs;
use common\services\GroupActiveService;
use common\services\GroupRoomOrderService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;


class GroupOrderService extends \common\services\GroupOrderService
{

    /**
     * 校验拼团操作行为
     * @param $groupOrderType
     */
    public static function checkGroupOrderType($groupOrderType){
        ExceptionAssert::assertNotNull($groupOrderType,StatusCode::createExp(StatusCode::GROUP_ORDER_TYPE_NOT_ALLOW));
        ExceptionAssert::assertTrue(in_array($groupOrderType,array_keys(GroupRoom::$groupOrderTypeArr)),StatusCode::createExp(StatusCode::GROUP_ORDER_TYPE_NOT_ALLOW));
    }


    /**
     * 返回ownerType and ownerId
     * @param $skuList
     * @return array
     */
    public static function getOwnerTyp($skuList){
        $firstKey = ArrayUtils::getFirstKeyFromArray($skuList);
        return [$skuList[$firstKey]['goods_owner'],$skuList[$firstKey]['goods_owner_id']];
    }
    /**
     * 计算价格、节省金额
     * @param $companyId
     * @param $activeNo
     * @param $num
     * @return array
     */
    public static function calculatePrice($companyId, $activeNo, $num){
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $skuList = [$activeNo=>$num];
        $goods_total = $num;
        $price_total = 0;
        if (!empty($skuList)){
            $skuModels = GoodsScheduleService::getGroupActiveSoldUpByIds($activeNo,null,$companyId,$deliveryId);
            $skuModels = ArrayUtils::index($skuModels,'active_no');
            foreach ($skuList as $key => $value) {
                if (ArrayHelper::keyExists($key,$skuModels)){
                    $model = $skuModels[$key];
                    $price_total += $model['price'] * $value;
                    $goods = $model;
                    $goods["num"] = $value;
                    $skuList[$key] = $goods;
                }
                else{
                    unset($skuList[$key]);
                }
            }
        }
        return [$price_total,$goods_total,$skuList];
    }

    /**
     * 校验空sku
     * @param $skuList
     */
    public static function checkSkuListNotEmpty($skuList)
    {
        ExceptionAssert::assertTrue(!empty($skuList), StatusCode::createExp(StatusCode::GOODS_NOT_EXIST));
    }

    /**
     * 判断团的状态（入团模式）
     * @param $groupOrderType
     * @param $roomNo
     * @param $companyId
     */
    public static function checkRoomsStatus($groupOrderType, $roomNo,$companyId)
    {
        if ($groupOrderType==GroupRoom::GROUP_ORDER_TYPE_JOIN){
            ExceptionAssert::assertNotBlank($roomNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'roomNo'));
            $groupRoom = GroupRoomService::getActiveModel(null,$roomNo,$companyId);
            ExceptionAssert::assertNotNull($groupRoom, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '团不存在'));
            list($res,$error) = GroupRoomService::canMoreOrder($groupRoom);
            ExceptionAssert::assertTrue($res, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, $error));
        }
    }


    /**
     * 创建订单
     * @param $activeNo
     * @param $roomNo
     * @param $groupOrderType
     * @param $companyId
     * @param $userId
     * @param $cModel
     * @param $skuList
     * @param $coupon_no
     * @param $deliveryType
     * @param $addressId
     * @param $orderNote
     * @return array
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public static function createOrder($activeNo, $roomNo,$groupOrderType,$companyId,$userId,$cModel, $skuList, $coupon_no, $deliveryType, $addressId,$orderNote){
        list($ownerType,$ownerId) = self::getOwnerTyp($skuList);
        ExceptionAssert::assertTrue(in_array($ownerType,array_keys(GoodsConstantEnum::$ownerArr)),StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'未知的ownerType'));
        $transaction = Yii::$app->db->beginTransaction();
        $orderNo = "";
        try {
            if (in_array($ownerType,[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY])){
                $orderNo = OrderService::createOrder($companyId,$userId,$cModel, $skuList, $coupon_no, $deliveryType, $addressId,$orderNote,$ownerType,GoodsConstantEnum::TYPE_GROUP_ACTIVE);
            }
            else if (in_array($ownerType,[GoodsConstantEnum::OWNER_HA])){
                //获取异业联盟点信息
                $alliance = AllianceOrderService::checkAllianceStatus($companyId,$skuList);
                $orderNo = AllianceOrderService::createOrder($companyId, $cModel, $skuList, $coupon_no, $deliveryType, $addressId, $orderNote, $alliance,GoodsConstantEnum::TYPE_GROUP_ACTIVE);
            }

            $roomNo = self::createOrJoinRoom($activeNo,$roomNo,$cModel['id'],$orderNo,$skuList,$companyId,$groupOrderType);
            $transaction->commit();

            return [
                'orderNo'=>$orderNo,
                'roomNo'=>$roomNo,
            ];
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            \yii::error($e->getMessage());
            throw $e;
        }
        catch (Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            throw StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,$e->getMessage());
        }
    }


    /**
     * 创建或加入团
     * @param $activeNo
     * @param $roomNo
     * @param $customerId
     * @param $orderNo
     * @param $skuList
     * @param $companyId
     * @param $groupOrderType
     * @return mixed|string
     * @throws BusinessException
     */
    public static function createOrJoinRoom($activeNo, $roomNo, $customerId, $orderNo,$skuList, $companyId, $groupOrderType)
    {
        $schedulePrice = ArrayUtils::getFirstValueFromArray($skuList)['price'];
        if ($groupOrderType == GroupRoom::GROUP_ORDER_TYPE_JOIN) {
            return self::joinRoom($activeNo, $roomNo, $customerId, $orderNo,$schedulePrice, $companyId);
        }
        else if ($groupOrderType == GroupRoom::GROUP_ORDER_TYPE_NEW){
            return self::createRoom($activeNo, $customerId, $orderNo,$schedulePrice, $companyId);
        }
        else{
            throw BusinessException::create("未知的拼团下单方式");
        }
    }

    /**
     * 加入团
     * @param $activeNo
     * @param $roomNo
     * @param $customerId
     * @param $orderNo
     * @param $schedulePrice
     * @param $companyId
     * @return mixed
     */
    private static function joinRoom($activeNo, $roomNo,$customerId, $orderNo,$schedulePrice, $companyId)
    {
        $groupRoomOrder = new GroupRoomOrder();
        $groupRoomOrder->active_no = $activeNo;
        $groupRoomOrder->customer_id = $customerId;
        $groupRoomOrder->order_no = $orderNo;
        $groupRoomOrder->room_no = $roomNo;
        $groupRoomOrder->company_id = $companyId;
        $groupRoomOrder->schedule_amount = $schedulePrice;
        ExceptionAssert::assertTrue($groupRoomOrder->save(),BusinessException::create(Common::getExistModelErrors($groupRoomOrder)));

        list($res,$errorMsg) = GroupRoomService::validateJustOrderOneOrder($roomNo,$customerId,$orderNo);
        ExceptionAssert::assertTrue($res,BusinessException::create($errorMsg));

        self::increaseGroupRoomPlaceCount($roomNo);
        return $roomNo;
    }

    /**
     * 抢占下单位置
     * @param $roomNo
     */
    public static function increaseGroupRoomPlaceCount($roomNo)
    {
        $updateCount = GroupRoom::updateAllCounters(
            [
                'place_count' => 1
            ],
            [
                'and',
                ['room_no' => $roomNo],
                "place_count + 1 <= max_level"
            ]);
        ExceptionAssert::assertTrue($updateCount >0 , BusinessException::create("房间{$roomNo}已满"));
    }

    /**
     * 创建Room
     * @param $activeNo
     * @param $customerId
     * @param $orderNo
     * @param $schedulePrice
     * @param $companyId
     * @return string
     */
    public static function createRoom($activeNo,$customerId, $orderNo,$schedulePrice, $companyId)
    {
        $groupActiveModel = GroupActiveService::getActiveModelWithSchedule(null,$activeNo,$companyId);
        $groupRoom = new GroupRoom();
        $groupRoom->active_no = $activeNo;
        $groupRoom->room_no = $groupRoom->generateNo();
        $groupRoom->created_at = DateTimeUtils::parseStandardWLongDate();
        $groupRoom->team_id = $customerId;
        $groupRoom->team_name = CustomerService::getNicknameById($customerId);
        $groupRoom->max_level = $groupActiveModel['maxLevel'];
        $groupRoom->min_level = $groupActiveModel['minLevel'];
        $groupRoom->continued = self::checkAndGetRemainContinued($groupRoom->created_at,$groupActiveModel['continued'],$groupActiveModel['schedule']['offline_time']);
        $groupRoom->expect_finished_at = DateTimeUtils::plusMinute($groupRoom->created_at,$groupRoom->continued);
        $groupRoom->place_count = 1;
        $groupRoom->paid_order_count = 0;
        $groupRoom->company_id = $companyId;
        $groupRoom->status = GroupRoom::GROUP_STATUS_PROCESSING;
        ExceptionAssert::assertTrue($groupRoom->save(), BusinessException::create(Common::getExistModelErrors($groupRoom)));


        $groupRoomOrder = new GroupRoomOrder();
        $groupRoomOrder->active_no = $activeNo;
        $groupRoomOrder->customer_id = $customerId;
        $groupRoomOrder->order_no = $orderNo;
        $groupRoomOrder->room_no = $groupRoom['room_no'];
        $groupRoomOrder->company_id = $companyId;
        $groupRoomOrder->schedule_amount = $schedulePrice;
        ExceptionAssert::assertTrue($groupRoomOrder->save(), BusinessException::create(Common::getExistModelErrors($groupRoomOrder)));

        return $groupRoom->room_no;
    }


    /**
     * 获取团的持续时间
     * @param $createdRoomTime
     * @param $continued
     * @param $activeEndTime
     * @return float|int
     */
    public static function checkAndGetRemainContinued($createdRoomTime, $continued, $activeEndTime){
        // 默认剩余时间 = 创建团时间+活动持续时间 - 当前时间；
        $littleTime = strtotime($activeEndTime)-strtotime($createdRoomTime);
        $result = 0;
        if ($littleTime>=$continued*60){
            $result = $continued;
        }
        else{
            $result = (int)($littleTime/60);
        }
        ExceptionAssert::assertTrue($result>0, BusinessException::create("活动即将结束，不在允许下单"));
        return $result;
    }


    /**
     * 关闭房间
     * @param $roomNo
     * @param $companyId
     * @param $customerId
     * @param $customerName
     * @throws BusinessException
     */
    public static function closeRoom($roomNo, $companyId, $customerId, $customerName){
        $room = GroupRoomService::getRoomDetail($roomNo,$companyId);
        ExceptionAssert::assertTrue($room['team_id']==$customerId, StatusCode::createExp(StatusCode::GROUP_ROOM_NOT_OWNER));
        $paymentSdk = Yii::$app->frontendWechat->payment;
        $transaction = Yii::$app->db->beginTransaction();
        try {
            list($success,$error) =  self::closeRoomCommon($roomNo,$companyId,$paymentSdk,OrderLogs::ROLE_CUSTOMER,$customerId,$customerName,"手动关闭拼团房间",true);
            ExceptionAssert::assertTrue($success, StatusCode::createExpWithParams(StatusCode::GROUP_ROOM_MANUAL_CLOSE, $error));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            throw BusinessException::create($e->getMessage());
        }
        //手动触发退款
        self::doRefundOneRoom($roomNo,$customerId,$customerName);
    }

    /**
     * [activeExpired 活动是否过期]
     * @param  [type] $active_id [description]
     * @return [type]            [description]
     */
    public static function checkActiveExpired($activeNo)
    {
        $groupActive = GroupByService::getActiveInfoByActiveId($activeId);
        ExceptionAssert::assertNotEmpty($groupActive, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '团购活动不存在'));
        $groupActiveEndTime = strtotime($groupActive['schedule']['offline_time']);
        if ($groupActiveEndTime <= time() || $groupActive['status'] == GroupActive::ACTIVE_END) {
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '活动过期'));
        }
        list($priceRules, $maxLevel) = GroupByService::decodeRules($groupActive['rule_desc']);
        $groupActive['price_rules'] = $priceRules;
        $groupActive['max_level'] = $maxLevel;
        return $groupActive;
    }

    /**
     * [checkGroupExpired 判断是否成团]
     * @param  [type] $groupActive [description]
     * @param  [type] $group_id    [description]
     * @return [type]              [description]
     */
    public static function checkGroupExpired($groupActive, $group_id)
    {
        ExceptionAssert::assertNotBlank($group_id, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, 'group_id不能为空'));
        $groupRoom = GroupRoomService::getActiveModel($group_id,$groupActive['company_id']);
        ExceptionAssert::assertNotNull($groupRoom, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '团不存在'));

        if ($groupRoom['status'] == GroupRoom::GROUP_STATUS_SUCCESSFUL) {
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '团已成'));
        }

        // 判断当前团是否已成团/过期
        list($res, $info) = GroupByService::checkActiveTimeOut($groupRoom['created_at'], $groupActive['continued'], $groupActive['schedule']['offline_time']);
        ExceptionAssert::assertTrue($res, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, $info));

        list($rules, $maxLevel) = GroupByService::decodeRules($groupActive['rule_desc']);
        $roomOrderCount = GroupByService::getInGroupRoomNmber($group_id);
        if ($roomOrderCount >= $maxLevel) {
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '团已满'));
        }

        // 占位人数 ==  最大成团设置人数时，不允许新用户进入,必须等占位释放出来
        if ($groupRoom['place_count'] >= $groupRoom['max_level'] && $groupRoom['place_count'] > $groupRoom['paid_order_count']) {
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '您还有机会,几分钟后再试！'));
        }
        return $groupRoom;
    }


    /**
     * 支付回调里 更新房间支付成功订单数量
     * @param Order $order
     * @return array
     */
    public static function increaseGroupRoomPaidCount(Order $order)
    {
        if ($order['order_type'] == GoodsConstantEnum::TYPE_GROUP_ACTIVE) {
            $roomOrder = GroupRoomOrderService::getActiveModel($order['order_no'],$order['company_id']);
            if (empty($roomOrder)){
                return [false,'找不到对应的拼团关系'];
            }
            $groupRoom = GroupRoomService::getActiveModel(null,$roomOrder['room_no'],$order['company_id']);
            if (empty($groupRoom)){
                return [false,"拼团{$roomOrder['room_no']}不存在"];
            }
            $updateCount = GroupRoom::updateAllCounters(
                [
                    'paid_order_count' => 1
                ],
                [
                    'and',
                    ['room_no' => $roomOrder['room_no']],
                ]);
            if ($updateCount < 1) {
                return [false, "拼团{$roomOrder['room_no']}更新支付数量失败"];
            }
        }
        return [true, ''];
    }



    public static function doRefundOneRoom($roomNo,$operationId,$operationName){
        $paymentSdk = Yii::$app->frontendWechat->payment;
        $groupRoomWaitRefundOrders = GroupRoomWaitRefundOrder::find()->where(['room_no'=>$roomNo])->asArray()->all();
        foreach ($groupRoomWaitRefundOrders as $groupRoomWaitRefundOrder){
            $transaction = Yii::$app->db->beginTransaction();
            try {
                list($success,$errorMsg) = self::doRealRefundOneOrder($groupRoomWaitRefundOrder['id'],$paymentSdk,OrderLogs::ROLE_CUSTOMER,$operationId,$operationName,"手动关拼团执行拼团退款单");
                $transaction->commit();
            }
            catch (\Exception $e){
                $transaction->rollBack();
                Yii::error($e);
            }
        }
    }
}