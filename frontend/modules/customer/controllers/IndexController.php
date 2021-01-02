<?php

namespace frontend\modules\customer\controllers;

use common\models\Banner;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\BannerService;
use frontend\services\CouponBatchService;
use frontend\services\GoodsDisplayDomainService;
use frontend\services\GoodsScheduleService;
use frontend\services\GoodsService;
use frontend\services\GoodsSortService;
use frontend\services\IndexService;
use frontend\services\LocationService;
use frontend\services\UserService;
use frontend\utils\RestfulResponse;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use common\models\Common;
use Yii;

class IndexController extends FController {

    public function actionTabs()
    {
        $tabs = [
            'today'=>['show'=>true],
            'tomorrow'=>['show'=>true],
            'alliance'=>['show'=>false]
        ];
        $userId = FrontendCommon::getUserId();
        if (StringUtils::isBlank($userId)){
            return RestfulResponse::success($tabs);
        }
        $companyId  = FrontendCommon::requiredFCompanyId();
        $deliveries = UserService::getDeliveriesByCompanyId($userId,$companyId);
        if (!empty($deliveries)){
            foreach ($deliveries as $delivery){
                if ($delivery['allow_order'] == Delivery::ALLOW_ORDER_TRUE){
                    $tabs['alliance'] =['show'=>true];
                    break;
                }
            }
        }
        return RestfulResponse::success($tabs);
    }

