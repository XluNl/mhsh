<?php


namespace common\services;


use common\models\OrderPay;
use common\models\OrderPayRefund;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;

class XluOrderPayRefundService
{

    /**
     * 微信退款
     * @param $order
     * @param $three_pay_amount
     * @param $paymentSdk \EasyWeChat\Payment\Application
     * @param $refundDesc
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function distributeRefundThreePartyPay($order,$three_pay_amount,$paymentSdk,$refundDesc,&$resRePay=null){
        if ($three_pay_amount>0){
            $three_pay_amount_cent = intval( $three_pay_amount/10);
            $orderPay = (new Query())->from(OrderPay::tableName())->where([
                'order_no'=>$order['order_no']
            ])->one();

            $orderPay = $orderPay===false?null:$orderPay;
            if ($orderPay==null){
                return [false,'无支付记录'];
            }
            if ($three_pay_amount_cent>($orderPay['total_fee']-$orderPay['remain_fee'])){
                return [false,'可退余额不足'];
            }
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
            if ($updateCount<1){
                return [false,'余额日志插入失败'];
            }

            $orderPayRefund = new OrderPayRefund();
            $orderPayRefund->company_id = $order['company_id'];
            $orderPayRefund->transaction_id = $orderPay['transaction_id'];
            $orderPayRefund->out_trade_no = $orderPay['out_trade_no'];
            $orderPayRefund->out_refund_no = $order['order_no'].'_'.($orderPay['version']+1);
            $orderPayRefund->total_fee = $orderPay['total_fee'];
            $orderPayRefund->refund_fee = $three_pay_amount_cent;
            $orderPayRefund->refund_status = OrderPayRefund::REFUND_STATUS_REFUNDING;

            // $orderPayRefund->out_refund_no = "C710718975400114_21";
            // var_dump($orderPayRefund->out_refund_no);die;
            $result = $paymentSdk->refund->byTransactionId($orderPay['transaction_id'], $orderPayRefund->out_refund_no, $orderPayRefund->total_fee, $orderPayRefund->refund_fee, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' =>"{$order['order_no']}{$refundDesc}",
            ]);
           // var_dump($result);die;
            if ($result===null){
                return [false,'调用退款接口失败:out_refund_no='.$orderPayRefund->out_refund_no];
            }
            if ($result['return_code']!='SUCCESS'){
                return [false,"调用退款接口失败:{$result['return_msg']}--out_refund_no=".$orderPayRefund->out_refund_no];
            }
            if ($result['result_code']!='SUCCESS'){
                Yii::error("用户退款失败{$order['order_no']}:{$result['err_code']},{$result['err_code_des']}");
                return [false,"调用退款接口失败:{$result['err_code']}--out_refund_no=".$orderPayRefund->out_refund_no];
            }
            $orderPayRefund->refund_id = $result['refund_id'];
            if (!$orderPayRefund->save()){
                return [false,"三方支付退款日志插入失败--out_refund_no=".$orderPayRefund->out_refund_no."--error".json_encode($orderPayRefund->errors)];
            }
            // $resRePay = $orderPayRefund;
            return [true,$orderPayRefund];
        }
        return [false,'退款金额必须大于0'];
    }
}