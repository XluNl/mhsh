<?php
namespace alliance\modules\alliance\controllers;
use alliance\components\FController;
use alliance\services\CaptchaService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use Yii;

class CaptchaController extends FController {

	public function actionReg() {
		$phone = Yii::$app->request->get("phone");
		$sort = Yii::$app->request->get("sort");
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone缺失'));
        ExceptionAssert::assertNotBlank($sort,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sort缺失'));
        CaptchaService::sendCaptcha($phone,$sort);
		return RestfulResponse::success(true);
	}

}