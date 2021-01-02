<?php


namespace inner\modules\inner\controllers;

use inner\components\InnerControllerInner;
use inner\services\StorageBindService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class StorageBindController  extends InnerControllerInner
{

    public function actionInfos()
    {
        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $data = StorageBindService::getModels($companyIds);
        return RestfulResponse::success($data);
    }


    public function actionCompanyList()
    {
        $storageId = Yii::$app->request->get("storageId");
        ExceptionAssert::assertNotBlank($storageId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'storageId'));
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $provider = StorageBindService::getModelsByStorageId($storageId,$pageNo,$pageSize);
        return RestfulResponse::successArrayDataProvider($provider);
    }
}