<?php


namespace common\services;


use common\models\OrderPayRefund;
use common\models\WechatPayLog;
use common\models\WechatPayRefundLog;
use Yii;
use yii\db\Query;

class WechatPayLogService
{

    public static function getModel($id,$model = false){
        $conditions = ['id' => $id];
        if ($model){
            return WechatPayLog::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(WechatPayLog::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据业务信息查询
     * @param $bizType
     * @param $bizId
     * @return array
     */
    public static function getByBiz($bizType,$bizId){
        $conditions = ['biz_type' => $bizType,'biz_id' => $bizId];
        $result = (new Query())->from(WechatPayLog::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 触发退款
     * @param $refundFee
     * @param $wechatPayLog
     * @param $refundDesc
     * @param $paymentSdk
     * @param $bizType
     * @param $bizId
     * @return array
     */
    public static function refund($refundFee,$wechatPayLog,$refundDesc,$paymentSdk,$bizType,$bizId){
        $wechatPayRefundLog = new WechatPayRefundLog();
        $wechatPayRefundLog->company_id = $wechatPayLog['company_id'];
        $wechatPayRefundLog->transaction_id = $wechatPayLog['transaction_id'];
        $wechatPayRefundLog->out_trade_no = $wechatPayLog['out_trade_no'];
        $wechatPayRefundLog->out_refund_no = $wechatPayLog['out_trade_no'].'_'.($wechatPayLog['version']+1);
        $wechatPayRefundLog->total_fee = $wechatPayLog['total_fee'];
        $wechatPayRefundLog->refund_fee = $refundFee;
        $wechatPayRefundLog->refund_status = OrderPayRefund::REFUND_STATUS_SUCCESS;
        $wechatPayRefundLog->biz_id = $bizId;
        $wechatPayRefundLog->biz_type = $bizType;
        $result = $paymentSdk->refund->byTransactionId($wechatPayLog['transaction_id'], $wechatPayRefundLog->out_refund_no, $wechatPayRefundLog->total_fee, $wechatPayRefundLog->refund_fee, [
            // 可在此处传入其他参数，详细参数见微信支付文档
            'refund_desc' =>"{$refundDesc}",
        ]);
        if ($result===null){
            return [false,'调用退款接口失败'];
        }
        if ($result['return_code']!='SUCCESS'){
            return [false,"调用退款接口失败:{$result['return_msg']}"];
        }
        if ($result['result_code']!='SUCCESS'){
            Yii::error("联盟保证金退回失败{$wechatPayLog['biz_id']}:{$result['err_code']},{$result['err_code_des']}");
            return [false,"调用退款接口失败:{$result['err_code']}"];
        }
        $wechatPayRefundLog->refund_id = $result['refund_id'];
        if (!$wechatPayRefundLog->save()){
            return [false,"三方支付退款日志插入失败"];
        }
        return [true,''];
    }
}