<?php


namespace common\services;


use business\services\OrderLogService;
use common\models\Common;
use common\models\Customer;
use common\models\CustomerBalanceItem;
use common\models\GoodsConstantEnum;
use common\models\GroupRoomOrder;
use common\models\GroupRoomWaitRefundOrder;
use common\models\GroupRoomWaitRefundOrderItem;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\utils\DateTimeUtils;
use common\utils\PriceUtils;
use Yii;
use common\models\GroupRoom;
use common\utils\StringUtils;
use yii\helpers\Json;

class GroupOrderService
{


    /**
     * 释放团位
     * @param $order
     * @return array
     */
    public static function releaseGroupRoomPlace($order){

        if ($order['order_type'] == GoodsConstantEnum::TYPE_GROUP_ACTIVE) {
            if ($order['order_status']==Order::ORDER_STATUS_UN_PAY){
                $groupRoomOrder = GroupRoomOrderService::getActiveModel($order['order_no']);
                if (empty($groupRoomOrder)){
                    return [false, "释放团位失败:查不到团信息"];
                }
                $updateCount = GroupRoom::updateAllCounters(
                    [
                        'place_count' => -1
                    ],
                    [
                        'and',
                        ['room_no' => $groupRoomOrder['room_no']],
                        'place_count > 0'
                    ]);
                if ($updateCount < 1) {
                    return [false, "释放团位失败:{$groupRoomOrder['room_no']}"];
                }
            }
        }
        return [true, ''];
    }


    /**
     * 结算房间
     * @param $roomNo
     * @param $companyId
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @param $cancelRemark
     * @param false $force
     * @return array
     */
    public static function closeRoomCommon($roomNo,$companyId,$paymentSdk,$role,$operationId,$operationName,$cancelRemark,$force=false){
        $room = GroupRoomService::getRoomDetail($roomNo,$companyId);
        if ($room==null){
            return [false,'查无此团'];
        }
        if ($room['status']!=GroupRoom::GROUP_STATUS_PROCESSING){
            return [false,'团已结算'];
        }
        $groupActive = GroupActiveService::getModelVO($room['active_no']);
        if (empty($groupActive)){
            return [false,'拼团活动不存在'];
        }
        if (!$force){
            //非强制关团
            if (GroupRoomService::roomIsNotTimeOut($room['expect_finished_at'])
                &&$room['paid_order_count']<$room['max_level']){
                return [false,"团仍在进行中"];
            }
            if ($room['place_count']>$room['paid_order_count']){
                return [false,"有待支付的订单，不能关团"];
            }
        }
        else{
            //强制关团
            $unPayOrders = GroupRoomOrderService::getRoomOrders($roomNo,$companyId,Order::ORDER_STATUS_UN_PAY);
            list($success,$error) = self::cancelWaitPayGroupOrders($unPayOrders,$paymentSdk,$role,$operationId,$operationName,$cancelRemark);
            if (!$success){
                return [false,$error];
            }
            //$room['place_count'] = $room['paid_order_count'];
        }
        $checkingOrders = GroupRoomOrderService::getRoomOrders($roomNo,$companyId,Order::ORDER_STATUS_CHECKING);
        if ($room['paid_order_count']<$room['minLevel']){
            list($success,$error) = self::cancelAllRoomOrders($checkingOrders,$room);
            if (!$success){
                return [false,$error];
            }
            $room['status'] = GroupRoom::GROUP_STATUS_FAILED;
            $room['msg'] = "未成团，未达到最小成团人数";
        }
        else{
            $roomPrice = self::getRoomPrice($room['paid_order_count'],$groupActive['rule_desc']);
            if ($room['paid_order_count']<1){
                //不需要处理，直接关团即可
                $room['status'] = GroupRoom::GROUP_STATUS_FAILED;
                $room['msg'] = "未成团，没有用户下单";
            }
            else if ($room['paid_order_count']==1){
                list($success,$error) = self::doNothingRoomOrders($checkingOrders,$room);
                if (!$success){
                    return [false,$error];
                }
                $room['status'] = GroupRoom::GROUP_STATUS_SUCCESSFUL;
            }
            //$room['paid_order_count']>1
            else {
                if ($roomPrice==null){
                    list($success,$error) = self::doNothingRoomOrders($checkingOrders,$room);
                    if (!$success){
                        return [false,$error];
                    }

                }
                foreach ($checkingOrders as $order){
                    $orderGoodsList = OrderService::getOrderGoodsModel($order['order_no']);
                    foreach ($orderGoodsList as &$orderGoods){
                        if ($groupActive['schedule_id']==$orderGoods['schedule_id']){
                            if ($orderGoods['sku_price']==$roomPrice){
                                list($success,$error) = self::doNothingRoomOrders($checkingOrders,$room);
                                if (!$success){
                                    return [false,$error];
                                }
                            }
                            else if ($orderGoods['sku_price']>$roomPrice){
                                list($success,$error) = self::doPartRefundRoomOrders($order,$orderGoodsList,$orderGoods,$roomPrice,$room);
                                if (!$success){
                                    return [false,$error];
                                }
                            }
                            else{
                                return [false,"价格设置错误，多人团价格比基础价格高"];
                            }

                            break;
                        }
                    }
                }
                $room['status'] = GroupRoom::GROUP_STATUS_SUCCESSFUL;
            }
            $room['finished_at'] = DateTimeUtils::parseStandardWLongDate();
        }
        $updateCount = GroupRoom::updateAll([
            'status'=>$room['status'],
            'finished_at'=>$room['finished_at'],
            'msg'=>$room['msg'],
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(),
        ],['room_no'=>$room['room_no']]);
        if ($updateCount<1){
            return [false,"拼团房间更新失败"];
        }
        return [true,''];
    }

