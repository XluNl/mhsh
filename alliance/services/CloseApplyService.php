<?php


namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\BizTypeEnum;
use common\models\CloseApply;
use common\utils\ArrayUtils;
use Yii;

class CloseApplyService  extends \common\services\CloseApplyService
{
    /**
     * 获取申请记录
     * @param $id
     * @param $userId
     * @param $allianceId
     * @param bool $model
     * @return array|bool|\common\models\CloseApply|\yii\db\ActiveRecord|null
     */
    public static function requireModel($userId,$allianceId,$model=false){
        $model = self::getModelByIdAndUserId($userId,BizTypeEnum::BIZ_TYPE_HA,$allianceId,$model);
        $model  = self::setVOText($model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::CLOSE_APPLY_NOT_EXIST));
        return $model;
    }


    /**
     * 尝试申请
     * @param $model
     * @return mixed
     * @throws BusinessException
     */
    public static function applyModel(&$model){
        ExceptionAssert::assertTrue(key_exists($model->biz_type,CloseApply::$applyTypeArr),StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,"申请类型错误"));
        $model->scenario = ArrayUtils::getArrayValue($model->biz_type,CloseApply::$applyTypeName);
        ExceptionAssert::assertTrue($model->validate(),StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,"申请参数缺失"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $closeApply = self::getModelByIdAndUserId($model->user_id,BizTypeEnum::BIZ_TYPE_HA,$model->biz_id);
            self::canApplying($model->user_id,$model->biz_id,$model->company_id);
            if (!empty($closeApply)){
                ExceptionAssert::assertTrue(self::removePreApply($model->user_id,BizTypeEnum::BIZ_TYPE_HA,$model->biz_id,$model->company_id),StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,"移除上次申请失败"));
            }
            ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,"保存失败"));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            Yii::error($e);
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,$e->getMessage()));
        }

        return $model;
    }

    /**
     * 撤销申请
     * @param $userId
     * @param $allianceId
     */
    public static function cancel($userId,$allianceId){
        $closeApply = self::getModelByIdAndUserId($userId,BizTypeEnum::BIZ_TYPE_HA,$allianceId);
        ExceptionAssert::assertNotNull($closeApply,StatusCode::createExp(StatusCode::CLOSE_APPLY_NOT_EXIST));
        ExceptionAssert::assertTrue(self::cancelApply($closeApply['id'],$userId,BizTypeEnum::BIZ_TYPE_HA,$allianceId),StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"当前状态不支持撤回"));
    }

    /**
     * 判断是否存在
     * @param $userId
     * @param $allianceId
     * @param $companyId
     */
    public static function canApplying($userId, $allianceId, $companyId){
        list($res,$error) = self::checkApplying($userId,BizTypeEnum::BIZ_TYPE_HA,$allianceId,$companyId);
        ExceptionAssert::assertTrue($res,StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,$error));
    }

}