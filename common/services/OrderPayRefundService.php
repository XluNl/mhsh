<?php


namespace common\services;


use common\models\OrderPay;
use common\models\OrderPayRefund;
use Yii;
use yii\db\Query;

class OrderPayRefundService
{

    /**
     * 微信退款
     * @param $order
     * @param $threePayAmount
     * @param $paymentSdk \EasyWeChat\Payment\Application
     * @param $refundDesc
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function distributeRefundThreePartyPay($order, $threePayAmount, $paymentSdk, $refundDesc){
        return self::distributeRefundThreePartyPayCommon($order['order_no'],$order['company_id'], $threePayAmount, $paymentSdk, $refundDesc);
    }

    /**
     * 微信退款
     * @param $orderNo
     * @param $companyId
     * @param $threePayAmount
     * @param $paymentSdk
     * @param $refundDesc
     * @return array
     */
    public static function distributeRefundThreePartyPayCommon($orderNo,$companyId, $threePayAmount, $paymentSdk, $refundDesc){
        if ($threePayAmount>0){
            $threePayAmountCent = intval( $threePayAmount/10);
            $orderPay = (new Query())->from(OrderPay::tableName())->where([
                'order_no'=>$orderNo
            ])->one();
            $orderPay = $orderPay===false?null:$orderPay;
            if ($orderPay==null){
                return [false,'无支付记录'];
            }
            if ($threePayAmountCent>($orderPay['total_fee']-$orderPay['remain_fee'])){
                return [false,'可退余额不足'];
            }
            $updateCount = OrderPay::updateAllCounters(
                [
                    'remain_fee'=>$threePayAmountCent,
                    'version'=>1
                ],
                [
                    'and',
                    [
                        'order_no'=>$orderNo,
                        'version'=>$orderPay['version']
                    ],
                    "total_fee-remain_fee>={$threePayAmountCent}",
                ]);
            if ($updateCount<1){
                return [false,'余额日志插入失败'];
            }

            $orderPayRefund = new OrderPayRefund();
            $orderPayRefund->company_id = $companyId;
            $orderPayRefund->transaction_id = $orderPay['transaction_id'];
            $orderPayRefund->out_trade_no = $orderPay['out_trade_no'];
            $orderPayRefund->out_refund_no = $orderNo.'_'.($orderPay['version']+1);
            $orderPayRefund->total_fee = $orderPay['total_fee'];
            $orderPayRefund->refund_fee = $threePayAmountCent;
            $orderPayRefund->refund_status = OrderPayRefund::REFUND_STATUS_REFUNDING;

            $result = $paymentSdk->refund->byTransactionId($orderPay['transaction_id'], $orderPayRefund->out_refund_no, $orderPayRefund->total_fee, $orderPayRefund->refund_fee, [
                // 可在此处传入其他参数，详细参数见微信支付文档
                'refund_desc' =>"{$orderNo}{$refundDesc}",
            ]);
            if ($result===null){
                return [false,'调用退款接口失败'];
            }
            if ($result['return_code']!='SUCCESS'){
                return [false,"调用退款接口失败:{$result['return_msg']}"];
            }
            if ($result['result_code']!='SUCCESS'){
                Yii::error("用户退款失败{$orderNo}:{$result['err_code']},{$result['err_code_des']}");
                return [false,"调用退款接口失败:{$result['err_code']}"];
            }
            $orderPayRefund->refund_id = $result['refund_id'];
            if (!$orderPayRefund->save()){
                return [false,"三方支付退款日志插入失败"];
            }
            return [true,$orderPayRefund->out_refund_no];
        }
        return [true,''];
    }



}