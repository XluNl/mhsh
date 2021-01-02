<?php
namespace alliance\modules\alliance\controllers;

use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\CloseApplyService;
use alliance\services\GoodsDisplayDomainService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use common\models\BizTypeEnum;
use common\models\CloseApply;
use Yii;

class CloseApplyController extends FController {

    public function actionApply(){
        $userId = AllianceCommon::requiredUserId();
        $companyId = AllianceCommon::getFCompanyId();
        $alliance = AllianceCommon::requiredAlliance();
        $model = new CloseApply();
        $model->loadDefaultValues();
        $load = $model->load(Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::CLOSE_APPLY_OPERATION_ERROR,"数据格式错误"));
        $model->id = null;
        $model->user_id = $userId;
        $model->biz_type = BizTypeEnum::BIZ_TYPE_HA;
        $model->biz_id = $alliance['id'];
        $model->name = $alliance['nickname'];
        $model->phone = $alliance['phone'];
        $model->company_id = $companyId;
        CloseApplyService::applyModel($model);
        return RestfulResponse::success($model->id);
    }

    public function actionInfo(){
        $userId = AllianceCommon::requiredUserId();
        $allianceId = AllianceCommon::requiredAllianceId();
        $closeApply = CloseApplyService::getModelByIdAndUserId($userId,BizTypeEnum::BIZ_TYPE_HA,$allianceId);
        $closeApply = GoodsDisplayDomainService::renameImageUrl($closeApply,"images");
        $closeApply = CloseApplyService::setVOText($closeApply);
        return RestfulResponse::success($closeApply);
    }

    public function actionCancel(){
        $userId = AllianceCommon::requiredUserId();
        $allianceId = AllianceCommon::requiredAllianceId();
        CloseApplyService::cancel($userId,$allianceId);
        return RestfulResponse::success(true);
    }

}