    /**
     * 计算退款方案
     * @param $order
     * @param $refundAmount
     * @return array
     */
    public static function calcBalanceAndThreePartyAmountForGroupOrder($order,$refundAmount){
        $balanceAmount = $order['balance_pay_amount'];
        $threePartyAmount = $order['three_pay_amount'];
        $refundBalanceAmount = 0;
        $refundThreePartyAmount = 0;
        //如果尚未进行任何提货
        //如果差额大于三方支付金额
        if ($refundAmount<=$order['balance_pay_amount']){
            $balanceAmount = $order['balance_pay_amount'] - $refundAmount;
            $refundBalanceAmount = $refundAmount;
        }
        else{
            $balanceAmount = 0;
            $refundBalanceAmount = $order['balance_pay_amount'];
            $refundThreePartyAmount = $refundAmount-$order['balance_pay_amount'];
            $threePartyAmount = $order['three_pay_amount'] - $refundThreePartyAmount;
        }
        return [$balanceAmount,$threePartyAmount,$refundBalanceAmount,$refundThreePartyAmount];
    }

    /**
     * 记录取消团内所有订单
     * @param $checkingOrders
     * @param $room
     * @return array
     */
    private static function cancelAllRoomOrders($checkingOrders,$room){
        foreach ($checkingOrders as $order){
            $refundOrder = new GroupRoomWaitRefundOrder();
            $refundOrder->active_no = $room['active_no'];
            $refundOrder->room_no = $room['room_no'];
            $refundOrder->order_no = $order['order_no'];
            $refundOrder->customer_id = $order['customer_id'];
            $refundOrder->order_amount = $order['real_amount'];
            $refundOrder->refund_amount = $order['real_amount'];
            $refundOrder->status = GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT;
            $refundOrder->refund_action = GroupRoomWaitRefundOrder::REFUND_ACTION_CANCEL;
            $refundOrder->company_id = $order['company_id'];
            if (!$refundOrder->save()){
                return [false,Common::getExistModelErrors($refundOrder)];
            }
            /*if ($order['balance_pay_amount']>0){
                $refundOrderItem = new GroupRoomWaitRefundOrderItem();
                $refundOrderItem->wait_refund_order_id = $refundOrder->id;
                $refundOrderItem->active_no = $room['active_no'];
                $refundOrderItem->room_no = $room['room_no'];
                $refundOrderItem->order_no = $order['order_no'];
                $refundOrderItem->customer_id = $order['customer_id'];
                $refundOrderItem->refund_type = GroupRoomWaitRefundOrderItem::REFUND_TYPE_BALANCE;
                $refundOrderItem->refund_amount = $order['balance_pay_amount'];
                $refundOrderItem->company_id = $order['company_id'];
                if (!$refundOrderItem->save()){
                    return [false,Common::getExistModelErrors($refundOrderItem)];
                }
            }
            if ($order['three_pay_amount']>0){
                $refundOrderItem = new GroupRoomWaitRefundOrderItem();
                $refundOrderItem->wait_refund_order_id = $refundOrder->id;
                $refundOrderItem->active_no = $room['active_no'];
                $refundOrderItem->room_no = $room['room_no'];
                $refundOrderItem->order_no = $order['order_no'];
                $refundOrderItem->customer_id = $order['customer_id'];
                $refundOrderItem->refund_type = GroupRoomWaitRefundOrderItem::REFUND_TYPE_WECHAT;
                $refundOrderItem->refund_amount = $order['three_pay_amount'];
                $refundOrderItem->company_id = $order['company_id'];
                if (!$refundOrderItem->save()){
                    return [false,Common::getExistModelErrors($refundOrderItem)];
                }
            }*/
        }
        return [true,''];
    }

