<?php

namespace business\modules\delivery\controllers;
use business\components\FController;
use business\models\BusinessCommon;
use business\services\OrderStatisticService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use Yii;

class OrderStatisticController extends FController {

	public $enableCsrfValidation = false;

    public function actionOrderSummaryDay() {
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        ExceptionAssert::assertNotBlank($startDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'start_date'));
        ExceptionAssert::assertNotBlank($endDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'end_date'));
        $delivery = BusinessCommon::requiredDelivery();
        $data = OrderStatisticService::getOrderSummaryEveryDay($delivery['company_id'],$delivery['id'],$startDate,$endDate);
        return RestfulResponse::success($data);
    }

	public function actionSortSummary() {
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        ExceptionAssert::assertNotBlank($startDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'start_date'));
        ExceptionAssert::assertNotBlank($endDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'end_date'));
        $delivery = BusinessCommon::requiredDelivery();
        $data = OrderStatisticService::getSortSummary($delivery['company_id'],$delivery['id'],$startDate,$endDate);
        return RestfulResponse::success($data);
	}


    public function actionGoodsSummary() {
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $bigSortId = Yii::$app->request->get('big_sort',null);
        ExceptionAssert::assertNotBlank($startDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'start_date'));
        ExceptionAssert::assertNotBlank($endDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'end_date'));
        $delivery = BusinessCommon::requiredDelivery();
        $data = OrderStatisticService::getGoodsSummary($delivery['company_id'],$delivery['id'],$startDate,$endDate,$bigSortId);
        return RestfulResponse::success($data);
    }

    public function actionFansSummary(){
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        ExceptionAssert::assertNotBlank($startDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'start_date'));
        ExceptionAssert::assertNotBlank($endDate,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'end_date'));
        $delivery = BusinessCommon::requiredDelivery();
        $data = OrderStatisticService::getFansSummary($delivery['company_id'],$delivery['id'],$startDate,$endDate);
        return RestfulResponse::success($data);
    }

}
