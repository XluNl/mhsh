<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\CommonStatus;
use common\models\OrderLogs;

class OrderLogService extends \common\services\OrderLogService
{

    public static function addLogForSystem($orderNo,$company_id,$operatorId,$operatorName,$action,$remark)
    {
        $log = new OrderLogs();
        $log->company_id =$company_id;
        $log->order_no = $orderNo;
        $log->role = OrderLogs::ROLE_SYSTEM;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        BExceptionAssert::assertTrue($log->save(), BStatusCode::createExpWithParams(BStatusCode::ADD_ORDER_LOG_ERROR, '订单日志保存失败'));
    }
}