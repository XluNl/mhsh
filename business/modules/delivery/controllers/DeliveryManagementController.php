<?php

namespace business\modules\delivery\controllers;
use business\components\FController;
use business\models\BusinessCommon;
use business\services\DeliveryManagementService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;

class DeliveryManagementController extends FController {

	public function actionList(){
        $expectArriveTime = Yii::$app->request->get("expect_arrive_time");
        $orderTimeStart = Yii::$app->request->get("order_time_start");
        $orderTimeEnd = Yii::$app->request->get("order_time_end");
        ExceptionAssert::assertNotNull($expectArriveTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'expect_arrive_time'));
        $deliveryModel = BusinessCommon::requiredDelivery();
        $list = DeliveryManagementService::getDeliveryDataByExpectArriveTimeB($expectArriveTime,$orderTimeStart,$orderTimeEnd,$deliveryModel['company_id'],$deliveryModel['id']);
        return RestfulResponse::success($list);
    }

    public function actionModifyExpectArriveTime(){
        $expectArriveTime = Yii::$app->request->get("expect_arrive_time");
        $scheduleId = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotNull($expectArriveTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'expect_arrive_time'));
        ExceptionAssert::assertNotNull($scheduleId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $deliveryModel = BusinessCommon::requiredDelivery();
        DeliveryManagementService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$deliveryModel['company_id'],$deliveryModel['id']);
        return RestfulResponse::success(true);
    }

    public function actionDeliveryOut(){
        $scheduleIds = Yii::$app->request->get("schedule_ids");
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        ExceptionAssert::assertNotBlank($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_ids'));
        $scheduleIds = explode(",", $scheduleIds);
        ExceptionAssert::assertNotEmpty($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'scheduleIds'));
        $deliveryModel = BusinessCommon::requiredDelivery();
        DeliveryManagementService::deliveryOut($scheduleIds,$orderTimeStart,$orderTimeEnd,$deliveryModel['company_id'],$deliveryModel['id'],$deliveryModel['id'],$deliveryModel['nickname']);
        return RestfulResponse::success(true);
    }


    public function actionReceiveList(){
        $expectArriveTime = Yii::$app->request->get("expect_arrive_time");
        $orderTimeStart = Yii::$app->request->get("order_time_start");
        $orderTimeEnd = Yii::$app->request->get("order_time_end");
        $ownerType = Yii::$app->request->get("owner_type");
        ExceptionAssert::assertNotNull($expectArriveTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'expect_arrive_time'));
        $deliveryModel = BusinessCommon::requiredDelivery();
        $list = DeliveryManagementService::getDeliveryReceiveDataByExpectArriveTime($expectArriveTime,$orderTimeStart,$orderTimeEnd,$deliveryModel['company_id'],$deliveryModel['id'],$ownerType);
        return RestfulResponse::success($list);
    }
}