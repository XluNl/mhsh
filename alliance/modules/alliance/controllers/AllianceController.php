<?php

namespace alliance\modules\alliance\controllers;
use alliance\models\AllianceCommon;
use alliance\services\AllianceService;
use alliance\services\RegionService;
use alliance\services\WechatPayLogService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use common\utils\PhoneUtils;
use Yii;
use yii\web\Controller;

class AllianceController extends Controller {

    public $enableCsrfValidation = false;
	public function actionChange() {
        $allianceId = Yii::$app->request->get("alliance_id");
        ExceptionAssert::assertNotNull($allianceId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'alliance_id'));
        $userId= AllianceCommon::requiredUserId();
        AllianceService::changeSelectedId($userId,$allianceId);
		return RestfulResponse::success($allianceId);
	}

	public function actionList(){
        $userId= AllianceCommon::requiredUserId();
        $allianceModels = AllianceService::getListWithDefault($userId);
        AllianceService::batchGetDisplayVO($allianceModels);
        RegionService::batchSetProvinceAndCityAndCounty($allianceModels);
        PhoneUtils::batchReplacePhoneMark($allianceModels,"phone");
        PhoneUtils::batchReplacePhoneMark($allianceModels,"em_phone");
        return RestfulResponse::success($allianceModels);
    }

    public function actionInfo() {
        $userId= AllianceCommon::requiredUserId();
        $allianceId = AllianceService::getSelectedId($userId);
        $allianceModel = AllianceService::getActiveModel($allianceId);
        $allianceModel = AllianceService::completeImageUrlText($allianceModel);
        RegionService::setProvinceAndCityAndCounty($allianceModel);
        PhoneUtils::replacePhoneMark($allianceModel,"phone");
        PhoneUtils::replacePhoneMark($allianceModel,"em_phone");
        return RestfulResponse::success($allianceModel);
    }

    public function actionModify() {
        $userId= AllianceCommon::requiredUserId();
        $allianceId = AllianceService::getSelectedId($userId);
        $model = AllianceService::requiredModel($allianceId,true);
        $load = $model->load(Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::ALLIANCE_MODIFY_ERROR,"数据格式错误"));
        AllianceService::checkCanModifyAttr($model);
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::ALLIANCE_MODIFY_ERROR,"保存失败"));
        return RestfulResponse::success($model->getAttributes());
    }

    public function actionAuth() {
        $user = AllianceCommon::requiredUserModel();
        $allianceModel = AllianceCommon::requiredAlliance();
        $config = AllianceService::auth($user,$allianceModel);
        return RestfulResponse::success($config);
    }

    public function actionCancelAuth() {
        $allianceModel = AllianceCommon::requiredAlliance();
        $log = WechatPayLogService::getAllianceAuthPayLog($allianceModel['id']);
        return RestfulResponse::success($log);
    }

    public function actionNotify(){
        Yii::error(Yii::$app->request->getRawBody(),'pay');
        $response = Yii::$app->allianceWechat->payment->handlePaidNotify(function ($message, $fail) {
            return AllianceService::payCallBack($message,$fail);
        });
        $response->send();
        exit(0);
    }
}