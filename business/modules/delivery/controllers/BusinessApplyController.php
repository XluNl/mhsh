<?php
namespace business\modules\delivery\controllers;

use business\components\FController;
use business\models\BusinessCommon;
use business\services\BusinessApplyService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\BusinessApply;
use common\utils\StringUtils;
use Yii;

class BusinessApplyController extends FController {

    public function actionApply(){
        $userId = BusinessCommon::requiredUserId();
        $modelId = BusinessCommon::getModelValueFromFormData('BusinessApply');
        if (!StringUtils::isBlank($modelId)){
            $model = BusinessApplyService::requireModel($modelId,$userId,true);
        }
        else{
            $model = new BusinessApply();
            $model->loadDefaultValues();
        }
        $load = $model->load(Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"数据格式错误"));
        $model->user_id = $userId;
        BusinessApplyService::applyModel($model);
        return RestfulResponse::success($model->id);
    }

    public function actionList(){
        $userId = BusinessCommon::requiredUserId();
        $models = BusinessApplyService::getModelByUserId($userId);
        return RestfulResponse::success($models);
    }

    public function actionCancel(){
        $userId = BusinessCommon::requiredUserId();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotBlank($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id缺失'));
        BusinessApplyService::cancel($id,$userId);
        return RestfulResponse::success(true);
    }

    public function actionExist(){
        $userId = BusinessCommon::requiredUserId();
        $applyType = Yii::$app->request->get("apply_type");
        $companyId = Yii::$app->request->get("company_id");
        BusinessApplyService::existApplying($userId,$applyType,$companyId);
        return RestfulResponse::success(true);
    }

}