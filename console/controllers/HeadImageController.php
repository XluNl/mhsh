<?php

namespace console\controllers;
use common\utils\DateTimeUtils;
use console\services\HeadImageService;
use console\utils\ConsoleResult;
use yii\console\Controller;

class HeadImageController extends Controller {

    /**
     * 刷新空头像信息任务
     */
    public function actionFlushZeroDataHeadImage(){
        $baseDir = "/data/images/mhsh/public/uploads/pub";
        //$baseDir = "C:\\Users\\hzg\\Desktop\\filename";
        $nowTime = time();
        list($successList,$failedList) = HeadImageService::flushAllZeroDataHeadImage($baseDir);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("刷新头像信息任务");
        $result->printNo("截止",$nowStr,"，需处理",$sumCount,"个,处理成功",$successCount,"个,处理失败",$failedCount,"个");
        $result->printNo('成功信息：');
        $this->showFlushZeroDataHeadImageResult($result,$successList);
        $result->printNo('失败信息：');
        $this->showFlushZeroDataHeadImageResult($result,$failedList);
        $result->showLog();
    }


    private function showFlushZeroDataHeadImageResult($result,$list){
        foreach ($list as $v){
            $result->printNo("file:{$v['file']},res:{$v['res']},error:{$v['error']}");
        }
    }


    /**
     * 刷新团长头像信息任务
     */
    public function actionFlushDeliveryHeadImage(){
        $nowTime = time();
        list($successList,$failedList) = HeadImageService::flushAllDeliveryHeadUrl();
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("刷新团长头像信息任务");
        $result->printNo("截止",$nowStr,"，需处理",$sumCount,"个,处理成功",$successCount,"个,处理失败",$failedCount,"个");
        $result->printNo('成功信息：');
        $this->showFlushDeliveryHeadImageResult($result,$successList);
        $result->printNo('失败信息：');
        $this->showFlushDeliveryHeadImageResult($result,$failedList);
        $result->showLog();
    }

    private function showFlushDeliveryHeadImageResult($result,$list){
        foreach ($list as $v){
            $result->printNo("deliveryId:{$v['deliveryId']},res:{$v['res']},error:{$v['error']}");
        }
    }


    /**
     * 刷新用户基本头像信息任务
     */
    public function actionFlushUserInfoHeadImage(){
        $nowTime = time();
        list($successList,$failedList) = HeadImageService::flushAllUserInfoHeadUrl();
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $successCount = count($successList);
        $failedCount = count($failedList);
        $sumCount = $successCount+$failedCount;
        $result = ConsoleResult::create("刷新用户基本头像信息任务");
        $result->printNo("截止",$nowStr,"，需处理",$sumCount,"个,处理成功",$successCount,"个,处理失败",$failedCount,"个");
        $result->printNo('成功信息：');
        $this->showFlushUserInfoHeadImageResult($result,$successList);
        $result->printNo('失败信息：');
        $this->showFlushUserInfoHeadImageResult($result,$failedList);
        $result->showLog();
    }

    private function showFlushUserInfoHeadImageResult($result,$list){
        foreach ($list as $v){
            $result->printNo("userInfoId:{$v['userInfoId']},res:{$v['res']},error:{$v['error']}");
        }
    }

}