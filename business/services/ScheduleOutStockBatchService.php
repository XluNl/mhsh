<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use common\models\GoodsConstantEnum;
use common\models\OrderLogs;
use common\models\ScheduleOutStockBatch;

class ScheduleOutStockBatchService extends \common\services\ScheduleOutStockBatchService
{
    /**
     * 按批次发货（可指定时间）
     * @param $scheduleId
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @param $deliveryId
     * @return int
     */
    public static function deliveryOutB($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName, $deliveryId){
        list($res,$error,$count)  = parent::deliveryOut($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName,GoodsConstantEnum::OWNER_DELIVERY, $deliveryId,ScheduleOutStockBatch::TYPE_DELIVERY,OrderLogs::ROLE_DELIVERY,OrderLogs::ACTION_DELIVERY_ORDER_DELIVERY_OUT);
        ExceptionAssert::assertTrue($res,BusinessException::create($error));
        return $count;
    }
}