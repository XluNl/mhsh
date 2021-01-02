<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\StorageBindService;
use backend\services\StorageSkuMappingService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use common\utils\ArrayUtils;
use Yii;

/**
 * StorageSkuMappingController implements the CRUD actions for StorageSkuMapping model.
 */
class StorageSkuMappingController extends BaseController
{

    public function actionBind()
    {
        $companyId = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goodsId");
        BExceptionAssert::assertNotBlank($goodsId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"goodsId"));
        $skuId = Yii::$app->request->get("skuId");
        BExceptionAssert::assertNotBlank($skuId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"skuId"));
        $storageSkuId = Yii::$app->request->get("storageSkuId");
        BExceptionAssert::assertNotBlank($storageSkuId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"storageSkuId"));
        $storageSkuNum = Yii::$app->request->get("storageSkuNum");
        BExceptionAssert::assertNotBlank($storageSkuNum,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"storageSkuNum"));
        $expectSoldNum = Yii::$app->request->get("expectSoldNum");
        BExceptionAssert::assertNotBlank($expectSoldNum,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"expectSoldNum"));
        StorageSkuMappingService::bindStorageSkuB($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum,$expectSoldNum);
        return BRestfulResponse::success(true);
    }


    public function actionSortSearch()
    {
        $companyId = BackendCommon::getFCompanyId();
        $storageBind = StorageBindService::getModel($companyId);
        $storageSortName = Yii::$app->request->get("storageSortName");
        if (empty($storageBind)){
            return  BRestfulResponse::success(['-1'=>'所有分类']);
        }
        $res = StorageSkuMappingService::getStorageSortSelect($storageBind['storage_id']);
        $res = (['-1'=>'所有分类']+$res);
        $options = ArrayUtils::mapToArray($res,'id','text');
        return BRestfulResponse::success($options);
    }


    public function actionSkuSearch()
    {
        $storageSkuId = Yii::$app->request->get("storageSkuId");
        $storageSkuName = Yii::$app->request->get("storageSkuName");
        $storageSortId = Yii::$app->request->get("storageSortId",-1);
        $storageSortId = $storageSortId>0?$storageSortId:null;
        $companyId = BackendCommon::getFCompanyId();
        $storageBind = StorageBindService::getModel($companyId);
        BExceptionAssert::assertNotNull($storageBind,BStatusCode::createExp(BStatusCode::STORAGE_UN_BIND));
        $res = StorageSkuMappingService::getStorageSkuSelect($storageBind['storage_id'],$storageSortId,$storageSkuName);
        $options = ArrayUtils::mapToArray($res,'id','text');
        return BRestfulResponse::success($options);
    }
}
