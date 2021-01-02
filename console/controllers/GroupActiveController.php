<?php

namespace console\controllers;

use common\utils\DateTimeUtils;
use console\services\GroupOrderService;
use console\utils\GlobalConsoleLogger;
use yii\console\Controller;
use yii\helpers\Json;


class GroupActiveController extends Controller
{
    /**
     * 拼团自动结算定时任务
     */
    public function actionScheduleCloseGroupRoom()
    {
        $nowTime = time();
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        list($successList, $failedList) = GroupOrderService::batchCloseGroupRoom($nowStr);
        GlobalConsoleLogger::initLogger("拼团自动结算定时任务", 'group');
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount + $failedCount;
        GlobalConsoleLogger::printNo("截止", $nowStr, ",需处理", $sumCount, "个,成功", $successCount, "单,失败", $failedCount, "单");
        GlobalConsoleLogger::printNo('成功流水号：', Json::encode($successList));
        GlobalConsoleLogger::printNo('失败流水号：', Json::encode($failedList));
        GlobalConsoleLogger::showLog();
    }

    /**
     * 拼团退款单结算定时任务
     */
    public function actionScheduleDoGroupRoomRefund(){
        $nowTime = time();
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        list($successList, $failedList) = GroupOrderService::batchDoGroupRoomRefund($nowStr);
        GlobalConsoleLogger::initLogger("拼团退款单结算定时任务", 'group');
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount + $failedCount;
        GlobalConsoleLogger::printNo("截止", $nowStr, ",需处理", $sumCount, "个,成功", $successCount, "单,失败", $failedCount, "单");
        GlobalConsoleLogger::printNo('成功流水号：', Json::encode($successList));
        GlobalConsoleLogger::printNo('失败流水号：', Json::encode($failedList));
        GlobalConsoleLogger::showLog();
    }

}