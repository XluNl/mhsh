<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\models\BusinessCommon;
use business\services\PushMessageService;
use business\utils\RestfulResponse;
use common\utils\DateTimeUtils;

class PushMessageController extends FController
{

    public function actionWaitOrder(){
        $deliveryId = BusinessCommon::requiredDeliveryId();
        $time = DateTimeUtils::formatYearAndMonthAndDay(time(),false);
        $count= PushMessageService::getWaitNoticeOrderCount($deliveryId,$time);
        return RestfulResponse::success($count);
    }

    public function actionSend(){
        $deliveryId = BusinessCommon::requiredDeliveryId();
        $time = DateTimeUtils::formatYearAndMonthAndDay(time(),false);
        $result = PushMessageService::prepareSendTemplate($deliveryId,$time);
        return RestfulResponse::success($result);
    }
}