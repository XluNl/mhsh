<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\DownloadService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use common\utils\DateTimeUtils;
use Yii;

class DownloadController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * 采购单
     */
    public function actionPurchaseList()
    {
        $scheduleId = Yii::$app->request->get('schedule_id');
        BExceptionAssert::assertNotBlank($scheduleId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $company_id = BackendCommon::getFCompanyId();
        DownloadService::downloadPurchaseList($scheduleId,$company_id,RedirectParams::create("记录不存在",['goods-schedule/index']));
        return;
    }

    /**
     * 采购单（集合）
     */
    public function actionPurchaseListCollection()
    {
        $collectionId = Yii::$app->request->get('collection_id');
        BExceptionAssert::assertNotBlank($collectionId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'collection_id'));
        $company_id = BackendCommon::getFCompanyId();
        DownloadService::downloadPurchaseListCollection($collectionId,$company_id,RedirectParams::create("记录不存在",['goods-schedule-collection/index']));
        return;
    }

    /**
     * 分拣单
     */
    public function actionSortingList()
    {
        $sortingDate = Yii::$app->request->get('sorting_date');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'sorting_date'));
        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        DownloadService::downloadSortingList($sortingDate,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 司机路线订单
     */
    public function actionRouteSummaryList()
    {
        $sortingDate = Yii::$app->request->get('date');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        DownloadService::downloadRouteSummary($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 配送团长接收确认单 2222
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliveryGoodsList()
    {
        $sortingDate = Yii::$app->request->get('date');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        DownloadService::downloadDeliveryGoods($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 团长订单明细导出  1111
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * zwb
     */
    public function actionOrderList(){
        $sortingDate = Yii::$app->request->get('date');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        DownloadService::downloadOrderList($sortingDate,$orderOwner,$company_id);
        return;
    }

    /**
     * 司机送货详单-装车单
     */
    public function actionRouteGoodsList()
    {
        $sortingDate = Yii::$app->request->get('date');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $orderOwner = Yii::$app->request->get('order_owner');
        BExceptionAssert::assertNotBlank($sortingDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
        $company_id = BackendCommon::getFCompanyId();
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($sortingDate),RedirectParams::create("时间格式错误：{$sortingDate}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        DownloadService::downloadRouteGoods($sortingDate,$orderOwner,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 团长订单下载
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliveryOrderList()
    {   
        $bizDate = Yii::$app->request->get('date');
        BExceptionAssert::assertNotBlank($bizDate,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'date'));
        $company_id = BackendCommon::getFCompanyId();
        DownloadService::downloadDeliveryOrder($bizDate,$company_id);
        return;
    }

    /**
     * 订单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionOrder()
    {
        $orderNo = Yii::$app->request->get('order_no');
        BExceptionAssert::assertNotBlank($orderNo,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
        $company_id = BackendCommon::getFCompanyId();
        DownloadService::downloadOrder($orderNo,$company_id);
        return;
    }


}