<?php

namespace frontend\modules\customer\controllers;
use common\models\Delivery;
use common\models\SystemOptions;
use common\services\SystemOptionsService;
use common\utils\NumberUtils;
use common\utils\PhoneUtils;
use frontend\models\FrontendCommon;
use frontend\services\CouponBatchService;
use frontend\services\DeliveryService;
use frontend\services\GoodsDisplayDomainService;
use frontend\services\RegionService;
use frontend\services\UserInfoService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class DeliveryController extends Controller {

	public function actionChange() {
        $delivery_id = Yii::$app->request->get("delivery_id");
        ExceptionAssert::assertNotNull($delivery_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_id'));
        $userModel= FrontendCommon::requiredUserModel();
        DeliveryService::changeSelectedDelivery($userModel,$delivery_id);
        
        // 发放新人优惠券
        CouponBatchService::automaticDrawCoupon();
		return RestfulResponse::success($delivery_id);
	}


    public function actionNearBy() {
        $lat = Yii::$app->request->get("lat");
        $lng = Yii::$app->request->get("lng");
        $ver = Yii::$app->request->get("version");
        if (!NumberUtils::isNumeric($lat)||$lat<3||$lat>54||!NumberUtils::isNumeric($lng)||$lng<72||$lng>136||$ver == SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_TEST_VERSION)){
            $lat = 30.3015290000;
            $lng = 120.1060900000;
        }
        else {
            $userId = FrontendCommon::getUserId();
            if (!empty($userId)){
                UserInfoService::updateUserInfoLatLng($userId,$lat,$lng);
            }
        }
        $keyword = Yii::$app->request->get("keyword","");
        ExceptionAssert::assertNotNull($lat,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lat'));
        ExceptionAssert::assertNotNull($lng,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lng'));
        $currentModel = DeliveryService::getCurrent($lat,$lng);
        RegionService::setProvinceAndCityAndCounty($currentModel);
        PhoneUtils::replacePhoneMark($currentModel,'phone','phone');
        $currentModel = GoodsDisplayDomainService::renameImageUrl($currentModel,'head_img_url');
        $cooperateModels = DeliveryService::getNearBy($lat,$lng,Delivery::TYPE_COOPERATE,null,$keyword);
        RegionService::batchSetProvinceAndCityAndCounty($cooperateModels);
        PhoneUtils::batchReplacePhoneMark($cooperateModels,'phone','phone');
        $cooperateModels =  GoodsDisplayDomainService::batchRenameImageUrl($cooperateModels,'head_img_url');
        $directModels = DeliveryService::getNearBy($lat,$lng,Delivery::TYPE_DIRECT);
        RegionService::batchSetProvinceAndCityAndCounty($directModels);
        PhoneUtils::batchReplacePhoneMark($directModels,'phone','phone');
        $directModels = GoodsDisplayDomainService::batchRenameImageUrl($directModels,'head_img_url');
        return RestfulResponse::success(['current'=>$currentModel,'cooperate'=>$cooperateModels,'direct'=>$directModels]);
    }

    public function actionInfo() {
        $deliveryModel = FrontendCommon::requiredDelivery();
        $deliveryArray = $deliveryModel->attributes;
        RegionService::setProvinceAndCityAndCounty($deliveryArray);
        $deliveryArray = GoodsDisplayDomainService::renameImageUrl($deliveryArray,'head_img_url');
        RegionService::setProvinceAndCityAndCounty($deliveryArray);
        PhoneUtils::replacePhoneMark($deliveryArray,'phone',null);
        return RestfulResponse::success($deliveryArray);
    }

    public function actionDetail() {
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotBlank($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $deliveryArray = DeliveryService::getActiveModel($id,null,false);
        $deliveryArray = GoodsDisplayDomainService::renameImageUrl($deliveryArray,'head_img_url');
        RegionService::setProvinceAndCityAndCounty($deliveryArray);
        PhoneUtils::replacePhoneMark($deliveryArray,'phone',null);
        return RestfulResponse::success($deliveryArray);
    }

}