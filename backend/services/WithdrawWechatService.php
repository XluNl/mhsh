<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\BizTypeEnum;
use common\models\WithdrawWechat;
use Yii;

class WithdrawWechatService extends \common\services\WithdrawWechatService
{
    public static function createPayment($withdrawApply){
        $withdrawWechat = WithdrawWechatService::getModel($withdrawApply['id']);
        BExceptionAssert::assertNotNull($withdrawWechat, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信打款单未生成'));
        BExceptionAssert::assertNotNull(in_array($withdrawWechat['status'],[WithdrawWechat::STATUS_UN_DEAL,WithdrawWechat::STATUS_DEAL_FAILED]), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'只有未打款或者打款失败的才能重试'));
        $paymentSdk = null;
        if (in_array($withdrawApply['biz_type'],[BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET,BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE])){
            $paymentSdk = Yii::$app->frontendWechat->payment;
        }
        else if (in_array($withdrawApply['biz_type'],[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY])){
            $paymentSdk = Yii::$app->businessWechat->payment;
        }
        else if (in_array($withdrawApply['biz_type'],[BizTypeEnum::BIZ_TYPE_HA])){
            $paymentSdk = Yii::$app->allianceWechat->payment;
        }
        else{
            BExceptionAssert::assertTrue(false, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'不支持的bizType'));
        }
        $result = $paymentSdk->transfer->toBalance([
            'partner_trade_no' => $withdrawWechat['partner_trade_no'], // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $withdrawWechat['openid'],
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $withdrawWechat['amount']/10, // 企业付款金额，单位为分
            'desc' => "分润提现", // 企业付款操作说明信息。必填
        ]);
        BExceptionAssert::assertNotNull($result, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信支付调用失败'));
        BExceptionAssert::assertTrue(key_exists('return_code',$result), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信支付调用失败'));
        if ($result['return_code']=='SUCCESS'&&key_exists('result_code',$result)&&$result['result_code']=='SUCCESS'){
            $updateCount = WithdrawWechat::updateAll([
                'payment_no'=>$result['payment_no'],
                'status'=>WithdrawWechat::STATUS_DEAL_SUCCESS,
                'payment_time'=>strtotime($result['payment_time']),
            ],[
                'id'=>$withdrawWechat['id']
            ]);
            BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信打款记录更新失败'));
        }
        else if ($result['return_code']=='SUCCESS'){
            BExceptionAssert::assertTrue(key_exists($result['return_code'],$result)&&key_exists($result['result_code'],$result), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信支付'.$result['err_code_des']));
        }
        else {
            BExceptionAssert::assertTrue(key_exists($result['return_code'],$result)&&key_exists($result['result_code'],$result), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信支付'.$result['return_msg']));
        }
    }

}