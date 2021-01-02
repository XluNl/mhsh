<?php

namespace business\modules\delivery\controllers;
use business\models\BusinessCommon;
use business\services\PopularizerService;
use business\services\RegionService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;
use yii\web\Controller;

class PopularizerController extends Controller {

	public function actionChange() {
        $popularizer_id = Yii::$app->request->get("popularizer_id");
        ExceptionAssert::assertNotNull($popularizer_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'popularizer_id'));
        $userId= BusinessCommon::requiredUserId();
        PopularizerService::changeSelectedPopularizerId($userId,$popularizer_id);
		return RestfulResponse::success($popularizer_id);
	}

	public function actionList(){
        $userId= BusinessCommon::requiredUserId();
        $popularizerModels = PopularizerService::getListWithDefault($userId);
        return RestfulResponse::success($popularizerModels);
    }

    public function actionInfo() {
        $userId= BusinessCommon::requiredUserId();
        $popularizerId = PopularizerService::getSelectedPopularizerId($userId);
        $popularizerModel = PopularizerService::getActiveModel($popularizerId);
        RegionService::setProvinceAndCityAndCounty($popularizerModel);
        return RestfulResponse::success($popularizerModel);
    }

}