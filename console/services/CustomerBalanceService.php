<?php


namespace console\services;


use backend\utils\BStatusCode;
use console\utils\ExceptionAssert;
use console\utils\StatusCode;

class CustomerBalanceService extends \common\services\CustomerBalanceService
{

    public static function completeBalance($order, $amount, $remark, $operatorId, $operatorName){
        list($success,$errorMsg) = parent::adjustBalance($order, $amount, $remark, $operatorId, $operatorName);
        ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(BStatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
    }
}