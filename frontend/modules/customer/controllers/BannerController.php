<?php

namespace frontend\modules\customer\controllers;

use frontend\models\FrontendCommon;
use frontend\services\BannerService;
use frontend\services\GoodsDisplayDomainService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class BannerController extends Controller
{

    public function actionList()
    {
        $company_id = FrontendCommon::requiredFCompanyId();
        $banner_type = Yii::$app->request->get('banner_type');
        $deliveryId = FrontendCommon::requiredDeliveryId();
        ExceptionAssert::assertNotBlank($banner_type, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'banner_type'));
        $bannerList = BannerService::getValidBannerList(null,$company_id, $banner_type);
        $bannerList = BannerService::filterValidBannerList($bannerList,$deliveryId);
        return RestfulResponse::success($bannerList);
    }

    public function actionInfo()
    {
        $company_id = FrontendCommon::requiredFCompanyId();
        $banner_id = Yii::$app->request->get('banner_id');
        ExceptionAssert::assertNotBlank($banner_id, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'banner_id'));
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $banner = BannerService::getValidBanner($banner_id, $company_id);
        ExceptionAssert::assertNotNull($banner, StatusCode::createExp(StatusCode::BANNER_NOT_EXIST));
        $banner = BannerService::filterValidBanner($banner,$deliveryId);
        return RestfulResponse::success($banner);
    }

}