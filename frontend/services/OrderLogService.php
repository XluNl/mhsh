<?php


namespace frontend\services;


use common\models\Order;
use common\models\OrderLogs;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class OrderLogService extends \common\services\OrderLogService
{

    /**
     * 增加下单日志
     * @param Order $order
     */
    public static function addCreateOrderLog(Order $order)
    {
        list($result,$error)  = parent::addLogForCustomer($order,OrderLogs::ACTION_ORDER_CREATE);
        ExceptionAssert::assertTrue($result, StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, $error));
    }

    /**
     * 增加支付日志
     * @param Order $order
     */
    public static function addPayOrderLog(Order $order)
    {
        list($result,$error)  = parent::addLogForCustomer($order,OrderLogs::ACTION_PAY_SUCCESS);
        ExceptionAssert::assertTrue($result, StatusCode::createExpWithParams(StatusCode::PAY_BALANCE_ERROR, $error));
    }
}