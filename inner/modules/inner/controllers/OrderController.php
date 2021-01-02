<?php

namespace inner\modules\inner\controllers;

use inner\components\InnerControllerInner;
use inner\services\DeliveryService;
use inner\services\OrderService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class OrderController extends InnerControllerInner
{

    public function actionList(){
        $orderNos = Yii::$app->request->get("orderNos");
        ExceptionAssert::assertNotBlank($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNos'));
        $orderNos = explode(",", $orderNos);
        ExceptionAssert::assertNotEmpty($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNos'));
        $data = OrderService::getList($orderNos);
        return RestfulResponse::success($data);
    }

    public function actionDetail(){
        $orderNo = Yii::$app->request->get("orderNo");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo'));
        $data = OrderService::getOrderWithGoods($orderNo);
        return RestfulResponse::success($data);
    }

    public function actionDeliveryReceive() {
        $orderNo = Yii::$app->request->get("orderNo");
        $orderGoodsIds = Yii::$app->request->get("orderGoodsIds");
        $operationId = Yii::$app->request->get("operationId");
        $operationName = Yii::$app->request->get("operationName");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo'));
        ExceptionAssert::assertNotBlank($orderGoodsIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderGoodsIds'));
        $orderGoodsIds = explode(",", $orderGoodsIds);
        ExceptionAssert::assertNotEmpty($orderGoodsIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderGoodsIds'));
        ExceptionAssert::assertNotBlank($operationId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'operationId'));
        ExceptionAssert::assertNotBlank($operationName,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'operationName'));
        OrderService::deliveryReceiveI($orderNo,$orderGoodsIds,$operationId,$operationName);
        return RestfulResponse::success(true);
    }


}