    /**
     * 记录不需要做任何事
     * @param $checkingOrders
     * @param $room
     * @return array
     */
    private static function doNothingRoomOrders($checkingOrders,$room){
        foreach ($checkingOrders as $order){
            $refundOrder = new GroupRoomWaitRefundOrder();
            $refundOrder->active_no = $room['active_no'];
            $refundOrder->room_no = $room['room_no'];
            $refundOrder->order_no = $order['order_no'];
            $refundOrder->customer_id = $order['customer_id'];
            $refundOrder->order_amount = $order['real_amount'];
            $refundOrder->refund_amount = 0;
            $refundOrder->status = GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT;
            $refundOrder->refund_action = GroupRoomWaitRefundOrder::REFUND_ACTION_NOTHING;
            $refundOrder->company_id = $order['company_id'];
            if (!$refundOrder->save()){
                return [false,Common::getExistModelErrors($refundOrder)];
            }
            $updateCount = GroupRoomOrder::updateAll(['active_amount'=>'schedule_amount','updated_at'=>DateTimeUtils::parseStandardWLongDate()],
                ['room_no'=>$room['room_no'],'order_no'=>$order['order_no']]
            );
            if ($updateCount<1){
                return [false,"房间订单最终团购价格更新失败"];
            }
        }
        return [true,''];
    }

