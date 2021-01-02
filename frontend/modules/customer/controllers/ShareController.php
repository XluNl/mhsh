<?php
namespace frontend\modules\customer\controllers;

use frontend\components\FController;
use frontend\services\ShareService;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Response;

class ShareController extends FController {

    public function actionShare(){
        $path = Yii::$app->request->get('path');
        $scene = Yii::$app->request->get('scene');
        $width = Yii::$app->request->get('width','280');
        ExceptionAssert::assertNotBlank($width,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'width'));
        $path = urldecode($path);
        $data = ShareService::generate($scene,$path,$width);
        $response = Yii::$app->getResponse();
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->format = Response::FORMAT_RAW;
        $response->content = $data;
        return $response->send();
    }
}