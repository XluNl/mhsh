<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\OrderLogs;

class OrderLogService  extends \common\services\OrderLogService
{

    public static function addLogForDelivery($orderNo,$company_id,$operatorId,$operatorName,$action,$remark)
    {
        list($res,$error) = parent::addLog(OrderLogs::ROLE_DELIVERY,$orderNo,$company_id,$operatorId,$operatorName,$action,$remark);
        ExceptionAssert::assertTrue($res, StatusCode::createExpWithParams(StatusCode::ADD_ORDER_LOG_ERROR, $error));
    }
}