<?php

namespace frontend\modules\customer\controllers;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\services\CartOperationService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class CartController extends Controller {

	public function actionOperate() {
	    $delivery = FrontendCommon::requiredCanOrderDelivery();
        $schedule_id = Yii::$app->request->get("schedule_id");
		$command = Yii::$app->request->get("command" );
		$command_array = ['minus', 'plus', 'empty', 'modify'];
		$sku_num = Yii::$app->request->get("sku_num", 1);
		ExceptionAssert::assertNotNull($schedule_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        ExceptionAssert::assertTrue(in_array($command, $command_array),StatusCode::createExp(StatusCode::STATUS_PARAMS_ERROR));
        $userId = FrontendCommon::requiredUserId();
        switch ($command) {
            case 'plus':
                CartOperationService::addGoods($userId,$schedule_id,$sku_num);
                break;
            case 'minus':
                CartOperationService::delGoods($userId,$schedule_id,$sku_num);
                break;
            case 'empty':
                CartOperationService::emptyGoods($userId);
                break;
            case 'modify':
                CartOperationService::modifyGoods($userId,$schedule_id,$sku_num);
                break;
        }
		return RestfulResponse::success(true);
	}


  /*  public function actionList() {
        $company_id = FrontendCommon::getFCompanyId();
        $delivery_id = FrontendCommon::requiredDeliveryId();
        $userId = FrontendCommon::getUserId();
        $goods_list = CartOperationService::listGoods($userId);
        $data = CartService::getCartGoodsDetail($userId,$company_id,$delivery_id,$goods_list);
        return RestfulResponse::success($data);
    }*/


    public function actionCheck() {
        $schedule_id = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotNull($schedule_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $userId = FrontendCommon::requiredUserId();
        CartOperationService::check($userId,$schedule_id);
        return RestfulResponse::success(true);
    }



    public function actionUnCheck() {
        $schedule_id = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotNull($schedule_id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $userId = FrontendCommon::requiredUserId();
        CartOperationService::unCheck($userId,$schedule_id);
        return RestfulResponse::success(true);
    }

    public function actionCheckAll() {
        $userId = FrontendCommon::requiredUserId();
        $ownerType = Yii::$app->request->get("owner_type");
        CartOperationService::check($userId,null,$ownerType);
        return RestfulResponse::success(true);
    }

    public function actionUnCheckAll() {
        $userId = FrontendCommon::requiredUserId();
        $ownerType = Yii::$app->request->get("owner_type");
        CartOperationService::unCheck($userId,null,$ownerType);
        return RestfulResponse::success(true);
    }


    public function actionSummary() {
        $userId = FrontendCommon::getUserId();
        if (StringUtils::isBlank($userId)){
            return RestfulResponse::success([
                'detail'=>ArrayUtils::mapToArray(CartOperationService::getZeroDetail(),'ownerType','num'),
                'total'=>0,
            ]);
        }
        $deliveryId = FrontendCommon::getDeliveryId();
        $companyId = FrontendCommon::requiredFCompanyId();
        $res = CartOperationService::summaryCart($userId,$companyId,$deliveryId);
        return RestfulResponse::success($res);
    }

}