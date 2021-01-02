<?php


namespace common\services;

use common\models\ScheduleOutStockBatch;

class ScheduleOutStockBatchService
{
    public static function deliveryOut($scheduleId, $orderTimeStart, $orderTimeEnd, $companyId, $operatorId, $operatorName,$ownerType, $ownerId,$scheduleOutStockBatchType,$orderLogRole,$orderLogAction){
        $orderGoodsModels = OrderGoodsService::getPrepareOrderGoods($scheduleId,$orderTimeStart,$orderTimeEnd,$companyId,$ownerType,$ownerId);
        if (empty($orderGoodsModels)){
            return [true,"",0];
        }
        list($result,$error,$batchModel) = self::createScheduleOutStockBatch($scheduleOutStockBatchType,$operatorId,$operatorName,$scheduleId,$companyId);
        if (!$result){
            return [false,$error,null];
        }
        $count = 0;
        foreach ($orderGoodsModels as $k=>$v){
            list($result,$error,$c) = OrderGoodsService::deliveryOutOrderGoods($batchModel['id'],$operatorId,$operatorName,$v,$orderLogRole,$orderLogAction);
            if (!$result){
                return [false,$error,null];
            }
            $count += $c;
        }
        return [true,'',$count];
    }

    private static function createScheduleOutStockBatch($type,$operatorId,$operatorName,$scheduleId,$companyId){
        $batch = new ScheduleOutStockBatch();
        $batch->type = $type;
        $batch->company_id = $companyId;
        $batch->order_goods_num = 0;
        $batch->sku_num = 0;
        $batch->schedule_id = $scheduleId;
        $batch->operator_id = $operatorId;
        $batch->operator_name = $operatorName;
        if (!$batch->save()){
            return [false,"发货批次创建失败",null];
        }
        return [true,"",$batch];
    }
}