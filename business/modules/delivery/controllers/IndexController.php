<?php

namespace business\modules\delivery\controllers;

use business\components\FController;
use business\models\BusinessCommon;
use business\services\DeliveryService;
use business\services\DistributeBalanceService;
use business\services\GoodsDisplayDomainService;
use business\services\PopularizerService;
use business\services\RegionService;
use business\services\RouteService;
use business\services\UserInfoService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\utils\DateTimeUtils;
use Yii;

class IndexController extends FController {


    public function actionIndex(){
        $userId = BusinessCommon::requiredUserId();
        $userInfo = UserInfoService::requiredUserInfo($userId);
        $userInfo = GoodsDisplayDomainService::renameImageUrl($userInfo,'head_img_url');
        RegionService::setProvinceAndCityAndCounty($userInfo);
        $popularizerModels = PopularizerService::getActiveModelByUserId($userId);
        $popularizerModels = GoodsDisplayDomainService::batchRenameImageUrl($popularizerModels,'head_img_url');
        RegionService::batchSetProvinceAndCityAndCounty($popularizerModels);
        $deliveryModels = DeliveryService::getActiveModelByUserId($userId);
        $deliveryModels = GoodsDisplayDomainService::batchRenameImageUrl($deliveryModels,'head_img_url');
        RegionService::batchSetProvinceAndCityAndCounty($deliveryModels);
        $deliveryId = DeliveryService::getSelectedDeliveryId($userId);
        $routeModel = RouteService::getRouteInfoByDeliveryId($deliveryId);
        $data = [];
        $data['user_info'] = $userInfo;
        $data['popularizer'] = $popularizerModels;
        $data['delivery'] = $deliveryModels;
        $data['distribute'] = DistributeBalanceService::getDistributeInfo($deliveryModels,$popularizerModels);
        $data['route'] = $routeModel;
        $data['route']['time'] = $dateStr = date("m-d",time());
        return RestfulResponse::success($data);
    }

    public function actionRoute(){
        $date = Yii::$app->request->get("date", DateTimeUtils::formatYearAndMonthAndDay(time(),false));
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($date),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $deliveryModel = BusinessCommon::requiredDelivery();
        $data = RouteService::getStorageRoute($date,$deliveryModel['company_id'],$deliveryModel['id']);
        return RestfulResponse::success($data);
    }

}
