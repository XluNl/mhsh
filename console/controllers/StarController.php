<?php

namespace console\controllers;
use common\utils\DateTimeUtils;
use console\services\CustomerInvitationLevelService;
use console\services\StarService;
use console\utils\ConsoleResult;
use Yii;
use yii\helpers\Json;
use yii\console\Controller;

class StarController extends Controller {

    /**
     * 同步商品至星球定时任务
     */
    public function actionSyncGoods(){
        $nowTime = time();
        list($res,$successCount,$failedCount,$error) = StarService::synchronizeGoods($nowTime);
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $result = ConsoleResult::create("同步商品至星球定时任务");
        if ($res){
            $result->printNo("截止",$nowStr,"总同步商品数为",$successCount+$failedCount,"个，成功:",$successCount,"个，失败:",$failedCount,'个');
        }
        else{
            $result->printNo('错误信息：',$error);
        }
        $result->showLog();
    }


}