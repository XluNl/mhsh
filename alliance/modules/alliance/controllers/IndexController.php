<?php

namespace alliance\modules\alliance\controllers;

use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\AllianceService;
use alliance\services\DistributeBalanceService;
use alliance\services\RegionService;
use alliance\services\UserInfoService;
use alliance\utils\RestfulResponse;
use common\utils\ArrayUtils;

class IndexController extends FController {


    public function actionIndex(){
        $userId = AllianceCommon::requiredUserId();
        $userInfo = UserInfoService::requiredUserInfo($userId);
        RegionService::setProvinceAndCityAndCounty($userInfo);
        $allianceModels = AllianceService::getActiveModelByUserId($userId);
        AllianceService::batchGetDisplayVO($allianceModels);
        RegionService::batchSetProvinceAndCityAndCounty($allianceModels);

        $defaultAllianceId = AllianceService::getSelectedId($userId);
        $data = [];
        $data['user_info'] = $userInfo;
        $data['alliance'] = $allianceModels;
        $data['distribute'] = DistributeBalanceService::getDistributeInfo($allianceModels);

        $allianceModelArray = ArrayUtils::index($allianceModels,'id');
        $defaultAlliance = null;
        if (key_exists($defaultAllianceId,$allianceModelArray)){
            $defaultAlliance = $allianceModelArray[$defaultAllianceId];
        }
        $data['defaultAlliance'] = $defaultAlliance;

        return RestfulResponse::success($data);
    }



}
