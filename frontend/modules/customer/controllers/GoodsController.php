<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\DeliveryService;
use frontend\services\GoodsScheduleService;
use frontend\services\GoodsService;
use frontend\services\IndexService;
use frontend\services\LocationService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class GoodsController extends FController {

    public function actionSearch() {
        $company_id = FrontendCommon::requiredFCompanyId();
        $delivery_id = FrontendCommon::requiredDeliveryId();
        $ownerType = Yii::$app->request->get("owner_type",null);
        $goods_name = Yii::$app->request->get("goods_name",null);
        $big_sort = Yii::$app->request->get("big_sort",null);
        $small_sort = Yii::$app->request->get("small_sort",null);
        $pageNo = Yii::$app->request->get("page_no", 0);
        $pageSize = Yii::$app->request->get("page_size", 20);
        ExceptionAssert::assertNotBlank($pageNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'page_no'));
        ExceptionAssert::assertNotBlank($pageSize,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'page_size'));
        ExceptionAssert::assertNotBlank($ownerType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'owner_type'));
        ExceptionAssert::assertNotBlank($goods_name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods_name'));
        ExceptionAssert::assertNotBlank($delivery_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_id'));
        ExceptionAssert::assertTrue(in_array($ownerType,array_keys(GoodsConstantEnum::$ownerArr)),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'owner_type'));
        $display_channel = ArrayUtils::getArrayValue($ownerType,GoodsConstantEnum::$scheduleSearchDisplayChannelMap);
        $goodsSku = GoodsScheduleService::getDisplayUp($ownerType,$company_id,$display_channel,$big_sort,$small_sort,$delivery_id,$goods_name,$pageNo,$pageSize);
        $goodsSku = IndexService::assembleStatusAndImageAndExceptTime($goodsSku);
        $goodsSku = GoodsScheduleService::assembleSkuInfoList($goodsSku);
        return  RestfulResponse::success($goodsSku);
    }

    public function actionGoodsDetail() {
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::getDeliveryId();
        $deliveryModel = StringUtils::isBlank($deliveryId)?null:DeliveryService::getActiveModel($deliveryId,$company_id);

        $goodsId = Yii::$app->request->get("goods_id");
        $displayChannel = Yii::$app->request->get("display_channel");
        ExceptionAssert::assertNotBlank($displayChannel,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'display_channel'));
        ExceptionAssert::assertNotBlank($goodsId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods_id'));
        $goodsSku = GoodsScheduleService::getDisplayDetail(null,$company_id,[$goodsId],$displayChannel);
        ExceptionAssert::assertNotEmpty($goodsSku,StatusCode::createExp(StatusCode::GOODS_NOT_EXIST));
        $goodsSku = IndexService::assembleStatusAndImageAndExceptTime($goodsSku);
        $userId = FrontendCommon::getUserId();
        $goodsSku = GoodsService::assembleCartNum($userId,$goodsSku);

        //补全联盟点信息
        GoodsService::completeAlliance($goodsSku);
        //补全距离信息
        LocationService::toDeliveryDistance($deliveryModel,$goodsSku);

        $alliance = GoodsService::checkAllianceStatus($company_id,$goodsSku);

        $goodsList = GoodsScheduleService::assembleSkuInfoList($goodsSku);
        $goodsList = GoodsService::completeDetail($goodsList,$company_id);
        $goodsList = GoodsService::assembleCouponBatchInfo($company_id,$goodsList);
        return RestfulResponse::success($goodsList);
    }


}