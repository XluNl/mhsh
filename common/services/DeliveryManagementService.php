<?php


namespace common\services;

use common\models\Order;
use common\models\OrderGoods;
use common\models\StorageSkuMapping;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class DeliveryManagementService
{
    /**
     * 发货列表
     * @param $ownerType
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $ownerId
     * @return Query
     */
    public static function getDeliveryDataByExpectArriveTime($ownerType, $expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, $ownerId){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $deliveryStatusPrepare = OrderGoods::DELIVERY_STATUS_PREPARE;

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
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$activeStatusArr,
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];

        $query  = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->select([
                "SUM({$orderGoodsTable}.num) as sold_amount",
                "SUM(case when  {$orderGoodsTable}.delivery_status in ({$deliveryStatusPrepare}) then {$orderGoodsTable}.num else 0 end) as un_delivery_amount",
                "{$orderGoodsTable}.schedule_id",
                "{$orderGoodsTable}.schedule_name",
                "{$orderGoodsTable}.goods_id",
                "{$orderGoodsTable}.goods_name",
                "{$orderGoodsTable}.sku_id",
                "{$orderGoodsTable}.sku_name",
                "{$orderGoodsTable}.sku_unit",
                "{$orderGoodsTable}.expect_arrive_time",
                "{$orderGoodsTable}.goods_owner",
                "{$orderGoodsTable}.company_id",
            ])->where($conditions)->groupBy(['schedule_id']
            )->orderBy('schedule_id');
        return $query;
    }

    /**
     * @param $storageDeliveryOut
     * @return array[]
     */
    public static function assembleStorageInfo($storageDeliveryOut){
        $res = [
            'orderGoodsList'=>[],
            'orderList'=>[],
            //'storageSkuStatistic'=>[],
        ];
        if (StringUtils::isBlank($storageDeliveryOut['storage_sku_statistic'])||StringUtils::isBlank($storageDeliveryOut['order_goods_ids'])){
            return $res;
        }
        //$res['storageSkuStatistic'] = Json::decode($storageDeliveryOut['storage_sku_statistic']);
        $orderGoodsIds =explode(',',$storageDeliveryOut['order_goods_ids']);
        $orderGoodsTable = OrderGoods::tableName();
        $storageSkuMappingTable = StorageSkuMapping::tableName();
        $conditions = [
            "{$orderGoodsTable}.id" => $orderGoodsIds,
        ];
        $orderGoodsModels = (new Query())->from($orderGoodsTable)
            ->innerJoin($storageSkuMappingTable, "{$storageSkuMappingTable}.sku_id={$orderGoodsTable}.sku_id")
            ->where($conditions)
            ->select(["{$orderGoodsTable}.*", "{$storageSkuMappingTable}.storage_sku_id", "{$storageSkuMappingTable}.storage_sku_num"])
            ->orderBy("{$storageSkuMappingTable}.storage_sku_id,{$orderGoodsTable}.sku_id")
            ->all();

        $orderGoodsModels = GoodsDisplayDomainService::batchRenameImageUrlOrSetDefault($orderGoodsModels,'goods_img');
        $orderGoodsModels = GoodsDisplayDomainService::batchRenameImageUrlOrSetDefault($orderGoodsModels,'sku_img');
        $res['orderGoodsList'] = $orderGoodsModels;


        $orderNos = ArrayUtils::getColumnWithoutNull('order_no',$orderGoodsModels);
        $orderModels = OrderService::getAllOrderModel($orderNos);


        $res['orderList'] = $orderModels;
        return $res;
    }



}