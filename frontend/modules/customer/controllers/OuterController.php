<?php

namespace frontend\modules\customer\controllers;

use frontend\components\FController;
use frontend\services\OuterService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class OuterController extends FController {

    public function actionGoods() {
        $lat = Yii::$app->request->get("lat");
        $lng = Yii::$app->request->get("lng");
        ExceptionAssert::assertNotNull($lat,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lat'));
        ExceptionAssert::assertNotNull($lng,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lng'));
        $goods = OuterService::getNearByGoods($lat,$lng);
        return RestfulResponse::success(['goods'=>$goods]);
    }


}
