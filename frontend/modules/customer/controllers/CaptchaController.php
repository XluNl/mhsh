<?php
namespace frontend\modules\customer\controllers;
use frontend\services\CaptchaService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class CaptchaController extends Controller {

	public function actionReg() {
		$phone = Yii::$app->request->get("phone");
		$sort = Yii::$app->request->get("sort");
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone不能为空'));
        ExceptionAssert::assertNotBlank($sort,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sort不能为空'));
        CaptchaService::sendCaptcha($phone,$sort);
		return RestfulResponse::success(true);
	}

}