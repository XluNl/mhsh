<?php


namespace frontend\services;


use common\models\BizTypeEnum;
use common\models\WithdrawApply;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;

class WithdrawApplyService extends \common\services\WithdrawApplyService
{
    /**
     * 提现申请（用户分润）
     * @param $bizId
     * @param $bizType
     * @param $amount
     * @param $type
     * @param $userId
     * @param $userName
     * @param $openId
     * @param $remark
     * @return array|void
     * @throws \yii\db\Exception
     */
    public static function createDistributeBalanceWithdrawApplyF($bizId, $bizType, $amount, $type, $userId, $userName, $openId,$remark){
        ExceptionAssert::assertTrue(in_array($type,[WithdrawApply::TYPE_OFFLINE,WithdrawApply::TYPE_WECHAT]),StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,"未知的提现方式"));
        ExceptionAssert::assertTrue(in_array($bizType,[BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE]),StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,"bizError"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::createDistributeBalanceWithdrawApply($bizId,$bizType,$amount,$type,$userId,$userName,$openId,$remark);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,$e->getMessage()));
        }
    }


    /**
     * 提现申请（用户余额）
     * @param $customerId
     * @param $amount
     * @param $type
     * @param $userId
     * @param $userName
     * @param $openId
     * @return array|void
     * @throws \yii\db\Exception
     */
    public static function createCustomerBalanceWithdrawApplyF($customerId, $amount, $type, $userId, $userName, $openId){
        ExceptionAssert::assertTrue(in_array($type,[WithdrawApply::TYPE_OFFLINE,WithdrawApply::TYPE_WECHAT]),StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,"未知的提现方式"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::createCustomerBalanceWithdrawApply($customerId,$amount,$type,$userId,$userName,$openId);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::WITHDRAW_ERROR,$e->getMessage()));
        }
    }
}