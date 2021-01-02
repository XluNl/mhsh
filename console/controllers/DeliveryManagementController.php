<?php

namespace console\controllers;
use common\utils\DateTimeUtils;
use console\services\DeliveryManagementService;
use console\utils\ConsoleResult;
use yii\console\Controller;
use yii\helpers\Json;

class DeliveryManagementController extends Controller {

    /**
     * 发货通知补偿定时任务
     * @throws \yii\db\Exception
     */
    public function actionScheduleNotifyStorage(){
        $nowTime = time();
        list($successList,$failedList) = DeliveryManagementService::batchNotifyStorage($nowTime);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("发货通知补偿定时任务");
        $result->printNo("截止",$nowStr,"，需通知",$sumCount,"单,成功",$successCount,"单,失败",$failedCount,"单");
        $result->printNo('成功流水号：',Json::encode($successList));
        $result->printNo('失败流水号：',Json::encode($failedList));
        $result->showLog();
    }
}