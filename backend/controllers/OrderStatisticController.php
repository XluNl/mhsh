<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\OrderStatisticService;
use backend\services\SortDownloadService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\utils\DateTimeUtils;
use Yii;

class OrderStatisticController extends BaseController
{
    public $enableCsrfValidation = false;


    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * 订单统计-客户维度数据统计
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionCustomerStatistic()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $companyId = BackendCommon::getFCompanyId();
        OrderStatisticService::downloadCustomerStatisticData($bigSort,$goodsOwner,$orderTimeStart,$orderTimeEnd,$companyId);
    }


    /**
     * 订单统计-团长维度统计单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionDeliveryStatistic()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $companyId = BackendCommon::getFCompanyId();
        OrderStatisticService::downloadDeliveryStatisticData($bigSort,$goodsOwner,$orderTimeStart,$orderTimeEnd,$companyId);
    }

    /**
     * 订单统计-商品维度统计单
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function actionGoodsStatistic()
    {
        $bigSort = Yii::$app->request->get('big_sort');
        $goodsOwner = Yii::$app->request->get('owner');
        $orderTimeStart = Yii::$app->request->get('order_time_start');
        $orderTimeEnd = Yii::$app->request->get('order_time_end');
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeStart),RedirectParams::create("时间格式错误：{$orderTimeStart}",Yii::$app->request->referrer));
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($orderTimeEnd),RedirectParams::create("时间格式错误：{$orderTimeEnd}",Yii::$app->request->referrer));
        $companyId = BackendCommon::getFCompanyId();
        OrderStatisticService::downloadGoodsStatisticData($bigSort,$goodsOwner,$orderTimeStart,$orderTimeEnd,$companyId);
    }


}