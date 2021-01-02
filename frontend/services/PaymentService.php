<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 14:38
 */

namespace frontend\services;

use common\models\Payment;
use common\utils\ArrayUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;

class PaymentService
{
    public static function getAvailable(){
        $initCompanyId = Yii::$app->params['option.init.companyId'];
        $payments = (new Query())->from(Payment::tableName())->Where([
            'AND',
            ['status'=>Payment::STATUS_ACTIVE],
            ['company_id'=>$initCompanyId],
        ])
            ->orderBy("display_order desc")
            ->all();
        return $payments;
    }

    public static function getById($payId){
        $initCompanyId = \Yii::$app->params['option.init.companyId'];
        $payment = (new Query())->from(Payment::tableName())->Where([
            'id'=>$payId,
            'status'=>Payment::STATUS_ACTIVE,
            'company_id'=>$initCompanyId,
        ])->one();
        return $payment;
    }


    public static function generateJSSdkPayInfo($openid, $order_no, $threePartAmount, $createdTime = null){
        $config = [
            'body' => '满好生活-订单支付'.$order_no,
            'out_trade_no' => $order_no,
            'attach'=>$order_no,
            'total_fee' => $threePartAmount/10,
            'notify_url' => FrontendCommon::getPaymentCallBackUrl(), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $openid,
        ];
        if($createdTime){
            $expireTime = strtotime($createdTime) + Yii::$app->params['order.un_pay.time'];
            $config['time_expire'] = date("YmdHis",$expireTime);
        }
        $result = Yii::$app->frontendWechat->payment->order->unify($config);
        ExceptionAssert::assertNotEmpty($result, StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR, '微信支付创建失败'));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('return_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,ArrayUtils::getArrayValue('return_msg',$result,"")));
        ExceptionAssert::assertTrue(ArrayUtils::getArrayValue('result_code',$result,null)=='SUCCESS', StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,ArrayUtils::getArrayValue('err_code_des',$result,"")));
        $jsSdkConfig = Yii::$app->frontendWechat->payment->jssdk->bridgeConfig($result['prepay_id'],false);
        OrderService::updatePrepayId($order_no,$result['prepay_id']);
        return $jsSdkConfig;
    }

    /**
     * 获取微信支付
     * @return mixed
     */
    public static function getWxPayment(){
        $payments = self::getAvailable();
        if (!empty($payments)){
            foreach ($payments as $payment){
                if ($payment['type']==Payment::TYPE_WECHAT){
                    return $payment;
                }
            }
        }
        $payment = ['id'=>Yii::$app->params['default.wxPayment.id'],'name'=>Payment::$typeArr[Payment::TYPE_WECHAT]];
        return $payment;
    }

}