    /**
     * 记录退团差价
     * @param $order
     * @param $orderGoodsList
     * @param $orderGoods
     * @param $roomPrice
     * @param $room
     * @return array
     */
    private static function doPartRefundRoomOrders($order,$orderGoodsList,&$orderGoods,$roomPrice,$room){
        $orderGoods['amount']  = PriceUtils::accurateToTen( ($orderGoods['num']*$roomPrice)-$orderGoods['discount']);
        $orderGoods['sku_price'] = $roomPrice;
        $updateCount = OrderGoods::updateAll(['sku_price'=>$orderGoods['sku_price'],'amount'=>$orderGoods['amount']],['order_no'=>$order['order_no'],'id'=>$orderGoods['id']]);
        if ($updateCount<1){
            return [false,"商品单价设置失败"];
        }
        $needAmount = 0;
        //$realAmount = 0;
        foreach ($orderGoodsList as $orderGoodsModel){
            $needAmount += $orderGoodsModel['sku_price']*$orderGoodsModel['num'];
            //$realAmount += $orderGoodsModel['amount'];
        }
        $realAmount = OrderService::calcRealAmount($needAmount,$order['freight_amount'],$order['discount']);
        list($balanceAmount,$threePartyAmount,$refundBalanceAmount,$refundThreePartyAmount) = self::calcBalanceAndThreePartyAmountForGroupOrder($order,$order['real_amount']-$realAmount);
        $order['need_amount'] = $needAmount;
        $order['real_amount'] = $realAmount;
        $order['balance_pay_amount'] = $balanceAmount;
        $order['three_pay_amount'] = $threePartyAmount;
        Order::updateAll([
            'need_amount'=>$needAmount,
            'real_amount'=>$order['real_amount'],
            'balance_pay_amount'=>$balanceAmount,
            'three_pay_amount'=>$threePartyAmount,
        ],['order_no'=>$order['order_no']]);
        $refundOrder = new GroupRoomWaitRefundOrder();
        $refundOrder->active_no = $room['active_no'];
        $refundOrder->room_no = $room['room_no'];
        $refundOrder->order_no = $order['order_no'];
        $refundOrder->customer_id = $order['customer_id'];
        $refundOrder->order_amount = $order['real_amount'];
        $refundOrder->refund_amount = $refundBalanceAmount+$refundThreePartyAmount;
        $refundOrder->status = GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT;
        $refundOrder->refund_action = GroupRoomWaitRefundOrder::REFUND_ACTION_PART;
        $refundOrder->company_id = $order['company_id'];
        if (!$refundOrder->save()){
            return [false,Common::getExistModelErrors($refundOrder)];
        }
        if ($refundBalanceAmount>0){
            $refundOrderItem = new GroupRoomWaitRefundOrderItem();
            $refundOrderItem->wait_refund_order_id = $refundOrder->id;
            $refundOrderItem->active_no = $room['active_no'];
            $refundOrderItem->room_no = $room['room_no'];
            $refundOrderItem->order_no = $order['order_no'];
            $refundOrderItem->customer_id = $order['customer_id'];
            $refundOrderItem->refund_type = GroupRoomWaitRefundOrderItem::REFUND_TYPE_BALANCE;
            $refundOrderItem->refund_amount = $refundBalanceAmount;
            $refundOrderItem->company_id = $order['company_id'];
            if (!$refundOrderItem->save()){
                return [false,Common::getExistModelErrors($refundOrderItem)];
            }
        }
        if ($refundThreePartyAmount>0){
            $refundOrderItem = new GroupRoomWaitRefundOrderItem();
            $refundOrderItem->wait_refund_order_id = $refundOrder->id;
            $refundOrderItem->active_no = $room['active_no'];
            $refundOrderItem->room_no = $room['room_no'];
            $refundOrderItem->order_no = $order['order_no'];
            $refundOrderItem->customer_id = $order['customer_id'];
            $refundOrderItem->refund_type = GroupRoomWaitRefundOrderItem::REFUND_TYPE_WECHAT;
            $refundOrderItem->refund_amount = $refundThreePartyAmount;
            $refundOrderItem->company_id = $order['company_id'];
            if (!$refundOrderItem->save()){
                return [false,Common::getExistModelErrors($refundOrderItem)];
            }
        }

        $updateCount = GroupRoomOrder::updateAll(['active_amount'=>$roomPrice,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],
            ['room_no'=>$room['room_no'],'order_no'=>$order['order_no']]
        );
        if ($updateCount<1){
            return [false,"房间订单最终团购价格更新失败"];
        }

        return [true,''];
    }

    /**
     * 解析房间价格
     * @param $paidOrderCount
     * @param $ruleDesc
     * @return mixed|null
     */
    private static function getRoomPrice($paidOrderCount,$ruleDesc){
        if (StringUtils::isBlank($ruleDesc)){
            return null;
        }
        $ruleDescJson = Json::decode($ruleDesc);
        if (empty($ruleDescJson)){
            return null;
        }
        ksort($ruleDescJson);
        $price = null;
        foreach ($ruleDescJson as $k=>$v){
            if ($k<=$paidOrderCount){
                $price=$v;
            }
            else{
                break;
            }
        }
        return $price;
    }

    /**
     * 尝试取消团内未支付订单
     * @param $orders
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @param $cancelRemark
     * @return array
     */
    public static function cancelWaitPayGroupOrders($orders,$paymentSdk,$role,$operationId,$operationName,$cancelRemark)
    {
        foreach ($orders as $key => $order) {
            // 团内下单后15分钟内没有支付的订单 暂时不关团，也不用去查询订单状态
            $orderUnPayTime = strtotime($order['created_at']) + Yii::$app->params['order.un_pay.time'];
            if($orderUnPayTime > time()){
                return [false, '该房间有未支付订单未过期,此次不关闭该房间'];
            }
            // 过期(逻辑过期时间)未支付的订单，去微信查一查
            $resInfo = $paymentSdk->order->queryByOutTradeNumber($order['order_no']);
            if ($resInfo['return_code'] == 'SUCCESS') {
                if ($resInfo['result_code'] == 'SUCCESS'){
                    if ($resInfo['trade_state']=="NOTPAY" || $resInfo['trade_state']=="USERPAYING"){
                        return [false, "订单{$order['order_no']}正在支付中"];
                    }
                }
                else{
                    if ($resInfo['err_code']!="ORDERNOTEXIST"){
                        return [false, "订单{$order['order_no']}查询失败:".$resInfo['err_code_des']];
                    }
                }

            }
            else{
                return [false, "订单{$order['order_no']}支付状态查询失败"];
            }
        }
        foreach ($orders as $key => $order) {
            list($res, $error) = OrderService::cancelOrderCommon(
                $order,$paymentSdk,$role,$operationId,$operationName,$cancelRemark
            );
            if(!$res){
                return [$res,$error];
            }
        }
        return [true, '取消订单成功'];
    }


