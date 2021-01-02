<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;

class CustomerBalanceService extends \common\services\CustomerBalanceService
{

    /**
     * 订单完成后多退少补余额
     * @param $order
     * @param $amount
     * @param $remark
     * @param $operatorId
     * @param $operatorName
     */
    public static function completeBalance($order, $amount, $remark, $operatorId, $operatorName){
        list($success,$errorMsg) = parent::adjustBalance($order, $amount, $remark, $operatorId, $operatorName);
        BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
    }

}