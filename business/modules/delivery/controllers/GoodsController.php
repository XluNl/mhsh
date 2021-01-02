<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\models\BusinessCommon;
use business\services\GoodsSkuService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use Yii;

class GoodsController extends FController
{


    public function actionList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $status = Yii::$app->request->get("status", null);
        $bigSortId = Yii::$app->request->get("big_sort", null);
        $smallSortId = Yii::$app->request->get("small_sort", null);
        $goodsName = Yii::$app->request->get("goods_name", null);
        $deliveryId = BusinessCommon::getDeliveryId();
        $skuList = GoodsSkuService::getPageFilter($deliveryId,$bigSortId,$smallSortId,$status,$goodsName,$pageNo,$pageSize);
        return RestfulResponse::success($skuList);
    }

    public function actionHaList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $bigSortId = Yii::$app->request->get("big_sort", null);
        $smallSortId = Yii::$app->request->get("small_sort", null);
        $goodsName = Yii::$app->request->get("goods_name", null);
        $deliveryModel = BusinessCommon::requiredDelivery();
        $skuList = GoodsSkuService::getHAPageFilter($deliveryModel['company_id'],$deliveryModel['id'],$bigSortId,$smallSortId,$goodsName,$pageNo,$pageSize);
        return RestfulResponse::success($skuList);
    }
    public function actionInfo(){
        $delivery = BusinessCommon::requiredDelivery();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        $model = GoodsSkuService::getSkuInfo($skuId,$delivery['id'],$delivery['company_id'],GoodsConstantEnum::OWNER_DELIVERY);
        return RestfulResponse::success($model);
    }

    public function actionHaInfo(){
        $delivery = BusinessCommon::requiredDelivery();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        $model = GoodsSkuService::getSkuInfo($skuId,null,$delivery['company_id'],GoodsConstantEnum::OWNER_HA);
        return RestfulResponse::success($model);
    }
    public function actionOnline(){
        $delivery = BusinessCommon::requiredDelivery();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        GoodsSkuService::changeGoodsStatus($skuId,$delivery['id'],$delivery['company_id'],GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_UP);
        return RestfulResponse::success(true);
    }

    public function actionOffline(){
        $delivery = BusinessCommon::requiredDelivery();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        GoodsSkuService::changeGoodsStatus($skuId,$delivery['id'],$delivery['company_id'],GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN);
        return RestfulResponse::success(true);
    }

}