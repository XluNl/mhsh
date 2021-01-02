<?php


namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use business\models\BusinessCommon;
use common\models\BusinessApply;
use common\models\CommonStatus;
use common\utils\ArrayUtils;
use yii\db\Query;

class BusinessApplyService  extends \common\services\BusinessApplyService
{
    /**
     * 获取申请记录
     * @param $id
     * @param $userId
     * @param bool $model
     * @return array|bool|BusinessApply|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id,$userId,$model=false){
        $model = self::getModelByIdAndUserId($id,$userId,$model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::BUSINESS_APPLY_NOT_EXIST));
        return $model;
    }

    /**
     * 根据userId获取申请列表
     * @param $userId
     * @return array
     */
    public static function getModelByUserId($userId){
        $models = (new Query())->from(BusinessApply::tableName())->where(['user_id'=>$userId,'status'=>CommonStatus::STATUS_ACTIVE,'type'=>BusinessApply::APPLY_TYPE_HA])->orderBy('id desc')->all();
        $models = BusinessApplyDisplayVOService::batchSetVOText($models);
        return $models;
    }

    /**
     * @param BusinessApply $model
     * @return array|bool|BusinessApply|\yii\db\ActiveRecord|null
     */
    public static function applyModel(&$model){
        ExceptionAssert::assertTrue(key_exists($model->type,BusinessApply::$applyTypeArr),StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"申请类型错误"));
        $model->scenario = ArrayUtils::getArrayValue($model->type,BusinessApply::$applyTypeName);
        ExceptionAssert::assertTrue($model->validate(),StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,BusinessCommon::getModelErrors($model)));
        self::existApplying($model->user_id,$model->type,$model->company_id);
        //校验是否还能再开新店铺
        list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateAlliance($model['user_id']);
        ExceptionAssert::assertTrue($checkError,StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,$checkErrorMsg));

        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"保存失败"));
        return $model;
    }

    /**
     * 撤销申请
     * @param $id
     * @param $userId
     */
    public static function cancel($id,$userId){
        ExceptionAssert::assertTrue(self::cancelApply($id,$userId),StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"撤销保存失败"));
    }

    public static function existApplying($userId,$type,$companyId){
        list($res,$error) = self::checkApplying($userId,$type,$companyId);
        ExceptionAssert::assertTrue($res,StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,$error));
    }

}