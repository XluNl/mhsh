<?php


namespace backend\services;


use alliance\services\WechatPayLogService;
use backend\models\BackendCommon;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Alliance;
use common\models\BizTypeEnum;
use common\models\CloseApply;
use common\models\CommonStatus;
use common\models\WechatPayLog;
use common\utils\StringUtils;
use Yii;
use yii\data\ActiveDataProvider;

class CloseApplyService extends \common\services\CloseApplyService
{

    /**
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function renameImages($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        GoodsDisplayDomainService::batchRenameImageUrl($models,'images');
        $dataProvider->setModels($models);
        return $dataProvider;
    }

    /**
     * 操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operationRemark
     * @param $validateException
     */
    public static function operate($id,$commander,$company_id,$operatorId,$operatorName,$operationRemark,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CloseApply::ACTION_ACCEPT,CloseApply::ACTION_DENY,CloseApply::ACTION_DELETED]),$validateException);
        try{
            if ($commander==CloseApply::ACTION_ACCEPT){
                self::acceptAndClose($id,$company_id,$operatorId,$operatorName,$operationRemark);
            }
            else if ($commander==CloseApply::ACTION_DENY){
                self::denyAndUpdate($id,$company_id,$operatorId,$operatorName,$operationRemark);
            }
            else if ($commander==CloseApply::ACTION_DELETED){
                self::softDelete($id,$company_id,$operatorId,$operatorName,$operationRemark);
            }
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            BackendCommon::showWarningInfo($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException);
        }
    }


    /**
     * 接受并创建
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     * @throws BBusinessException
     * @throws \Exception
     */
    public static function acceptAndClose($id, $company_id, $operatorId, $operatorName, $operatorRemark){
        $closeApply = self::getModel($id,$company_id);
        BExceptionAssert::assertNotNull($closeApply,BBusinessException::create("申请不存在"));
        BExceptionAssert::assertTrue($closeApply['action']==CloseApply::ACTION_APPLY,BBusinessException::create("申请已处理，请勿重复处理"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($closeApply['biz_type']==BizTypeEnum::BIZ_TYPE_HA){
                self::closeAlliance($closeApply);
            }
            $updateCount = CloseApply::updateAll(['action'=>CloseApply::ACTION_ACCEPT,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>CloseApply::ACTION_APPLY]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息更新失败"));
            $transaction->commit();
        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BBusinessException::create($e->getMessage()));
        }
    }

    /**
     * 关闭联盟点并将保证金退回
     * @param $closeApply CloseApply
     */
    private static function closeAlliance($closeApply){
        $paymentSdk = Yii::$app->allianceWechat->payment;
        $alliance = AllianceService::getActiveModel($closeApply['biz_id'],$closeApply['company_id'],true);
        BExceptionAssert::assertTrue($alliance['status']!=Alliance::STATUS_OFFLINE,BBusinessException::create("联盟店铺已关闭"));
        if ($alliance['auth']==Alliance::AUTH_STATUS_AUTH&&StringUtils::isNotBlank($alliance['auth_id'])){
            $wechatPayLog = WechatPayLogService::getModel($alliance['auth_id']);
            if (!empty($wechatPayLog)){
                $refundDesc = "联盟保证金退回".$wechatPayLog['biz_id'];
                list($error,$errMsg) = WechatPayLogService::refund($wechatPayLog['total_fee'],$wechatPayLog,$refundDesc,$paymentSdk,WechatPayLog::BIZ_TYPE_ALLIANCE_AUTH,$wechatPayLog['biz_id']);
                BExceptionAssert::assertTrue($error,BBusinessException::create($errMsg));
                $alliance->auth = Alliance::AUTH_STATUS_CANCEL_AUTH;
            }
        }
        $alliance->status = Alliance::STATUS_OFFLINE;
        BExceptionAssert::assertTrue($alliance->save(),BBusinessException::create("联盟店铺信息更新失败"));
    }

    /**
     * 拒绝申请
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     * @throws BBusinessException
     * @throws \Exception
     */
    public static function denyAndUpdate($id,$company_id,$operatorId,$operatorName,$operatorRemark){
        $closeApply = self::getModel($id,$company_id);
        BExceptionAssert::assertNotNull($closeApply,BBusinessException::create("申请不存在"));
        BExceptionAssert::assertTrue($closeApply['action']==CloseApply::ACTION_APPLY,BBusinessException::create("申请已处理，请勿重复处理"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $updateCount = CloseApply::updateAll(['action'=>CloseApply::ACTION_DENY,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>CloseApply::ACTION_APPLY]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息更新失败"));
            $transaction->commit();
        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BBusinessException::create($e->getMessage()));
        }
    }

    /**
     * 删除申请
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $operatorRemark
     */
    public static function softDelete($id, $company_id, $operatorId, $operatorName, $operatorRemark){
        $updateCount = CloseApply::updateAll(['status'=>CommonStatus::STATUS_DISABLED,'operator_id'=>$operatorId,'operator_name'=>$operatorName,'operator_remark'=>$operatorRemark],['id'=>$id,'company_id'=>$company_id,'action'=>CloseApply::ACTION_APPLY,'status'=>CommonStatus::STATUS_ACTIVE]);
        BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("申请信息删除失败"));
    }
}