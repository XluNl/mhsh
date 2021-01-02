<?php

namespace frontend\modules\customer\controllers;
use frontend\services\CityService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class CityController extends Controller {

    public function actionSearch() {
        $name = Yii::$app->request->get("name");
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"name不能为空"));
        $data = CityService::searchCityByName($name);
        return RestfulResponse::success($data);
    }

	public function actionOpen() {
	    $data = CityService::getOpenCities();
        return RestfulResponse::success($data);
	}


}