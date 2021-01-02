<?php


namespace common\services;


use common\models\Common;
use common\models\CommonStatus;
use common\models\Order;
use common\models\OrderGoods;
use common\models\ScheduleOutStockBatch;
use common\models\ScheduleOutStockLog;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;

class OrderGoodsService
{

    public static function getModels($id,$orderNo=null,$company_id=null,$model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isBlank($orderNo)){
            $conditions['order_no'] = $orderNo;
        }
        if (!StringUtils::isBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return OrderGoods::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(OrderGoods::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getPrepareOrderGoods($scheduleId, $orderTimeStart, $orderTimeEnd, $company_id,$ownerType, $ownerId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();

        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        if (StringUtils::isNotBlank($ownerType)){
            $conditions[] = ["{$orderTable}.order_owner"=>$ownerType];
        }
        if (StringUtils::isNotBlank($ownerId)){
            $conditions[] = ["{$orderTable}.order_owner_id"=>$ownerId];
        }
        $conditions[] = [
            "{$orderGoodsTable}.schedule_id"=>$scheduleId,
            "{$orderGoodsTable}.company_id"=>$company_id,
            "{$orderGoodsTable}.status"=>CommonStatus::STATUS_ACTIVE,
            "{$orderGoodsTable}.delivery_status"=>OrderGoods::DELIVERY_STATUS_PREPARE,
            "{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]
        ];

        $orderGoodsModels = (new Query())->from($orderGoodsTable)->leftJoin($orderTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")->where($conditions)->select(["{$orderGoodsTable}.*"])->all();
        return empty($orderGoodsModels)?[]:$orderGoodsModels;
    }


    public static function deliveryOutOrderGoods($batchId,$operatorId,$operatorName,$orderGoodsModel,$orderLogRole,$orderLogAction){
        $updateCount = OrderGoods::updateAll(['delivery_status' => OrderGoods::DELIVERY_STATUS_SELF_DELIVERY, 'updated_at' => DateTimeUtils::parseStandardWLongDate()], ['id' => $orderGoodsModel['id'], 'delivery_status' => OrderGoods::DELIVERY_STATUS_PREPARE]);
        if ($updateCount<1){
            return [false,"此订单商品已发货",null];
        }
        $log = new ScheduleOutStockLog();
        $log->batch_id = $batchId;
        $log->order_no = $orderGoodsModel['order_no'];
        $log->order_goods_id = $orderGoodsModel['id'];
        $log->num = $orderGoodsModel['num'];
        $log->operator_id = $operatorId;
        $log->operator_name = $operatorName;
        $log->company_id = $orderGoodsModel['company_id'];
        if (!$log->save()){
            return [false,'出货日志记录失败',null];
        }
        $updateCount = ScheduleOutStockBatch::updateAllCounters(['order_goods_num'=>1,'sku_num'=>$log->num],['id'=>$batchId]);
        if ($updateCount<1){
            return [false,'发货批次更新失败',null];
        }
        list($res,$error) = OrderService::deliveryOutOrderStatusForOrderGoods($orderLogRole,$orderLogAction,$orderGoodsModel['order_no'],$orderGoodsModel['company_id'],$operatorId,$operatorName);
        if (!$res){
            return [false,$error,null];
        }
        return [true,'',$log->num];
    }
}