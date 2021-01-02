<?php


namespace business\services;

use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\Common;
use common\utils\ArrayUtils;
use Yii;

class PaymentService
{




    /**
     * 获取支付信息
     * @param $openid
     * @param $bizType
     * @param $bizId
     * @param $money
     * @return array
     */
    public static function generateJSSdkPayInfo($openid,$bizType,$bizId,$money){
        $payments = [];
        $result = Yii::$app->businessWechat->payment->order->unify([
            'body' => "满好生活-商品质保金(".DistributeBalanceService::encodePayChargeAttachMessage($bizType,$bizId).")",
            'out_trade_no' => DeliveryService::generateChargeOrderNo($bizId,time()),
            'attach'=>DistributeBalanceService::encodePayChargeAttachMessage($bizType,$bizId),
            'total_fee' => $money/10,
            'notify_url' => BusinessCommon::getChargeCallBackUrl(), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
        ]);
        ExceptionAssert::assertNotEmpty($result, StatusCode::createExpWithParams(StatusCode::CHARGE_PAY_ERROR, '微信支付创建失败'));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('return_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::CHARGE_PAY_ERROR,ArrayUtils::getArrayValue('return_msg',$result,"")));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('result_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::CHARGE_PAY_ERROR,ArrayUtils::getArrayValue('err_code_des',$result,"")));
        $jsApiParameters = Yii::$app->businessWechat->payment->jssdk->bridgeConfig($result['prepay_id'],false);
        $wechatPayments = [];
        $wechatPayments['text'] .= "微信支付：".Common::showAmount($money)."元";
        $wechatPayments['params'] = $jsApiParameters;
        $payments[] = $wechatPayments;
        return $payments;
    }

}