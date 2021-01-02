<?php

namespace outer\modules\outer\controllers;

use outer\components\FController;
use outer\services\ApiService;
use outer\utils\ExceptionAssert;
use outer\utils\RestfulResponse;
use outer\utils\StatusCode;
use Yii;

/**
 *
 * @property-read array[] $domainMap
 */
class ApiController extends FController {

    public function actionDomain(){
        $appId = Yii::$app->request->get('appId');
        ExceptionAssert::assertNotBlank($appId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'appId'));
        $env = Yii::$app->request->get('env','develop');
        $type = Yii::$app->request->get('type');
        ExceptionAssert::assertNotBlank($type,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'type'));
        $data = ApiService::getDomainData($appId,$env,$type);
        return RestfulResponse::success($data);
    }



}