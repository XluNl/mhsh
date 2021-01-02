<?php

namespace alliance\modules\alliance\controllers;
use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\DeliveryManagementService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use Yii;

class DeliveryManagementController extends FController {

	public function actionList(){
        $expectArriveTime = Yii::$app->request->get("expect_arrive_time");
        $orderTimeStart = Yii::$app->request->get("order_time_start");
        $orderTimeEnd = Yii::$app->request->get("order_time_end");
        ExceptionAssert::assertNotNull($expectArriveTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'expect_arrive_time'));
        $allianceModel = AllianceCommon::requiredAlliance();
        $list = DeliveryManagementService::getDeliveryDataByExpectArriveTimeA($expectArriveTime,$orderTimeStart,$orderTimeEnd,$allianceModel['company_id'],$allianceModel['id']);
        return RestfulResponse::success($list);
    }

    public function actionModifyExpectArriveTime(){
        $expectArriveTime = Yii::$app->request->get("expect_arrive_time");
        $scheduleId = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotNull($expectArriveTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'expect_arrive_time'));
        ExceptionAssert::assertNotNull($scheduleId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $allianceModel = AllianceCommon::requiredAlliance();
        DeliveryManagementService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$allianceModel['company_id'],$allianceModel['id']);
        return RestfulResponse::success(true);
    }

    public function actionDeliveryOut(){
        $scheduleIds = Yii::$app->request->get("schedule_ids");
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        ExceptionAssert::assertNotBlank($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_ids'));
        $scheduleIds = explode(",", $scheduleIds);
        ExceptionAssert::assertNotEmpty($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'scheduleIds'));
        $allianceModel = AllianceCommon::requiredAlliance();
        DeliveryManagementService::deliveryOut($scheduleIds,$orderTimeStart,$orderTimeEnd,$allianceModel['company_id'],$allianceModel['id'],$allianceModel['id'],$allianceModel['nickname']);
        return RestfulResponse::success(true);
    }

}