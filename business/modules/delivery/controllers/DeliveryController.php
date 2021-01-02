<?php

namespace business\modules\delivery\controllers;
use business\components\FController;
use business\models\BusinessCommon;
use business\services\DeliveryService;
use business\services\TagService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;

class DeliveryController extends FController {

	public function actionChange() {
        $delivery_id = Yii::$app->request->get("delivery_id");
        ExceptionAssert::assertNotNull($delivery_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_id'));
        $userId= BusinessCommon::requiredUserId();
        DeliveryService::changeSelectedDeliveryId($userId,$delivery_id);
		return RestfulResponse::success($delivery_id);
	}

	public function actionList(){
        $userId= BusinessCommon::requiredUserId();
        $deliveryModels = DeliveryService::getListWithDefault($userId);
        $deliveryModels = DeliveryService::batchSetRenamePicture($deliveryModels);
        return RestfulResponse::success($deliveryModels);
    }

    public function actionInfo() {
        $userId= BusinessCommon::requiredUserId();
        $deliveryId = DeliveryService::getSelectedDeliveryId($userId);
        $deliveryModel = DeliveryService::getActiveModel($deliveryId);
        $deliveryModel = DeliveryService::setRenamePicture($deliveryModel);
        return RestfulResponse::success($deliveryModel);
    }

    public function actionAuth() {
        $user = BusinessCommon::requiredUserModel();
        $deliveryModel = BusinessCommon::requiredDelivery();
        $config = DeliveryService::auth($user,$deliveryModel);
        return RestfulResponse::success($config);
    }

    public function actionPlatformRoyalty() {
        $deliveryModel = BusinessCommon::requiredDelivery();
        $value = TagService::getPlatformRoyaltyValue($deliveryModel['company_id'],$deliveryModel['id']);
        return RestfulResponse::success($value);
    }

    public function actionNotify(){
        Yii::error(Yii::$app->request->getRawBody(),'pay');
        $response = Yii::$app->businessWechat->payment->handlePaidNotify(function ($message, $fail) {
            return DeliveryService::payCallBack($message,$fail);
        });
        $response->send();
        exit(0);
    }


}