<?php


namespace console\services;


use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\models\SystemOptions;
use common\utils\DateTimeUtils;
use console\utils\ExceptionAssert;
use console\utils\StatusCode;
use Yii;
use frontend\services\GroupOrderService;

class OrderService extends \common\services\OrderService
{
    /**
     * 系统批量取消未支付的超时订单
     * @param $nowTime
     * @return array
     * @throws \yii\db\Exception
     */
    public static function batchCancelUnPayOrder($nowTime){
        $orderUnPayMinute = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_AUTO_CANCEL_ORDER);
        Yii::error($orderUnPayMinute);
        $orderUnPayTime = 60*$orderUnPayMinute;
        Yii::error($orderUnPayTime);
        $createTime = DateTimeUtils::parseStandardWLongDate(($nowTime-$orderUnPayTime));
        $orderModels = Order::find()->where([
            'and',
            ['order_status'=>Order::ORDER_STATUS_UN_PAY],
            ['<','created_at',$createTime]
        ])->all();
        $successOrders = [];
        $failedOrders = [];
        $paymentSdk = Yii::$app->frontendWechat->payment;

        if (!empty($orderModels)){
            foreach ($orderModels as $orderModel){
                if (!self::checkUnPay($paymentSdk,$orderModel['order_no'])){
                    $failedOrders[] = $orderModel['order_no'];
                }
                if (self::autoCancelOrder($orderModel)){
                    $successOrders[] = $orderModel['order_no'];
                }
                else{
                    $failedOrders[] = $orderModel['order_no'];
                }
            }
        }
        return [$successOrders,$failedOrders];
    }

    /**
     * 超时取消订单
     * @param $order
     * @return bool
     * @throws \yii\db\Exception
     */
    private static function autoCancelOrder($order){
        $transaction = Yii::$app->db->beginTransaction();
        try{

            // 订单如果是团活动订单 则释放团占位
            list($success,$error) = GroupOrderService::releaseGroupRoomPlace($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //恢复优惠券
            list($success,$error) =  CouponService::recoveryCoupon($order['company_id'],$order['customer_id'],$order['order_no']);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));
            //取消库存
            list($success,$error) =  parent::refreshStock($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //增加日志
            list($success,$error) =  OrderLogService::addOrderLogForSystem($order,OrderLogs::ACTION_CANCEL_ORDER,"超时取消订单");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //取消订单
            list($success,$error) =  parent::refreshOrderStatusToCancel($order,"超时取消订单");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //最后退余额
            list($success,$error) = CustomerBalanceService::adjustBalance($order,$order['balance_pay_amount'],'超时取消订单退款', OrderLogs::ROLE_SYSTEM, OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM]);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            $transaction->commit();
            return true;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            return false;
        }
    }

    /**
     * 系统批量确认完成订单
     * @param $nowTime
     * @return array
     * @throws \yii\db\Exception
     */
    public static function batchCompleteOrder($nowTime){
        $orderAcceptTime = Yii::$app->params["order.complete.time"];
        $acceptTime = DateTimeUtils::parseStandardWLongDate(($nowTime-$orderAcceptTime));
        $orderModels = Order::find()->where([
            'and',
            ['order_status'=>Order::ORDER_STATUS_RECEIVE],
            ['<','accept_time',$acceptTime]
        ])->all();
        $successOrders = [];
        $failedOrders = [];
        if (!empty($orderModels)){
            foreach ($orderModels as $orderModel){
                if (self::autoCompleteOrder($orderModel)){
                    $successOrders[] = $orderModel['order_no'];
                }
                else{
                    $failedOrders[] = $orderModel['order_no'];
                }
            }
        }
        return [$successOrders,$failedOrders];
    }

    /**
     * 判断订单尚未支付
     * @param $paymentSdk
     * @param $orderNo
     * @return bool
     */
    public static function checkUnPay($paymentSdk,$orderNo){
        try{
            $orderQueryResult = $paymentSdk->order->queryByOutTradeNumber($orderNo);
            if ($orderQueryResult['return_code'] != 'SUCCESS'){
                return false;
            }
            if ($orderQueryResult['result_code'] == 'FAIL'&&$orderQueryResult['err_code'] == 'ORDERNOTEXIST'){
                return true;
            }
            if ($orderQueryResult['result_code'] == 'SUCCESS'&&$orderQueryResult['trade_state'] != 'SUCCESS'){
                return true;
            }
            return false;
        }
        catch (\Exception $e){
            Yii::error("checkUnPay error",$e);
            return false;
        }
    }

    /**
     * 超时完成订单
     * @param $order
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function autoCompleteOrder($order){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($result,$errorMsg) = self::completeOrder($order,$paymentSdk,OrderLogs::ROLE_SYSTEM,0,OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM]);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
            $transaction->commit();
            return true;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error("订单:{$order['order_no']},超时完成订单error:".$e->getMessage());
            return false;
        }
    }

    /**
     * 批量超时送达订单
     * @param $nowTime
     * @return array[]
     */
    public static function batchReceiveOrder($nowTime){
        $receiveTime = DateTimeUtils::parseStandardWLongDate(($nowTime-Yii::$app->params["order.receive.time"]));
        $lastOrderTime = DateTimeUtils::parseStandardWLongDate(($nowTime-Yii::$app->params["order.recent.receive.time"]));
        $orderModels = Order::find()->where([
            'and',
            [
                'order_status'=>[Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_DELIVERY],
                'order_owner'=>[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA],
            ],
            ['>','created_at',$lastOrderTime],
            ['>','created_at','2020-07-15 00:00:00'],
        ])->with(['goods'])->asArray()->all();
        $successOrders = [];
        $failedOrders = [];
        if (!empty($orderModels)){
            foreach ($orderModels as $orderModel){
                $goodsCount = count($orderModel['goods']);
                $goodsWaitReceiveCount = 0;
                $tmpReceiveTime = DateTimeUtils::parseStandardWLongDate(0);
                foreach ($orderModel['goods'] as $good){
                    if (!in_array($good['delivery_status'],[OrderGoods::DELIVERY_STATUS_PREPARE])){
                        $goodsWaitReceiveCount++;
                    }
                    $tmpReceiveTime = max($tmpReceiveTime,$good['updated_at']);
                }
                if ($goodsWaitReceiveCount==$goodsCount&&$tmpReceiveTime<$receiveTime){
                    if (self::autoReceiveOrder($orderModel)){
                        $successOrders[] = $orderModel['order_no'];
                    }
                    else{
                        $failedOrders[] = $orderModel['order_no'];
                    }
                }
            }
        }
        return [$successOrders,$failedOrders];
    }

    /**
     * 超时送达订单
     * @param $order
     * @return bool
     */
    public static function autoReceiveOrder($order){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$errorMsg) = self::uploadWeightAndReceiveOrderCommon($order,OrderLogs::ROLE_SYSTEM,0,OrderLogs::$role_list[OrderLogs::ROLE_SYSTEM]);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
            $transaction->commit();
            return true;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error("订单:{$order['order_no']},error:".$e->getMessage());
            return false;
        }
    }

}