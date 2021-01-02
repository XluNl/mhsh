<?php

namespace frontend\modules\customer\controllers;

use common\models\GoodsConstantEnum;
use frontend\services\GroupRoomService;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\GroupActiveService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class GroupActiveController extends FController
{

    public function actionList()
    {
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $ownerType = Yii::$app->request->get("ownerType", GoodsConstantEnum::OWNER_SELF);
        $keyword = Yii::$app->request->get("keyword");
        $companyId = FrontendCommon::requiredFCompanyId();
        ExceptionAssert::assertNotBlank($ownerType, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'ownerType'));
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $groupActiveGoodsList = GroupActiveService::getGroupActiveList($ownerType, $companyId, $deliveryId,$keyword, $pageNo, $pageSize);
        return RestfulResponse::success($groupActiveGoodsList);
    }



    public function actionDetail()
    {
        $activeNo = Yii::$app->request->get("activeNo");
        $companyId = FrontendCommon::requiredFCompanyId();
        ExceptionAssert::assertNotBlank($activeNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'activeNo'));
        $ownerType = Yii::$app->request->get("ownerType");
        $groupActiveDetail = GroupActiveService::getGroupActiveDetail($activeNo,$ownerType,$companyId);
        return RestfulResponse::success($groupActiveDetail);
    }

    public function actionRoomsCount()
    {
        $activeNo = Yii::$app->request->get("activeNo");
        ExceptionAssert::assertNotBlank($activeNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'activeNo'));
        $data = GroupActiveService::getGroupActiveStatistic($activeNo);
        return RestfulResponse::success($data);
    }

    public function actionRooms()
    {
        $pageNo = Yii::$app->request->get("pageNo", 0);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $activeNo = Yii::$app->request->get("activeNo");
        $companyId = FrontendCommon::requiredFCompanyId();
        ExceptionAssert::assertNotBlank($activeNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'activeNo'));
        $roomListAndOrders = GroupRoomService::getRoomListAndOrders($activeNo,$companyId, $pageNo, $pageSize);
        return RestfulResponse::success($roomListAndOrders);
    }

    public function actionRoomDetail()
    {
        $roomNo = Yii::$app->request->get("roomNo");
        $companyId = FrontendCommon::requiredFCompanyId();
        ExceptionAssert::assertNotBlank($roomNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS, 'roomNo'));
        $roomDetail = GroupRoomService::getRoomDetail($roomNo,$companyId);
        return RestfulResponse::success($roomDetail);
    }
}