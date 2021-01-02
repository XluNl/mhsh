<?php
namespace alliance\controllers;
use alliance\services\DownloadFileService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use Yii;
use yii\web\Controller;


class DownloadController extends Controller {
    public $enableCsrfValidation = false;
	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

    public function actionFile()
    {
        try {
            $url = Yii::$app->request->get('url');
            $ext = Yii::$app->request->get('ext');
            ExceptionAssert::assertNotBlank($url,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'url'));
            ExceptionAssert::assertNotBlank($ext,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ext'));
            $info = DownloadFileService::downloadFile($url,"/uploads/pub",$ext);
            return RestfulResponse::success($info);
        } catch (\Exception $e) {
            return RestfulResponse::error($e);
        }
    }
}
