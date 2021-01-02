<?php


namespace alliance\modules\alliance\controllers;


use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\AllianceService;
use alliance\services\GoodsSkuService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
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
        $userId = AllianceCommon::requiredUserId();
        $allianceId = AllianceService::getSelectedId($userId);
        $skuList = GoodsSkuService::getPageFilter($allianceId,$status,$bigSortId,$smallSortId,$goodsName,$pageNo,$pageSize);
        return RestfulResponse::success($skuList);
    }

    public function actionInfo(){
        $alliance = AllianceCommon::requiredAlliance();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        $model = GoodsSkuService::getSkuInfo($skuId,$alliance['id'],$alliance['company_id']);
        return RestfulResponse::success($model);
    }

    public function actionOnline(){
        $alliance = AllianceCommon::requiredAlliance();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        GoodsSkuService::changeGoodsStatus($skuId,$alliance['id'],$alliance['company_id'],GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_UP);
        return RestfulResponse::success(true);
    }

    public function actionOffline(){
        $alliance = AllianceCommon::requiredAlliance();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotNull($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id'));
        GoodsSkuService::changeGoodsStatus($skuId,$alliance['id'],$alliance['company_id'],GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN);
        return RestfulResponse::success(true);
    }

}