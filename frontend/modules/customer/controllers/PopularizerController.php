<?php
namespace frontend\modules\customer\controllers;

use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\PopularizerBindService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class PopularizerController extends FController {

    public function actionBind(){
        $popularizerId =  Yii::$app->request->get('share_id');
        ExceptionAssert::assertNotBlank($popularizerId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'share_id'));
        $customerId = FrontendCommon::requiredActiveCustomerId();
        $companyId = FrontendCommon::requiredFCompanyId();
        PopularizerBindService::bind($customerId,$popularizerId,$companyId);
        return RestfulResponse::success(true);
    }
}