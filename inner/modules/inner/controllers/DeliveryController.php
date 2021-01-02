<?php

namespace inner\modules\inner\controllers;

use common\utils\PageModule;
use common\utils\StringUtils;
use inner\components\InnerControllerInner;
use inner\services\DeliveryService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class DeliveryController extends InnerControllerInner
{

    public function actionInfos() {
        $ids = Yii::$app->request->get("ids");
        ExceptionAssert::assertNotBlank($ids,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids'));
        $ids = explode(",", $ids);
        ExceptionAssert::assertNotEmpty($ids,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids'));
        $models = DeliveryService::getAllModel($ids);
        $models = DeliveryService::batchSetRenamePicture($models);
        return RestfulResponse::success($models);
    }

    public function actionList() {
        $companyIds = Yii::$app->request->get("companyIds");
        if (StringUtils::isNotBlank($companyIds)){
            $companyIds = explode(",", $companyIds);
        }
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $provider = DeliveryService::getList($companyIds,$pageNo,$pageSize);
        $pageModule = PageModule::createModel($provider);
        $pageModule->items = DeliveryService::batchSetRenamePicture($pageModule->items);
        return RestfulResponse::success($pageModule);
    }

}