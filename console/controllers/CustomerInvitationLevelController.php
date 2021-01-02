<?php

namespace console\controllers;
use common\utils\DateTimeUtils;
use console\services\CustomerInvitationLevelService;
use console\utils\ConsoleResult;
use yii\console\Controller;
use yii\helpers\Json;

class CustomerInvitationLevelController extends Controller {

    /**
     * 刷新一级二级邀请人数定时任务
     */
    public function actionFreshCount(){
        $nowTime = time();
        list($scanCount,$error) = CustomerInvitationLevelService::refreshCount();
        $nowStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $result = ConsoleResult::create("刷新一级二级邀请人数定时任务");
        $result->printNo("截止",$nowStr,"需刷新",$scanCount,"人","其中错误个数为",count($error),"个");
        if (count($error)>0){
            $result->printNo('错误信息：',Json::encode($error));
        }
        $result->showLog();
    }


}