    private static function refreshOrderCalcRealAmount($real_amount,$freight_amount){
        return round(($real_amount+$freight_amount)/10.0)*10.0;
    }

    /*----------------------------------------------------------------------------------------------------------*/





    /**
     * 执行一个退款单
     * @param $groupRoomWaitRefundOrderId
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @param $cancelRemark
     * @return array
     */
    public static function doRealRefundOneOrder($groupRoomWaitRefundOrderId,$paymentSdk,$role,$operationId,$operationName,$cancelRemark){
        $groupRoomWaitRefundOrder = GroupRoomWaitRefundOrder::findOne(['id'=>$groupRoomWaitRefundOrderId]);
        if ($groupRoomWaitRefundOrder===false){
            return [false,"退款单不存在"];
        }
        if ($groupRoomWaitRefundOrder['status']!=GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT){
            return [false,"退款单已处理"];
        }
        if ($groupRoomWaitRefundOrder['refund_action']==GroupRoomWaitRefundOrder::REFUND_ACTION_NOTHING){
            list($success,$msg) = self::refreshOrderStatusCheckingToPrepare($groupRoomWaitRefundOrder['order_no'],$groupRoomWaitRefundOrder['company_id'],$role,$operationId,$operationName);
            if (!$success){
                return [false,$msg];
            }
        }
        else {
            $groupRoomWaitRefundOrderItems = GroupRoomWaitRefundOrderItem::find()->where(['wait_refund_order_id'=>$groupRoomWaitRefundOrderId])->all();
            if (empty($groupRoomWaitRefundOrderItems)){
                return [false,"没有退款单明细"];
            }
            $customer = Customer::findOne(['id'=>$groupRoomWaitRefundOrder['customer_id']]);
            if ($customer===false){
                return [false,"找不到客户信息"];
            }
            if ($groupRoomWaitRefundOrder['refund_action']==GroupRoomWaitRefundOrder::REFUND_ACTION_NOTHING){
                list($success,$msg) = self::refreshOrderStatusCheckingToPrepare($groupRoomWaitRefundOrder['order_no'],$groupRoomWaitRefundOrder['company_id'],$role,$operationId,$operationName);
                if (!$success){
                    return [false,$msg];
                }
            }
            else if ($groupRoomWaitRefundOrder['refund_action']==GroupRoomWaitRefundOrder::REFUND_ACTION_CANCEL){
                list($res, $error) = self::doRealRefundOneCancel($groupRoomWaitRefundOrder,$paymentSdk,$role,$operationId,$operationName,$cancelRemark);
                if(!$res){
                    return [$res,$error];
                }
            }
            else if ($groupRoomWaitRefundOrder['refund_action']==GroupRoomWaitRefundOrder::REFUND_ACTION_PART){
                list($res, $error) = self::doRealRefundOnePart($groupRoomWaitRefundOrder,$groupRoomWaitRefundOrderItems,$customer,$paymentSdk,$role,$operationId,$operationName);
                if(!$res){
                    return [$res,$error];
                }
            }
        }
        $updateCount = GroupRoomWaitRefundOrder::updateAll([
            'status'=>GroupRoomWaitRefundOrder::REFUND_STATUS_SUCCESS,
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(),
        ],
            [
                'id'=>$groupRoomWaitRefundOrderId,
                'status'=>GroupRoomWaitRefundOrder::REFUND_STATUS_WAIT
            ]);
        if ($updateCount<1){
            return [false,'退款单更新失败'];
        }
        return [true,""];
    }

    /**
     * 执行取消订单
     * @param $groupRoomWaitRefundOrder
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @param $cancelRemark
     * @return array
     */
    public static function doRealRefundOneCancel($groupRoomWaitRefundOrder, $paymentSdk, $role, $operationId, $operationName, $cancelRemark){
        $order = Order::find()->where(['order_no'=>$groupRoomWaitRefundOrder['order_no']])->one();
        if ($order===false){
            return [false,"退款单中的订单不存在"];
        }
        if ($order['order_status']!=Order::ORDER_STATUS_CHECKING){
            return [false,"退款单中的订单状态不处于确认中"];
        }
        list($res, $error) = OrderService::cancelOrderCommon(
            $order,$paymentSdk,$role,$operationId,$operationName,$cancelRemark
        );
        if(!$res){
            return [$res,$error];
        }
        return [true,''];
    }

