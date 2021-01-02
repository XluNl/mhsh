<?php

namespace console\controllers;
use common\utils\DateTimeUtils;
use console\services\OrderService;
use console\utils\ConsoleResult;
use yii\console\Controller;
use yii\helpers\Json;

class OrderController extends Controller {

    /**
     * 超时自动取消订单定时任务
     * @throws \yii\db\Exception
     */
    public function actionScheduleCancelOrder(){
        $nowTime = time();
        list($successOrders,$failedOrders) = OrderService::batchCancelUnPayOrder($nowTime);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successOrders);
        $failedCount = count($failedOrders);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("超时自动取消订单定时任务");
        $result->printNo("截止",$nowStr,"，需取消",$sumCount,"单,取消成功",$successCount,"单,取消失败",$failedCount,"单");
        $result->printNo('成功单号：',Json::encode($successOrders));
        $result->printNo('失败单号：',Json::encode($failedOrders));
        $result->showLog();
    }

    /**
     * 超时自动确认订单完成定时任务
     * @throws \yii\db\Exception
     */
    public function actionScheduleCompleteOrder(){
        $nowTime = time();
        list($successOrders,$failedOrders) = OrderService::batchCompleteOrder($nowTime);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successOrders);
        $failedCount = count($failedOrders);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("超时自动确认订单完成定时任务");
        $result->printNo("截止",$nowStr,"，需自动确认",$sumCount,"单,自动确认成功",$successCount,"单,自动确认失败",$failedCount,"单");
        $result->printNo('成功单号：',Json::encode($successOrders));
        $result->printNo('失败单号：',Json::encode($failedOrders));
        $result->showLog();
    }



    /**
     * 超时自动送达订单完成定时任务
     * @throws \yii\db\Exception
     */
    public function actionScheduleReceiveOrder(){
        $nowTime = time();
        list($successOrders,$failedOrders) = OrderService::batchReceiveOrder($nowTime);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successOrders);
        $failedCount = count($failedOrders);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("超时自动送达订单完成定时任务");
        $result->printNo("截止",$nowStr,"，需自动送达",$sumCount,"单,自动送达成功",$successCount,"单,自动送达失败",$failedCount,"单");
        $result->printNo('成功单号：',Json::encode($successOrders));
        $result->printNo('失败单号：',Json::encode($failedOrders));
        $result->showLog();
    }
}