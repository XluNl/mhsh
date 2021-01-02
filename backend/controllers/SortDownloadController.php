<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\SortDownloadService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\utils\DateTimeUtils;
use Yii;

class SortDownloadController extends BaseController
{
    public $enableCsrfValidation = false;


    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * 按分类导出分拣单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSortCollection()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $date = Yii::$app->request->get('date');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        SortDownloadService::downloadSortCollectionList($bigSort,$goodsOwner,$date,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 按分类导出明细单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSortDetail()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $date = Yii::$app->request->get('date');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        SortDownloadService::downloadSortDetailList($bigSort,$goodsOwner,$date,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

    /**
     * 分类-配送团长接收确认单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionSortDeliveryGoods()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        $date = Yii::$app->request->get('date');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        SortDownloadService::downloadSortDeliveryGoods($bigSort,$goodsOwner,$date,$orderTimeStart,$orderTimeEnd,$company_id);
        return;
    }

}