<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\BizTypeEnum;
use common\models\CustomerBalance;
use common\models\CustomerBalanceItem;
use common\models\DistributeBalance;
use common\models\DistributeBalanceItem;
use common\models\RoleEnum;
use common\models\WithdrawApply;
use common\models\WithdrawLog;
use common\models\WithdrawWechat;
use common\services\CustomerBalanceItemService;
use common\services\DistributeBalanceService;
use common\utils\DateTimeUtils;
use Yii;

class WithdrawApplyService extends \common\services\WithdrawApplyService
{
    /**
     * 审核操作
     * @param $id
     * @param $commander
     * @param $operatorId
     * @param $auditRemark
     * @param $operatorName
     * @param $validateException
     */
    public static function auditOperation($id,$commander,$auditRemark,$operatorId,$operatorName,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[WithdrawApply::AUDIT_STATUS_ACCEPT,WithdrawApply::AUDIT_STATUS_DENY]),$validateException);
        $withdrawApply = parent::getModel($id);
        BExceptionAssert::assertNotNull($withdrawApply,$validateException->updateMessage("提现申请单不存在"));
        $accessBizType = BizTypeEnum::getBizTypeShowArrKey(BackendCommon::getFCompanyId());
        BExceptionAssert::assertNotNull(in_array($withdrawApply['biz_type'],$accessBizType),$validateException->updateMessage("提现申请单不存在"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($commander==WithdrawApply::AUDIT_STATUS_ACCEPT){
                self::auditAccept($withdrawApply,$operatorId,$operatorName,$auditRemark);
            }
            else if ($commander==WithdrawApply::AUDIT_STATUS_DENY){
                self::auditDeny($withdrawApply,$operatorId,$operatorName,$auditRemark);
            }
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            BackendCommon::showWarningInfo($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 处理打款流程
     * @param $id
     * @param $operatorId
     * @param $operatorName
     * @param $validateException RedirectParams
     * @throws \yii\db\Exception
     */
    public static function processDeal($id,$operatorId,$operatorName,$validateException){
        $withdrawApply = parent::getModel($id);
        BExceptionAssert::assertNotNull($withdrawApply,$validateException->updateMessage("提现申请单不存在"));
        BExceptionAssert::assertTrue($withdrawApply['audit_status']==WithdrawApply::AUDIT_STATUS_ACCEPT&&$withdrawApply['process_status']==WithdrawApply::PROCESS_STATUS_UN_DEAL,$validateException->updateMessage("提现申请单不存在"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($withdrawApply['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
                if ($withdrawApply['type']==WithdrawApply::TYPE_OFFLINE){
                    self::processDistributeBalanceOfflineDeal($withdrawApply,$operatorId,$operatorName);
                }
                else if ($withdrawApply['type']==WithdrawApply::TYPE_WECHAT){
                    self::processCustomerBalanceWechatDeal($withdrawApply,$operatorId,$operatorName);
                }
                else{
                    BExceptionAssert::assertTrue(false, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'未知的提现方式'));
                }
            }
            else{
                if ($withdrawApply['type']==WithdrawApply::TYPE_OFFLINE){
                    self::processDistributeBalanceOfflineDeal($withdrawApply,$operatorId,$operatorName);
                }
                else if ($withdrawApply['type']==WithdrawApply::TYPE_WECHAT){
                    self::processDistributeBalanceWechatDeal($withdrawApply,$operatorId,$operatorName);
                }
                else{
                    BExceptionAssert::assertTrue(false, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'未知的提现方式'));
                }
            }

            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            BackendCommon::showWarningInfo($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 手动退回余额
     * @param $id
     * @param $operatorId
     * @param $operatorName
     * @param $validateException
     */
    public static function manualRefund($id,$operatorId,$operatorName,$validateException){
        $withdrawApply = parent::getModel($id);
        BExceptionAssert::assertNotNull($withdrawApply,$validateException->updateMessage("提现申请单不存在"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($withdrawApply['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
                self::refundWithdrawForCustomerBalance($withdrawApply);
            }
            else{
                self::refundWithdrawForDistributeBalance($withdrawApply);
            }
            self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_RETURN);
            $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'process_status'=>WithdrawApply::PROCESS_STATUS_FAILED,'is_return'=>WithdrawApply::IS_RETURN_TRUE],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'process_status'=>WithdrawApply::PROCESS_STATUS_UN_DEAL,'is_return'=>WithdrawApply::IS_RETURN_FALSE]);
            BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'申请单更新失败'));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            BackendCommon::showWarningInfo($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 线下打款（分润）
     * @param $withdrawApply
     * @param $operatorId
     * @param $operatorName
     */
    private static function processDistributeBalanceOfflineDeal($withdrawApply, $operatorId, $operatorName){
        $distributeBalanceItem = DistributeBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($distributeBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'分润日志不存在'));
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_DEAL);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'process_status'=>WithdrawApply::PROCESS_STATUS_SUCCESS],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'process_status'=>[WithdrawApply::PROCESS_STATUS_UN_DEAL]]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'申请单更新失败'));
        $updateCount = DistributeBalanceItem::updateAll(['action'=>DistributeBalanceItem::ACTION_ACCEPT,'remark'=>'已线下打款成功','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$distributeBalanceItem['id'],'action'=>DistributeBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润日志更新失败'));
    }

    /**
     * 线下打款（用户余额）
     * @param $withdrawApply
     * @param $operatorId
     * @param $operatorName
     */
    private static function processCustomerBalanceOfflineDeal($withdrawApply, $operatorId, $operatorName){
        $customerBalanceItem = CustomerBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($customerBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'提现日志不存在'));
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_DEAL);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'process_status'=>WithdrawApply::PROCESS_STATUS_SUCCESS,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'process_status'=>[WithdrawApply::PROCESS_STATUS_UN_DEAL]]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'申请单更新失败'));
        $updateCount = CustomerBalanceItem::updateAll(['action'=>CustomerBalanceItem::ACTION_ACCEPT,'remark'=>'已线下打款成功','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerBalanceItem['id'],'action'=>CustomerBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润日志更新失败'));
    }

    /**
     * 微信打款（分润）
     * @param $withdrawApply
     * @param $operatorId
     * @param $operatorName
     */
    private static function processDistributeBalanceWechatDeal($withdrawApply, $operatorId, $operatorName){
        $distributeBalanceItem = DistributeBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($distributeBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'分润日志不存在'));
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_DEAL);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'process_status'=>WithdrawApply::PROCESS_STATUS_SUCCESS,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'process_status'=>[WithdrawApply::PROCESS_STATUS_UN_DEAL,WithdrawApply::PROCESS_STATUS_FAILED]]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'申请单更新失败'));
        $updateCount = DistributeBalanceItem::updateAll(['action'=>DistributeBalanceItem::ACTION_ACCEPT,'remark'=>'已微信打款成功','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$distributeBalanceItem['id'],'action'=>DistributeBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润日志更新失败'));

        //微信打款
        $withdrawWechat = WithdrawWechatService::getModel($withdrawApply['id']);
        BExceptionAssert::assertNotNull($withdrawWechat, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信打款单未生成'));
        BExceptionAssert::assertNotNull(in_array($withdrawWechat['status'],[WithdrawWechat::STATUS_UN_DEAL]), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'只有未打款才能重试'));
        WithdrawWechatService::createPayment($withdrawApply);
    }


    /**
     * 微信打款（用户余额）
     * @param $withdrawApply
     * @param $operatorId
     * @param $operatorName
     */
    private static function processCustomerBalanceWechatDeal($withdrawApply, $operatorId, $operatorName){
        $customerBalanceItem = CustomerBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($customerBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'提现日志不存在'));
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_DEAL);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'process_status'=>WithdrawApply::PROCESS_STATUS_SUCCESS,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'process_status'=>[WithdrawApply::PROCESS_STATUS_UN_DEAL,WithdrawApply::PROCESS_STATUS_FAILED]]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'申请单更新失败'));
        $updateCount = CustomerBalanceItem::updateAll(['action'=>CustomerBalanceItem::ACTION_ACCEPT,'remark'=>'已微信打款成功','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerBalanceItem['id'],'action'=>CustomerBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润日志更新失败'));

        //微信打款
        $withdrawWechat = WithdrawWechatService::getModel($withdrawApply['id']);
        BExceptionAssert::assertNotNull($withdrawWechat, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'微信打款单未生成'));
        BExceptionAssert::assertNotNull(in_array($withdrawWechat['status'],[WithdrawWechat::STATUS_UN_DEAL]), BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_DEAL_ERROR,'只有未打款才能重试'));
        WithdrawWechatService::createPayment($withdrawApply);
    }

    /**
     * @param $withdrawApply WithdrawApply
     * @param $operatorId
     * @param $operatorName
     * @param $auditRemark
     */
    private static function auditAccept($withdrawApply,$operatorId,$operatorName,$auditRemark){
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_ACCEPT);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'audit_status'=>WithdrawApply::AUDIT_STATUS_ACCEPT,'audit_remark'=>$auditRemark],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'申请单更新失败'));
    }

    /**
     * 审核拒绝并退款
     * @param $withdrawApply
     * @param $operatorId
     * @param $operatorName
     * @param $auditRemark
     */
    private static function auditDeny($withdrawApply,$operatorId,$operatorName,$auditRemark){
        if ($withdrawApply['biz_type']==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
            self::refundWithdrawForCustomerBalance($withdrawApply);
        }
        else{
            self::refundWithdrawForDistributeBalance($withdrawApply);
        }
        self::createLog($withdrawApply['id'],$operatorId,$operatorName,WithdrawLog::ACTION_ADMIN_DENY);
        $updateCount = WithdrawApply::updateAll(['version'=>$withdrawApply['version']+1,'audit_status'=>WithdrawApply::AUDIT_STATUS_DENY,'audit_remark'=>$auditRemark,'is_return'=>WithdrawApply::IS_RETURN_TRUE],['id'=>$withdrawApply['id'],'version'=>$withdrawApply['version'],'audit_status'=>WithdrawApply::AUDIT_STATUS_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'申请单更新失败'));
    }

    /**
     * 创建提现日志
     * @param $withdrawId
     * @param $operatorId
     * @param $operatorName
     * @param $action
     */
    public static function createLog($withdrawId,$operatorId,$operatorName,$action){
        $withdrawLog = new WithdrawLog();
        $withdrawLog->action = $action;
        $withdrawLog->withdraw_id=$withdrawId;
        $withdrawLog->role = RoleEnum::ROLE_ADMIN;
        $withdrawLog->operator_id = $operatorId;
        $withdrawLog->operator_name = $operatorName;
        BExceptionAssert::assertTrue($withdrawLog->save(), BStatusCode::createExp(BStatusCode::WITHDRAW_ADD_LOG_ERROR));
    }

    /**
     * 退款提现金额(分润)
     * @param $withdrawApply WithdrawApply
     */
    private static function refundWithdrawForDistributeBalance($withdrawApply){
        $distributeBalanceItem = DistributeBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($distributeBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'分润日志不存在'));
        $distributeBalance = DistributeBalanceService::getModel($distributeBalanceItem['distribute_balance_id']);
        BExceptionAssert::assertNotNull($distributeBalance, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'分润主体不存在'));
        $updateCount = DistributeBalance::updateAll(['version'=>$distributeBalance['version']+1,'amount'=>$distributeBalance['amount']+$withdrawApply['amount'],'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$distributeBalance['id'],'version'=>$distributeBalance['version']]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润主体更新失败'));
        $updateCount = DistributeBalanceItem::updateAll(['action'=>DistributeBalanceItem::ACTION_DENY,'remark'=>'提现金额已退回','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$distributeBalanceItem['id'],'action'=>DistributeBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'分润日志更新失败'));
    }

    /**
     * 退款提现金额(用户余额)
     * @param $withdrawApply WithdrawApply
     */
    private static function refundWithdrawForCustomerBalance($withdrawApply){
        $customerBalanceItem = CustomerBalanceItemService::getModelByWithdrawId($withdrawApply['id']);
        BExceptionAssert::assertNotNull($customerBalanceItem, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'提现日志不存在'));
        $customerBalance = CustomerBalanceService::getByCustomerId($customerBalanceItem['customer_id']);
        BExceptionAssert::assertNotNull($customerBalance, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_AUDIT_ERROR,'余额账户不存在'));
        $updateCount = CustomerBalance::updateAll(['version'=>$customerBalance['version']+1,'amount'=>$customerBalance['amount']+$withdrawApply['amount'],'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerBalance['id'],'version'=>$customerBalance['version']]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'余额账户更新失败'));
        $updateCount = CustomerBalanceItem::updateAll(['action'=>CustomerBalanceItem::ACTION_DENY,'remark'=>'提现金额已退回','updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerBalanceItem['id'],'action'=>CustomerBalanceItem::ACTION_APPLY]);
        BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_REFUND_ERROR,'账户日志更新失败'));
    }

    /**
     * 提现
     * @param $companyId
     * @param $amount
     * @param $type
     * @param $operatorId
     * @param $operatorName
     */
    public static function createCustomerBalanceWithdrawApplyB($companyId, $amount, $type,$operatorId,$operatorName){
        BExceptionAssert::assertTrue(in_array($type,[WithdrawApply::TYPE_OFFLINE,WithdrawApply::TYPE_WECHAT]),BBusinessException::create("未知的提现方式"));
        if (BackendCommon::isSuperCompany($companyId)){
            list($result,$error) = parent::createDistributeBalanceWithdrawApply($companyId,BizTypeEnum::BIZ_TYPE_COMPANY,$amount,$type,$operatorId,$operatorName,null,"");
        }
        else{
            list($result,$error) = parent::createDistributeBalanceWithdrawApply($companyId,BizTypeEnum::BIZ_TYPE_AGENT,$amount,$type,$operatorId,$operatorName,null,"");
        }
        BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::WITHDRAW_ERROR,$error));
    }

}