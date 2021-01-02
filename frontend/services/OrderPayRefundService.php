<?php


namespace frontend\services;


use common\models\OrderPay;
use common\models\OrderPayRefund;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;

class OrderPayRefundService extends \common\services\OrderPayRefundService
{

    public static function refundThreePartPay($order){
        if ($order['three_pay_amount']>0){
            $three_pay_amount_cent = ($order['three_pay_amount']/10);
            $orderPay = (new Query())->from(OrderPay::tableName())->where([
                'order_no'=>$order['order_no']
            ])->one();
            $orderPay = $orderPay===false?null:$orderPay;
            ExceptionAssert::assertNotNull($orderPay,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"无支付记录"));

            ExceptionAssert::assertTrue($three_pay_amount_cent<=($orderPay['total_fee']-$orderPay['remain_fee']),StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"可退余额不足"));
            $updateCount = OrderPay::updateAllCounters(
                [
                    'remain_fee'=>$three_pay_amount_cent,
                    'version'=>1
                ],
                [
                    'and',
                    [
                        'order_no'=>$order['order_no'],
                        'version'=>$orderPay['version']
                    ],
                    "total_fee-remain_fee>={$three_pay_amount_cent}",
                ]);

            ExceptionAssert::assertTrue($updateCount>0,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"余额日志插入失败"));

            $orderPayRefund = new OrderPayRefund();
            $orderPayRefund->company_id = $order['company_id'];
            $orderPayRefund->transaction_id = $orderPay['transaction_id'];
            $orderPayRefund->out_trade_no = $orderPay['out_trade_no'];
            $orderPayRefund->out_refund_no = $order['order_no'].'_'.($orderPay['version']+1);
            $orderPayRefund->total_fee = $orderPay['total_fee'];
            $orderPayRefund->refund_fee = $three_pay_amount_cent;
            $orderPayRefund->refund_status = OrderPayRefund::REFUND_STATUS_REFUNDING;

            $paymentSdk = Yii::$app->frontendWechat->payment;

            $result = $paymentSdk->refund->byTransactionId($orderPay['transaction_id'], $orderPayRefund->out_refund_no, $orderPayRefund->total_fee, $orderPayRefund->refund_fee, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' =>"{$order['order_no']}订单取消",
            ]);
            ExceptionAssert::assertNotNull($result,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"调用退款接口失败"));
            ExceptionAssert::assertTrue($result['return_code']=='SUCCESS',StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"调用退款接口失败:{$result['return_msg']}"));

            $orderPayRefund->refund_id = $result['refund_id'];
            ExceptionAssert::assertTrue($orderPayRefund->save(),StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"三方支付退款日志插入失败"));
        }
    }

}