    /**
     * d执行部分退款
     * @param $groupRoomWaitRefundOrder
     * @param $groupRoomWaitRefundOrderItems
     * @param $customer
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @return array
     */
    public static function doRealRefundOnePart($groupRoomWaitRefundOrder,$groupRoomWaitRefundOrderItems,$customer,$paymentSdk,$role,$operationId,$operationName){
        //校验没有异常的退款方式
        foreach ($groupRoomWaitRefundOrderItems as $groupRoomWaitRefundOrderItem){
            if (!in_array($groupRoomWaitRefundOrderItem['refund_type'],array_keys(GroupRoomWaitRefundOrderItem::$refundTypeArr))){
                return [false,"有未知的退款方式"];
            }
        }
        //退款余额方式
        foreach ($groupRoomWaitRefundOrderItems as $groupRoomWaitRefundOrderItem){
            if ($groupRoomWaitRefundOrderItem['refund_type']==GroupRoomWaitRefundOrderItem::REFUND_TYPE_BALANCE){
                list($success,$msg,$balanceId) = CustomerBalanceService::adjustBalanceCommon(
                    CustomerBalanceItem::BIZ_TYPE_GROUP_ROOM_REFUND_GAP,
                    $groupRoomWaitRefundOrderItem['order_no'],
                    $groupRoomWaitRefundOrderItem['customer_id'],
                    $groupRoomWaitRefundOrderItem['refund_amount'],
                    '拼团退差价',
                    $customer['id'],
                    $customer['nickname']
                );
                if (!$success){
                    return [false,$msg];
                }
                $updateCount = GroupRoomWaitRefundOrderItem::updateAll(['refund_no'=>$msg,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$groupRoomWaitRefundOrderItem['id']]);
                if ($updateCount<1){
                    return [false,'退款明细中退款单号更新失败'];
                }
            }
        }
        //退款微信方式
        foreach ($groupRoomWaitRefundOrderItems as $groupRoomWaitRefundOrderItem){
            if ($groupRoomWaitRefundOrderItem['refund_type']==GroupRoomWaitRefundOrderItem::REFUND_TYPE_WECHAT){
                list($success,$msg) = OrderPayRefundService::distributeRefundThreePartyPayCommon(
                    $groupRoomWaitRefundOrderItem['order_no'],
                    $groupRoomWaitRefundOrderItem['company_id'],
                    $groupRoomWaitRefundOrderItem['refund_amount'],
                    $paymentSdk,
                    "拼团退差价",
                );
                if (!$success){
                    return [false,$msg];
                }
                $updateCount = GroupRoomWaitRefundOrderItem::updateAll(['refund_no'=>$msg,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$groupRoomWaitRefundOrderItem['id']]);
                if ($updateCount<1){
                    return [false,'退款明细中退款单号更新失败'];
                }
            }
        }
        list($success,$msg) = self::refreshOrderStatusCheckingToPrepare($groupRoomWaitRefundOrder['order_no'],$groupRoomWaitRefundOrder['company_id'],$role,$operationId,$operationName);
        if (!$success){
            return [false,$msg];
        }
        return [true,''];
    }
    /**
     * checking转prepare状态
     * @param $orderNo
     * @param $companyId
     * @param $role
     * @param $operationId
     * @param $operationName
     * @return array
     */
    public static function refreshOrderStatusCheckingToPrepare($orderNo,$companyId,$role,$operationId,$operationName){
        $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_PREPARE,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$orderNo,'order_status'=>Order::ORDER_STATUS_CHECKING]);
        if ($updateCount<1){
            return [false,'订单状态已变更，请重试'];
        }
        //增加日志
        list($success,$error) =  OrderLogService::addLog($role,$orderNo,$companyId,$operationId,$operationName,OrderLogs::ACTION_GROUP_ACTIVE_TIME_OUT_CANCEL_ORDER,"");
        if (!$success){
            return [false,$error];
        }
        return [true,''];

    }

}