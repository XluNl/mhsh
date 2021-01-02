<?php


namespace alliance\modules\alliance\controllers;
use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\AllianceChannelService;
use alliance\services\DeliveryService;
use alliance\services\GoodsDisplayDomainService;
use alliance\services\RegionService;
use alliance\services\UserInfoService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use common\models\AllianceChannel;
use common\models\Delivery;
use common\utils\PhoneUtils;
use Yii;

class AllianceChannelController extends FController
{

    public function actionNearBy() {
        $lat = Yii::$app->request->get("lat");
        $lng = Yii::$app->request->get("lng");
        $alliance = AllianceCommon::requiredAlliance();
        $userId = $alliance['user_id'];
        if (!empty($userId)){
            UserInfoService::updateUserInfoLatLng($userId,$lat,$lng);
        }
        $keyword = Yii::$app->request->get("keyword","");
        ExceptionAssert::assertNotNull($lat, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lat'));
        ExceptionAssert::assertNotNull($lng,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'lng'));
        $cooperateModels = DeliveryService::getNearBy($lat,$lng,Delivery::TYPE_COOPERATE,$alliance['company_id'],$keyword);
        RegionService::batchSetProvinceAndCityAndCounty($cooperateModels);
        PhoneUtils::batchReplacePhoneMark($cooperateModels,'phone','phone');
        $cooperateModels =  GoodsDisplayDomainService::batchRenameImageUrl($cooperateModels,'head_img_url');
        $directModels = DeliveryService::getNearBy($lat,$lng,Delivery::TYPE_DIRECT,$alliance['company_id']);
        RegionService::batchSetProvinceAndCityAndCounty($directModels);
        PhoneUtils::batchReplacePhoneMark($directModels,'phone','phone');
        $directModels = GoodsDisplayDomainService::batchRenameImageUrl($directModels,'head_img_url');
        return RestfulResponse::success(['cooperate'=>$cooperateModels,'direct'=>$directModels]);
    }

    public function actionInfo(){
        $alliance = AllianceCommon::requiredAlliance();
        $data = AllianceChannelService::getChannel($alliance['id'],$alliance['company_id']);
        return RestfulResponse::success($data);
    }

    public function actionModify(){
        $alliance = AllianceCommon::requiredAlliance();
        $model = AllianceChannelService::getChannelByAlliance($alliance['id'],$alliance['company_id'],null,true);
        if (empty($model)){
            $model = new AllianceChannel();
            $model->loadDefaultValues();
        }
        $load = $model->load(Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::BUSINESS_APPLY_OPERATION_ERROR,"数据格式错误"));
        $model->company_id = $alliance['company_id'];
        $model->alliance_id = $alliance['id'];
        AllianceChannelService::applyChannel($model);
        return RestfulResponse::success($model->id);
    }

}