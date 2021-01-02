<?php

namespace inner\modules\inner\controllers;

use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use inner\components\InnerControllerInner;
use inner\services\DeliveryManagementService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class DeliveryManagementController extends InnerControllerInner
{

    public function actionList(){
        $expectArriveTime = Yii::$app->request->get("expectArriveTime");
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'expectArriveTime'));
        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));

        $scheduleIds = Yii::$app->request->get("scheduleIds");
        if (StringUtils::isNotBlank($scheduleIds)){
            $scheduleIds = explode(",", $scheduleIds);
        }
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);

        list($orderTimeStart, $orderTimeEnd) = $this->getOrderTimeLimitParams();


        $provider = DeliveryManagementService::getDeliveryDataByExpectArriveTimeI($expectArriveTime,$orderTimeStart,$orderTimeEnd,$scheduleIds,$companyIds,$pageNo,$pageSize);
        return RestfulResponse::successArrayDataProvider($provider);
    }


    public function actionDeliveryList(){
        $expectArriveTime = Yii::$app->request->get("expectArriveTime");
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'expectArriveTime'));
        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $scheduleId = Yii::$app->request->get("scheduleId");
        ExceptionAssert::assertNotEmpty($scheduleId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'scheduleId'));
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);

        list($orderTimeStart, $orderTimeEnd) = $this->getOrderTimeLimitParams();

        $provider = DeliveryManagementService::getScheduleDataByExpectArriveTimeI($expectArriveTime,$orderTimeStart, $orderTimeEnd,$scheduleId,$companyIds,$pageNo,$pageSize);
        return RestfulResponse::successArrayDataProvider($provider);
    }


    public function actionDeliveryOut(){
        $tradeNo = Yii::$app->request->get("tradeNo");
        ExceptionAssert::assertNotBlank($tradeNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'tradeNo'));
        $expectArriveTime = Yii::$app->request->get("expectArriveTime");
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'expectArriveTime'));

        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $scheduleIds = Yii::$app->request->get("scheduleIds");
        $scheduleIds = ExceptionAssert::assertNotBlankAndNotEmpty($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'scheduleIds'));
        $deliveryIds = Yii::$app->request->get("deliveryIds");
        if (StringUtils::isNotBlank($deliveryIds)){
            $deliveryIds = ExceptionAssert::assertNotBlankAndNotEmpty($deliveryIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'deliveryIds'));
        }

        list($orderTimeStart, $orderTimeEnd) = $this->getOrderTimeLimitParams();


        $operatorId = Yii::$app->request->get("operatorId");
        $operatorName = Yii::$app->request->get("operatorName");
        ExceptionAssert::assertNotBlank($operatorId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'operatorId'));
        ExceptionAssert::assertNotBlank($operatorName,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'operatorName'));


        $data = DeliveryManagementService::deliveryOut($tradeNo,$expectArriveTime,$orderTimeStart, $orderTimeEnd,$companyIds,$scheduleIds,$deliveryIds,$operatorId,$operatorName);
        return RestfulResponse::success($data);
    }


    public function actionModifyExpectArriveTime(){
        $expectArriveTime = Yii::$app->request->get("expectArriveTime");
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'expectArriveTime'));
        $newExpectArriveTime = Yii::$app->request->get("newExpectArriveTime");
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmd($newExpectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'newExpectArriveTime'));

        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $scheduleIds = Yii::$app->request->get("scheduleIds");
        $scheduleIds = ExceptionAssert::assertNotBlankAndNotEmpty($scheduleIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'scheduleIds'));
        $deliveryIds = Yii::$app->request->get("deliveryIds");
        if (StringUtils::isNotBlank($deliveryIds)){
            $deliveryIds = ExceptionAssert::assertNotBlankAndNotEmpty($deliveryIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'deliveryIds'));
        }
        list($orderTimeStart, $orderTimeEnd) = $this->getOrderTimeLimitParams();

        $updateCount = DeliveryManagementService::modifyExpectArriveTimeI($expectArriveTime,$newExpectArriveTime,$orderTimeStart, $orderTimeEnd,$companyIds,$scheduleIds,$deliveryIds);
        return RestfulResponse::success($updateCount);
    }

    /**
     * 获取订单起始截止限制
     * @return array
     */
    private function getOrderTimeLimitParams()
    {
        $orderTimeStart = Yii::$app->request->get("orderTimeStart");
        if (StringUtils::isNotBlank($orderTimeStart)) {
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeStart), StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR, 'orderTimeStart'));
            $orderTimeStart = DateTimeUtils::parseStandardWStrDate($orderTimeStart);
        }
        $orderTimeEnd = Yii::$app->request->get("orderTimeEnd");
        if (StringUtils::isNotBlank($orderTimeEnd)) {
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeEnd), StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR, 'orderTimeEnd'));
            $orderTimeEnd = DateTimeUtils::parseStandardWStrDate($orderTimeEnd);
        }
        return array($orderTimeStart, $orderTimeEnd);
    }

}