<?php


namespace inner\modules\inner\controllers;


use inner\components\InnerControllerInner;
use inner\services\StorageSkuMappingService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class StorageSkuMappingController extends InnerControllerInner
{

    public function actionBind(){
        $companyId = Yii::$app->request->get("companyId");
        ExceptionAssert::assertNotBlank($companyId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyId'));
        $goodsId = Yii::$app->request->get("goodsId");
        ExceptionAssert::assertNotBlank($goodsId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goodsId'));
        $skuId = Yii::$app->request->get("skuId");
        ExceptionAssert::assertNotBlank($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'skuId'));
        $storageSkuId = Yii::$app->request->get("storageSkuId");
        ExceptionAssert::assertNotBlank($storageSkuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'storageSkuId'));
        $storageSkuNum = Yii::$app->request->get("storageSkuNum");
        ExceptionAssert::assertNotBlank($storageSkuNum,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'storageSkuNum'));
        StorageSkuMappingService::bindStorageSkuI($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum);
        return RestfulResponse::success(true);
    }


    public function actionBindList()
    {
        $companyIds = Yii::$app->request->get("companyIds");
        ExceptionAssert::assertNotBlank($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $companyIds = explode(",", $companyIds);
        ExceptionAssert::assertNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $activeDataProvider = StorageSkuMappingService::bindList($companyIds,$pageNo,$pageSize);
        StorageSkuMappingService::assembleStorageSkuMappingList($activeDataProvider);
        return RestfulResponse::successModelDataProvider($activeDataProvider);
    }
}