    public function actionToday() {
        $ownerType = Yii::$app->request->get("owner_type",GoodsConstantEnum::OWNER_SELF);
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $sorts = GoodsSortService::getSortByParentId(0,$company_id,$ownerType);
        $couponBatchList = [];

        $todayNormal = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,null,null,$deliveryId,null);
        $todayNormal = IndexService::assembleStatusAndImageAndExceptTime($todayNormal);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todayNormal,$couponBatchList);

        $todaySpike = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_SPIKE,null,null,$deliveryId,null);
        $todaySpike = IndexService::assembleStatusAndImageAndExceptTime($todaySpike);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todaySpike,$couponBatchList);

        $todayDiscount = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,null,null,$deliveryId,null);
        $todayDiscount = IndexService::assembleStatusAndImageAndExceptTime($todayDiscount);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todayDiscount,$couponBatchList);

        $sortsAlliance = GoodsSortService::getSortByParentId(0,$company_id,GoodsConstantEnum::OWNER_HA);
        $todayNormalAlliance = GoodsScheduleService::getDisplayUpToday([GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY],$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_OUTER,null,null,$deliveryId,null);
        $todayNormalAlliance = IndexService::assembleStatusAndImageAndExceptTime($todayNormalAlliance);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todayNormalAlliance,$couponBatchList);

        $todayNormal = IndexService::assembleGoodsSkuAndSort($sorts,$todayNormal);

        $todaySpike = IndexService::classifyByOnlineTime($todaySpike);
        $todayDiscount = IndexService::sortByOnlineTime($todayDiscount);

        $todayNormalAlliance = IndexService::assembleGoodsSkuAndSort($sortsAlliance,$todayNormalAlliance);

        $couponBatchList = IndexService::filterPopCouponList($couponBatchList);
        $todayDiscountImage = [
            'title'=>'一折购',
            'url'=>\Yii::$app->fileDomain->generateUrl("discount_back_image.png"),
        ];
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        return RestfulResponse::success([
            'todayNormal'=>$todayNormal,
            'todaySpike'=>$todaySpike,
            'todayDiscount'=>$todayDiscount,
            'todayNormalAlliance'=>$todayNormalAlliance,
            'todayDiscountImage'=>$todayDiscountImage,
            'couponBatch'=>$couponBatchConfig]);
    }


    /**
     * [actionNormalGoodsList 分类商品]
     * @return [type] [description]
     */
    public function actionNormalGoodsList(){
        $ownerType  = Yii::$app->request->get("owner_type",GoodsConstantEnum::OWNER_SELF);
        $pageNo = Yii::$app->request->get("page_no", 0);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $couponBatchList = [];
        $bigSortId = Yii::$app->request->get("big_sort");
        ExceptionAssert::assertNotBlank($bigSortId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'big_sort'));
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $goodsList  = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,$bigSortId,null,$deliveryId,null,$pageNo,$pageSize);
        // $goodsList = IndexService::assembleStatusAndImageAndExceptTime($goodsList);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$goodsList,$couponBatchList);
        $sortInfo  = GoodsSortService::getActiveGoodsSort($bigSortId,$company_id);
        if($sortInfo){
            $sortInfo['pic_name'] = Common::generateAbsoluteUrl($sortInfo['pic_name']);
            $sortInfo['pic_icon'] = Common::generateAbsoluteUrl($sortInfo['pic_icon']);
        }
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        $resInfo = [
            'sortInfo'=>$sortInfo,
            'goodsList' => $goodsList,
            'couponBatch'=>$couponBatchConfig
        ];
        return RestfulResponse::success($resInfo);
    }

    /**
     * [actionSpikeGoodsList 限时抢购]
     * @return [type] [description]
     */
    public function actionSpikeGoodsList(){
        $ownerType  = Yii::$app->request->get("owner_type",GoodsConstantEnum::OWNER_SELF);
        $couponBatchList = [];
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $todaySpike = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_SPIKE,null,null,$deliveryId,null);
        $todaySpike = IndexService::assembleStatusAndImageAndExceptTime($todaySpike);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todaySpike,$couponBatchList);
        $todaySpike = IndexService::classifyByOnlineTime($todaySpike);
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        $resInfo = [
            'todaySpike'=>$todaySpike,
            'couponBatch'=>$couponBatchConfig
        ];
        return RestfulResponse::success($resInfo);
    }

    /**
     * [actionDiscountGoodsList 天天特价]
     * @return [type] [description]
     */
    public function actionDiscountGoodsList(){
        $ownerType  = Yii::$app->request->get("owner_type",GoodsConstantEnum::OWNER_SELF);
        $pageNo = Yii::$app->request->get("page_no", 0);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $couponBatchList = [];
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $todayDiscount = GoodsScheduleService::getDisplayUpToday($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,null,null,$deliveryId,null,$pageNo,$pageSize);
        $todayDiscount = IndexService::assembleStatusAndImageAndExceptTime($todayDiscount);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todayDiscount,$couponBatchList);
        $todayDiscount = IndexService::sortByOnlineTime($todayDiscount);
        $todayDiscountImage = [
            'title'=>'一折购',
            'url'=>\Yii::$app->fileDomain->generateUrl("discount_back_image.png"),
        ];
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        $resInfo = [
            'todayDiscount'=>$todayDiscount,
            'todayDiscountImage'=>$todayDiscountImage,
            'couponBatch'=>$couponBatchConfig
        ];
        return RestfulResponse::success($resInfo);
    }

    public function actionTomorrow() {
        $ownerType = Yii::$app->request->get("owner_type",GoodsConstantEnum::OWNER_SELF);
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $sorts = GoodsSortService::getSortByParentId(0,$company_id,GoodsConstantEnum::OWNER_SELF);
        $couponBatchList = [];
        $tomorrowNormal = GoodsScheduleService::getDisplayUpTomorrow($ownerType,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,null,null,$deliveryId,null);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$tomorrowNormal,$couponBatchList);
        $tomorrowNormal = IndexService::assembleStatusAndImageAndExceptTime($tomorrowNormal);
        $tomorrowNormal = IndexService::assembleGoodsSkuAndSort($sorts,$tomorrowNormal);
        $couponBatchList = IndexService::filterPopCouponList($couponBatchList);
        return RestfulResponse::success(['tomorrowNormal'=>$tomorrowNormal,'couponBatch'=>$couponBatchList]);
    }

    public function actionAlliance() {
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryModel = FrontendCommon::requiredDelivery();
        $deliveryId = $deliveryModel['id'];
        $couponBatchList = [];


        $sortsAlliance = GoodsSortService::getSortByParentId(0,$company_id,GoodsConstantEnum::OWNER_HA);
        $todayNormalAlliance = GoodsScheduleService::getDisplayUpToday(GoodsConstantEnum::OWNER_HA,$company_id,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_OUTER,null,null,$deliveryId,null);
        //补全联盟点信息
        GoodsService::completeAlliance($todayNormalAlliance);
        //补全距离信息
        LocationService::toDeliveryDistance($deliveryModel,$todayNormalAlliance);

        $todayNormalAlliance = IndexService::assembleStatusAndImageAndExceptTime($todayNormalAlliance);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($company_id,$todayNormalAlliance,$couponBatchList);

        $todayNormalAlliance = IndexService::assembleGoodsSkuAndSort($sortsAlliance,$todayNormalAlliance);

        $banner = BannerService::getActiveModelList($company_id,Banner::TYPE_ALLIANCE);

        $banner = GoodsDisplayDomainService::batchRenameImageUrl($banner,'images');


        $couponBatchList = IndexService::filterPopCouponList($couponBatchList);
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        return RestfulResponse::success([
            'todayNormalAlliance'=>$todayNormalAlliance,
            'couponBatch'=>$couponBatchConfig,
            'banner'=>$banner
        ]);
    }

    public function actionRecommend(){
        $ownerType  = Yii::$app->request->get("ownerType",GoodsConstantEnum::OWNER_SELF);
        $pageNo = Yii::$app->request->get("pageNo", 0);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $couponBatchList = [];
        $companyId = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $goodsList  = GoodsScheduleService::getRecommendDisplayUpToday($ownerType,$companyId,[GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_SPIKE],null,null,$deliveryId,null,$pageNo,$pageSize);
        $goodsList = IndexService::assembleStatusAndImageAndExceptTime($goodsList);
        CouponBatchService::assembleAvailableCouponListMultipleGoods($companyId,$goodsList,$couponBatchList);
        $couponBatchConfig = [
            'list'=>$couponBatchList,
            'interval'=>30*60,
        ];
        $couponBatchConfig = empty($couponBatchList)?null:$couponBatchConfig;
        $resInfo = [
            'goodsList' => $goodsList,
            'couponBatch'=>$couponBatchConfig
        ];
        return RestfulResponse::success($resInfo);
    }


    public function actionStarShow()
    {
        $show = Yii::$app->params['mini.program.star.show'];
        $show = StringUtils::isNotBlank($show)?$show:false;
        return RestfulResponse::success($show);
    }
}
