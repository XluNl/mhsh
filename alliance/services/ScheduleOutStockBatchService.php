<?php


namespace alliance\services;


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
     * @param $allianceId
     * @return int
     */
    public static function deliveryOutA($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName, $allianceId){
        list($res,$error,$count)  = parent::deliveryOut($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName,GoodsConstantEnum::OWNER_HA, $allianceId,ScheduleOutStockBatch::TYPE_HA,OrderLogs::ROLE_ALLIANCE,OrderLogs::ACTION_HA_ORDER_DELIVERY_OUT);
        ExceptionAssert::assertTrue($res,BusinessException::create($error));
        return $count;
    }
}