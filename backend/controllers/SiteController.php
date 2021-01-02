<?php
namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\IndexStatisticService;
use backend\utils\BRestfulResponse;
use Yii;

/**
 * Site controller
 */
class SiteController extends BaseController
{
    
    public function actionIndex() {
		return $this->render('index');
	}


	public function actionSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $summary = [];
        $summary['delivery'] = IndexStatisticService::deliverySummary($companyId);
        $summary['order_goods'] = IndexStatisticService::orderGoodsSummary($companyId);
        $summary['customer'] = IndexStatisticService::customerSummary($companyId);
        $summary['order_need_amount'] = IndexStatisticService::orderNeedAmountSummary($companyId);
        IndexStatisticService::mulData("actionSummary",$summary,$companyId);
        return BRestfulResponse::success($summary);
    }


    public function actionDeliveryDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getDeliverySummaryEveryDay($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionDeliveryDay",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionOrderDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getOrderSummaryEveryDay($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionOrderDay",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionSortSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getSortSummary($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionSortSummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionGoodsSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = IndexStatisticService::getGoodsSummary($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionGoodsSummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }


    public function actionDeliverySummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = IndexStatisticService::getDeliverySummary($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionDeliverySummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionUserInfoSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $data = IndexStatisticService::getUserInfoSummary($companyId);
        IndexStatisticService::mulData("actionUserInfoSummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionOrderDeliveryDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getOrderDeliveryDay($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionOrderDeliveryDay",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionDownloadDashboard(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        IndexStatisticService::downloadDashboard($companyId,$startDate,$endDate);
        return;
    }





}
