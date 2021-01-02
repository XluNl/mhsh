<?php
namespace business\modules\delivery\controllers;
use business\services\CaptchaService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;
use yii\web\Controller;

class CaptchaController extends Controller {

	public function actionReg() {
		$phone = Yii::$app->request->get("phone");
		$sort = Yii::$app->request->get("sort");
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone缺失'));
        ExceptionAssert::assertNotBlank($sort,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sort缺失'));
        CaptchaService::sendCaptcha($phone,$sort);
		return RestfulResponse::success(true);
	}

}