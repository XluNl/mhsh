<?php
namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\AllianceStatisticService;
use backend\utils\BRestfulResponse;
use Yii;

/**
 * AllianceStatisticController
 */
class AllianceStatisticController extends BaseController
{
    
    public function actionIndex() {
		return $this->render('index');
	}


	public function actionSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $summary = AllianceStatisticService::headerSummary($companyId);
        return BRestfulResponse::success($summary);
    }


    public function actionOrderDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = AllianceStatisticService::getOrderSummaryEveryDay($companyId,$startDate,$endDate);
       // AllianceStatisticService::mulData("actionOrderDay",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionSortSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = AllianceStatisticService::getSortSummary($companyId,$startDate,$endDate);
       // AllianceStatisticService::mulData("actionSortSummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }

    public function actionGoodsSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = AllianceStatisticService::getGoodsSummary($companyId,$startDate,$endDate);
        //AllianceStatisticService::mulData("actionGoodsSummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }


    public function actionAllianceSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = AllianceStatisticService::getAllianceSummary($companyId,$startDate,$endDate);
        //AllianceStatisticService::mulData("actionDeliverySummary",$data,$companyId);
        return BRestfulResponse::success($data);
    }



    public function actionDownloadDashboard(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        AllianceStatisticService::downloadDashboard($companyId,$startDate,$endDate);
    }





}
