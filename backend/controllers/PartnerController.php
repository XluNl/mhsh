<?php


namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\DeliverySearch;
use backend\models\searches\UserSearch;
use backend\services\IndexStatisticService;
use backend\services\PartnerService;
use backend\services\RegionService;
use backend\utils\BRestfulResponse;
use Yii;

class PartnerController extends BaseController
{
    public function actionIndex() {
        return $this->render('index');
    }

    public function actionSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $summary = [];
        $summary['partner'] = IndexStatisticService::partnerSummary($companyId);
        $summary['partnerFans'] = IndexStatisticService::partnerFansSummary($companyId);
        $summary['partnerGoods'] = IndexStatisticService::partnerGoodsSummary($companyId);
        $summary['partnerOrder'] = IndexStatisticService::partnerOrderSummary($companyId);
        return BRestfulResponse::success($summary);
    }

    public function actionPartner(){
        $searchModel = new DeliverySearch();
        BackendCommon::addCompanyIdToParams('DeliverySearch');
        $dataProvider = $searchModel->searchFansCount(Yii::$app->request->queryParams);

        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        return $this->render('partner', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionFans(){
        $searchModel = new UserSearch();
        BackendCommon::addCompanyIdToParams('UserSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('fans', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionPartnerDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getPartnerTransactionSummaryEveryDay($companyId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionPartnerOrderDay(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getPartnerOrderTransactionSummaryEveryDay($companyId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionPartnerSortSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = IndexStatisticService::getSortSummary($companyId,$startDate,$endDate,1);
        return BRestfulResponse::success($data);
    }

    public function actionPartnerGoods(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = IndexStatisticService::getPartnerGoods($companyId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionPartnerSchedule(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = IndexStatisticService::getScheduleData($companyId,$startDate,$endDate);
        PartnerService::getPartnerOrderClearData($data,$companyId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionPartnerSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = IndexStatisticService::getDeliverySummary($companyId,$startDate,$endDate,1);
        return BRestfulResponse::success($data);
    }

}