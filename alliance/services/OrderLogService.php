<?php


namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\models\CommonStatus;
use common\models\OrderLogs;

class OrderLogService extends \common\services\OrderLogService
{

    public static function addLogForAlliance($orderNo, $company_id, $operatorId, $operatorName, $action, $remark)
    {
        $log = new OrderLogs();
        $log->company_id =$company_id;
        $log->order_no = $orderNo;
        $log->role = OrderLogs::ROLE_ALLIANCE;
        $log->name = $operatorName;
        $log->user_id = $operatorId;
        $log->action = $action;
        $log->status = CommonStatus::STATUS_ACTIVE;
        $log->remark = $remark;
        ExceptionAssert::assertTrue($log->save(), StatusCode::createExpWithParams(StatusCode::ADD_ORDER_LOG_ERROR, '订单日志保存失败'));
    }


}