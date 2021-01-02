<?php


namespace common\services;


use common\models\BizTypeEnum;
use common\models\Common;
use common\models\CommonStatus;
use common\models\CustomerBalance;
use common\models\CustomerBalanceItem;
use common\models\DistributeBalance;
use common\models\DistributeBalanceItem;
use Yii;
use common\models\WithdrawApply;
use common\models\WithdrawLog;
use common\models\WithdrawWechat;
use yii\db\Query;
use yii\helpers\Json;

class WithdrawApplyService
{
    /**
     * 获取model
     * @param $id
     * @param bool $model
     * @return array|bool|WithdrawApply|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$model = false){
        $conditions = ['id' => $id];
        if ($model){
            return WithdrawApply::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(WithdrawApply::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
    /**
     * 创建提现申请单（分润）
     * @param $bizId
     * @param $bizType
     * @param $amount
     * @param $type
     * @param $operatorId
     * @param $operatorName
     * @param $openId
     * @param $remark
     * @return array
     */
    public static function createDistributeBalanceWithdrawApply($bizId, $bizType, $amount, $type, $operatorId, $operatorName, $openId, $remark=""){
        if (!in_array($type,[WithdrawApply::TYPE_OFFLINE,WithdrawApply::TYPE_WECHAT])){
            return [false,'未知的提现方式'];
        }
        list($maxAndMinCheck,$maxAndMinCheckError) = self::checkMinAndMax($type,$amount);
        if (!$maxAndMinCheck){
            return [false,$maxAndMinCheckError];
        }
        if ($amount%10>0){
            return [false,'提现金额最小单位为分'];
        }
        if (!in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY,BizTypeEnum::BIZ_TYPE_HA,BizTypeEnum::BIZ_TYPE_AGENT,BizTypeEnum::BIZ_TYPE_COMPANY,BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE])){
            return [false,'未知的bizType'];
        }
        $userId = $operatorId;
        if (in_array($bizType,[BizTypeEnum::BIZ_TYPE_AGENT,BizTypeEnum::BIZ_TYPE_COMPANY])){
            $userId = $bizId;
        }
        $distributeBalance = DistributeBalanceService::getModelByBiz($bizId,$bizType,$userId);
        if ($distributeBalance==null){
            return [false,'账户不存在'];
        }
        if ($distributeBalance['amount']<$amount){
            return [false,'可提现余额不足'];
        }
        /**提现申请单*/
        $withdrawApply = new WithdrawApply();
        $withdrawApply->audit_status = WithdrawApply::AUDIT_STATUS_APPLY;
        $withdrawApply->amount = $amount;
        $withdrawApply->type = $type;
        $withdrawApply->biz_type = $bizType;
        $withdrawApply->biz_id = $bizId;
        $withdrawApply->biz_name = $operatorName;
        $withdrawApply->audit_status = WithdrawApply::AUDIT_STATUS_APPLY;
        $withdrawApply->process_status = WithdrawApply::PROCESS_STATUS_UN_DEAL;
        $withdrawApply->version = 0;
        $withdrawApply->is_return = WithdrawApply::IS_RETURN_FALSE;
        $withdrawApply->remark = $remark;
        if (!$withdrawApply->save()){
            Yii::error(Json::htmlEncode($withdrawApply->errors));
            return [false,'提现申请单保存失败'];
        }
        $distributeBalanceItem = new DistributeBalanceItem();
        $distributeBalanceItem->company_id = $distributeBalance['company_id'];
        $distributeBalanceItem->biz_id = $bizId;
        $distributeBalanceItem->biz_type = $bizType;
        $distributeBalanceItem->user_id  = $userId;
        $distributeBalanceItem->type = DistributeBalanceItem::TYPE_WITHDRAW;
        $distributeBalanceItem->type_id = $withdrawApply->id;
        $distributeBalanceItem->distribute_balance_id = $distributeBalance['id'];
        $distributeBalanceItem->amount = $amount;
        $distributeBalanceItem->status = CommonStatus::STATUS_ACTIVE;
        $distributeBalanceItem->in_out = DistributeBalanceItem::IN_OUT_OUT;
        $distributeBalanceItem->operator_id = $userId;
        $distributeBalanceItem->operator_name = $operatorName;
        $distributeBalanceItem->remain_amount = $distributeBalance['amount']-$amount;
        $distributeBalanceItem->action = DistributeBalanceItem::ACTION_APPLY;
        if (!$distributeBalanceItem->save()){
            Yii::error(Json::htmlEncode($distributeBalanceItem->errors));
            return [false,'分润明细保存失败'];
        }
        $withdrawLog = new WithdrawLog();
        $withdrawLog->action = WithdrawLog::ACTION_OWNER_APPLY;
        $withdrawLog->withdraw_id = $withdrawApply->id;
        $withdrawLog->role =BizTypeEnum::$bizTypeMap[$bizType];
        $withdrawLog->operator_id = $userId;
        $withdrawLog->operator_name = $operatorName;
        if (!$withdrawLog->save()){
            Yii::error(Json::htmlEncode($withdrawLog->errors));
            return [false,'提现申请保存失败'];
        }
        if ($type==WithdrawApply::TYPE_WECHAT){
            $withdrawWechat = new WithdrawWechat();
            $withdrawWechat->amount = $amount;
            $withdrawWechat->withdraw_apply_id = $withdrawApply->id;
            $withdrawWechat->partner_trade_no = $withdrawWechat->generateNo();
            $withdrawWechat->openid = $openId;
            $withdrawWechat->re_user_name = $operatorName;
            $withdrawWechat->desc = "提现分润".Common::showAmountWithYuan($amount);
            $withdrawWechat->spbill_create_ip = Yii::$app->request->getUserIP();
            $withdrawWechat->status = WithdrawWechat::STATUS_UN_DEAL;
            if (!$withdrawWechat->save()){
                Yii::error(Json::htmlEncode($withdrawWechat->errors));
                return [false,'微信提现单保存失败'];
            }
        }
        $updateCount = DistributeBalance::updateAllCounters(
            ['amount'=>-$amount,'version'=>1],
            [
                'and',
                [
                    'id'=>$distributeBalance['id'],
                    'version'=>$distributeBalance['version'],
                ],
                ['>=','amount',$amount]
            ]);
        if ($updateCount<1){
            return [false,'分润余额更新失败'];
        }
        return [true,''];
    }


    /**
     * 创建提现申请单（用户余额）
     * @param $customerId
     * @param $amount
     * @param $type
     * @param $userId
     * @param $userName
     * @param $openId
     * @return array
     */
    public static function createCustomerBalanceWithdrawApply($customerId, $amount, $type, $userId, $userName, $openId){
        $bizType=BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET;
        if (!in_array($type,[WithdrawApply::TYPE_OFFLINE,WithdrawApply::TYPE_WECHAT])){
            return [false,'未知的提现方式'];
        }
        list($maxAndMinCheck,$maxAndMinCheckError) = self::checkMinAndMax($type,$amount);
        if (!$maxAndMinCheck){
            return [false,$maxAndMinCheckError];
        }
        if ($amount%10>0){
            return [false,'提现金额最小单位为分'];
        }
        $customerBalance = CustomerBalanceService::getByCustomerId($customerId);
        if ($customerBalance==null){
            return [false,'用户余额不足(无账户)'];
        }
        if ($customerBalance['amount']<$amount){
            return [false,'可提现余额不足'];
        }
        /**提现申请单*/
        $withdrawApply = new WithdrawApply();
        $withdrawApply->audit_status = WithdrawApply::AUDIT_STATUS_APPLY;
        $withdrawApply->amount = $amount;
        $withdrawApply->type = $type;
        $withdrawApply->biz_type = $bizType;
        $withdrawApply->biz_id = $customerId;
        $withdrawApply->biz_name = $userName;
        $withdrawApply->audit_status = WithdrawApply::AUDIT_STATUS_APPLY;
        $withdrawApply->process_status = WithdrawApply::PROCESS_STATUS_UN_DEAL;
        $withdrawApply->version = 0;
        $withdrawApply->is_return = WithdrawApply::IS_RETURN_FALSE;
        if (!$withdrawApply->save()){
            Yii::error(Json::htmlEncode($withdrawApply->errors));
            return [false,'提现申请单保存失败'];
        }


        $balanceItem = new CustomerBalanceItem();
        $balanceItem->customer_id = $customerId;
        $balanceItem->operator_id = $userId;
        $balanceItem->operator_name = $userName;
        $balanceItem->status = CustomerBalanceItem::STATUS_ACTIVE;
        $balanceItem->action = CustomerBalanceItem::ACTION_APPLY;
        $balanceItem->in_out = CustomerBalanceItem::IN_OUT_OUT;
        $balanceItem->biz_type = CustomerBalanceItem::BIZ_TYPE_CUSTOMER_WITHDRAW;
        $balanceItem->biz_code = (string) $withdrawApply->id;
        $balanceItem->remark = '用户余额提现';
        $balanceItem->amount = $amount;
        $balanceItem->remain_amount = $customerBalance['amount']-$amount;
        if (!$balanceItem->save()){
            Yii::error(Json::htmlEncode($balanceItem->errors));
            return [false,'余额日志插入失败'];
        }

        $withdrawLog = new WithdrawLog();
        $withdrawLog->action = WithdrawLog::ACTION_OWNER_APPLY;
        $withdrawLog->withdraw_id = $withdrawApply->id;
        $withdrawLog->role =BizTypeEnum::$bizTypeMap[$bizType];
        $withdrawLog->operator_id = $userId;
        $withdrawLog->operator_name = $userName;
        if (!$withdrawLog->save()){
            Yii::error(Json::htmlEncode($withdrawLog->errors));
            return [false,'提现申请保存失败'];
        }
        if ($type==WithdrawApply::TYPE_WECHAT){
            $withdrawWechat = new WithdrawWechat();
            $withdrawWechat->amount = $amount;
            $withdrawWechat->withdraw_apply_id = $withdrawApply->id;
            $withdrawWechat->partner_trade_no = $withdrawWechat->generateNo();
            $withdrawWechat->openid = $openId;
            $withdrawWechat->re_user_name = $userName;
            $withdrawWechat->desc = "提现余额".Common::showAmountWithYuan($amount);
            $withdrawWechat->spbill_create_ip = Yii::$app->request->getUserIP();
            $withdrawWechat->status = WithdrawWechat::STATUS_UN_DEAL;
            if (!$withdrawWechat->save()){
                Yii::error(Json::htmlEncode($withdrawWechat->errors));
                return [false,'微信提现单保存失败'];
            }
        }

        $updateCount = CustomerBalance::updateAllCounters(
            [
                'amount'=>-$amount,
                'version'=>1
            ],
            [
                'and',
                [
                    'id'=>$customerBalance['id'],
                    'version'=>$customerBalance['version']
                ],
                ['>=','amount',$amount]
            ]);
        if ($updateCount<1){
            return [false,'余额更新失败'];
        }

        return [true,''];
    }

    /**
     * 校验不同的提现方式对应的最小提现金额
     * @param $type
     * @param $amount
     * @return array
     */
    private static function checkMinAndMax($type,$amount){
        $withdraws = \Yii::$app->params['withdraw'];
        if (key_exists($type,$withdraws)){
            $withdraw = $withdraws[$type];
            if ($amount<$withdraw['min_amount']){
                return [false,'最小提现金额为'.Common::showAmountWithYuan($withdraw['min_amount'])];
            }
            else if ($amount>$withdraw['max_amount']){
                return [false,'最大提现金额为'.Common::showAmountWithYuan($withdraw['max_amount'])];
            }
            return [true,''];
        }
        else{
            return [false,'未知的提现方式'];
        }
    }

    /**
     * 获取提现中的金额
     * @param $bizId
     * @param $bizType
     * @return mixed
     */
    public static function getWithdrawingAmount($bizId,$bizType){
        $withdrawingAmount = (new Query())->from(WithdrawApply::tableName())
            ->select(['COALESCE(SUM(amount),0)  as amount'])
            ->where([
                'biz_id'=>$bizId,
                'biz_type'=>$bizType,
                'process_status'=>WithdrawApply::PROCESS_STATUS_UN_DEAL,
                'is_return'=>WithdrawApply::IS_RETURN_FALSE
            ])->one();
        return $withdrawingAmount['amount'];
    }


}