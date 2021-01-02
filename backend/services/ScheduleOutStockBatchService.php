<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
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
     * @param $validateException BBusinessException
     * @return int|mixed
     * @throws \yii\db\Exception
     */
    public static function deliveryOutB($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName, $validateException){
        list($res,$error,$count)  = parent::deliveryOut($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName,null, null,ScheduleOutStockBatch::TYPE_HA,OrderLogs::ROLE_SYSTEM,OrderLogs::ACTION_ORDER_DELIVERY_OUT);
        BExceptionAssert::assertTrue($res,$validateException->updateMessage($error));
        return $count;
    }

}