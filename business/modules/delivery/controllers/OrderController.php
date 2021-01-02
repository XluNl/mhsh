<?php

namespace business\modules\delivery\controllers;
use business\components\FController;
use business\models\BusinessCommon;
use business\services\DeliveryService;
use business\services\OrderService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use Yii;

class OrderController extends FController {

	public $enableCsrfValidation = false;

	public function actionList() {
		$pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $filter = Yii::$app->request->get("filter",'all');
        $keyword = Yii::$app->request->get("search_keyword",null);
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        $userId = BusinessCommon::requiredUserId();
		$deliveryId = DeliveryService::getSelectedDeliveryId($userId);
        $orders = OrderService::getPageFilterOrder($ownerType,$deliveryId,$filter,$keyword,$pageNo,$pageSize);
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
        $userId = BusinessCommon::requiredUserId();
        $userName =BusinessCommon::requiredUserName();
        OrderService::uploadWeight($postData['order_no'],$postData['items'],$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionUnUploadWeight() {
        $postData = Yii::$app->request->post();
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'order_no',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'ids',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids缺失'));
        ExceptionAssert::assertNotEmpty($postData['ids'],StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids缺失'));

        $userId = BusinessCommon::requiredUserId();
        $userName =BusinessCommon::requiredUserName();
        OrderService::unUploadWeight($postData['order_no'],$postData['ids'],$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionReceive() {
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotBlank($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no缺失'));
        $userId = BusinessCommon::requiredUserId();
        $userName =BusinessCommon::requiredUserName();
        OrderService::receive($orderNo,$userId,$userName);
        return RestfulResponse::success(true);
    }

    public function actionUploadReceive() {
        $orderNos = Yii::$app->request->get("order_nos");
        ExceptionAssert::assertNotBlank($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_nos'));
        $orderNos = explode(",", $orderNos);
        ExceptionAssert::assertNotEmpty($orderNos,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'order_nos'));
        $userId = BusinessCommon::requiredUserId();
        $userName =BusinessCommon::requiredUserName();
        OrderService::batchUploadWeightAndReceiveOrder($orderNos,$userId,$userName);
        return RestfulResponse::success(true);
    }


    public function actionGetByDeliveryCode(){
        $deliveryCode = Yii::$app->request->get("delivery_code");
        ExceptionAssert::assertNotBlank($deliveryCode,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_code缺失'));
        $userId = BusinessCommon::requiredUserId();
        $order = OrderService::getOrderWithGoods($deliveryCode,$userId);
        return RestfulResponse::success($order);
    }

    public function actionGetDeliveryIntegralList(){
        $offset = Yii::$app->request->get("offset", 0);
        $length = Yii::$app->request->get("length", 10);
        $data = OrderService::getPartnerMonthOrderSum();
        $data = array_slice($data, (int)$offset*(int)$length, (int)$length);
        return RestfulResponse::success($data);
    }

    public function actionGetDeliveryRanking(){
        $deliveryId = BusinessCommon::getDeliveryId();
        $partnerMonthOrderSum = OrderService::getPartnerMonthOrderSum();
        $pmo = [];
        $sum = 0;
        foreach ($partnerMonthOrderSum as $pmos){
            $sum += $pmos['real_amount_sum'];
            if ($pmos['id'] == $deliveryId){
                $pmo = $pmos;
            }
        }
        if ($pmo) {
            $pmo['sum'] = $sum;
            return RestfulResponse::success($pmo);
        }else{
            return RestfulResponse::error((new \Exception("没有该合伙人数据")));
        }
    }

}
