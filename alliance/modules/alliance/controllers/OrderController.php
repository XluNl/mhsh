<?php

namespace alliance\modules\alliance\controllers;
use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\AllianceService;
use alliance\services\OrderService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use Yii;

class OrderController extends FController {

	public $enableCsrfValidation = false;

	public function actionList() {
		$pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $filter = Yii::$app->request->get("filter",'all');
        $userId = AllianceCommon::requiredUserId();
		$allianceId = AllianceService::getSelectedId($userId);
        $orders = OrderService::getPageFilterOrder($allianceId,$filter,$pageNo,$pageSize);
        return RestfulResponse::success($orders);
	}

    public function actionUploadWeight() {
        $postData = Yii::$app->request->post();
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'order_no',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'items',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'items缺失'));
        foreach ($postData['items'] as $value){
            ExceptionAssert::assertKeyExistAndNotBlack($value,'id',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'items的id缺失'));
            ExceptionAssert::assertKeyExistAndNotBlack($value,'num',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'items的num缺失'));
            ExceptionAssert::assertTrue($value['num']>0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'提货数量必须大于0'));

        }
        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::uploadWeight($postData['order_no'],$postData['items'],$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionUnUploadWeight() {
        $postData = Yii::$app->request->post();
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'order_no',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'ids',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids缺失'));
        ExceptionAssert::assertNotEmpty($postData['ids'],StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids缺失'));

        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::unUploadWeight($postData['order_no'],$postData['ids'],$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionDeliveryOut() {
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::deliveryOut($orderNo,$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionReceive() {
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::receive($orderNo,$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionGetByDeliveryCode(){
        $deliveryCode = Yii::$app->request->get("delivery_code");
        ExceptionAssert::assertNotBlank($deliveryCode,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_code缺失'));
        $userId = AllianceCommon::requiredUserId();
        $order = OrderService::getOrderWithGoods($deliveryCode,$userId);
        return RestfulResponse::success($order);
    }

    public function actionNoStock(){
        $orderNo = Yii::$app->request->get("order_no");
        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::noStock($orderNo,$userId,$userName);
        return RestfulResponse::success("订单取消成功");
    }

    public function actionUploadReceive() {
        $orderNos = Yii::$app->request->get("order_nos");
        ExceptionAssert::assertNotBlank($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_nos'));
        $orderNos = explode(",", $orderNos);
        ExceptionAssert::assertNotEmpty($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'order_nos'));
        $userId = AllianceCommon::requiredUserId();
        $userName =AllianceCommon::requiredUserName();
        OrderService::batchUploadWeightAndReceiveOrder($orderNos,$userId,$userName);
        return RestfulResponse::success(true);
    }

}
