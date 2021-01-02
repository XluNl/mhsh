<?php


namespace inner\services;


use common\models\CommonStatus;
use common\models\OrderLogs;
use inner\utils\ExceptionAssert;
use inner\utils\StatusCode;

class OrderLogService extends \common\services\OrderLogService
{

    public static function addLogForCourier($orderNo,$company_id,$operatorId,$operatorName,$action,$remark)
    {
        $log = new OrderLogs();
        $log->company_id =$company_id;
        $log->order_no = $orderNo;
        $log->role = OrderLogs::ROLE_COURIER;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        ExceptionAssert::assertTrue($log->save(), StatusCode::createExpWithParams(StatusCode::ADD_ORDER_LOG_ERROR, '保存失败'));
    }
}