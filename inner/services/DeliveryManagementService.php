<?php


namespace inner\services;


use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\models\StorageDeliveryOut;
use common\models\StorageSkuMapping;
use common\services\OrderService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use inner\models\InnerCommon;
use inner\utils\ExceptionAssert;
use inner\utils\exceptions\BusinessException;
use inner\utils\StatusCode;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\Json;

class DeliveryManagementService extends \common\services\DeliveryManagementService
{

    /**
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $scheduleIds
     * @param $companyIds
     * @param $pageNo
     * @param $pageSize
     * @return ActiveDataProvider
     */
    public static function getDeliveryDataByExpectArriveTimeI($expectArriveTime,$orderTimeStart,$orderTimeEnd,$scheduleIds,$companyIds,$pageNo,$pageSize){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $goodsScheduleTable = GoodsSchedule::tableName();
        //$storageSkuMappingTable = StorageSkuMapping::tableName();
        $deliveryStatusPrepare = OrderGoods::DELIVERY_STATUS_PREPARE;

        $ownerType = [GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA];
        $ownerId = null;

        $conditions = [
            'and',
            ['>',"{$goodsScheduleTable}.storage_sku_id",0],
        ];
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
        if (StringUtils::isNotEmpty($scheduleIds)){
            $conditions[] = ["{$orderGoodsTable}.schedule_id"=>$scheduleIds];
        }
        $conditions[] = [
            "{$orderGoodsTable}.company_id"=>$companyIds,
            "{$orderTable}.order_status"=>Order::$activeStatusArr,
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];

        $query  = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->innerJoin($goodsScheduleTable,"{$orderGoodsTable}.schedule_id={$goodsScheduleTable}.id")
            ->select([
                "SUM({$orderGoodsTable}.num) as sold_amount",
                "SUM(case when  {$orderGoodsTable}.delivery_status in ({$deliveryStatusPrepare}) then {$orderGoodsTable}.num else 0 end) as un_delivery_amount",
                "{$orderGoodsTable}.expect_arrive_time",
                "{$goodsScheduleTable}.storage_sku_id",
                "{$goodsScheduleTable}.storage_sku_num",
                "{$orderGoodsTable}.schedule_id",
                "{$orderGoodsTable}.schedule_name",
                "{$orderGoodsTable}.goods_id",
                "{$orderGoodsTable}.goods_name",
                "{$orderGoodsTable}.sku_id",
                "{$orderGoodsTable}.sku_name"
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.schedule_id"])
            ->orderBy("{$goodsScheduleTable}.storage_sku_id desc");

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' =>$pageNo-1,
                'pageSize'=>$pageSize,
            ],
        ]);
        return $provider;
    }

    /**
     * 单storageSkuId发货清单
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $scheduleId
     * @param $companyIds
     * @param $pageNo
     * @param $pageSize
     * @return ActiveDataProvider
     */
    public static function getScheduleDataByExpectArriveTimeI($expectArriveTime,$orderTimeStart, $orderTimeEnd,$scheduleId,$companyIds,$pageNo,$pageSize){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $goodsScheduleTable = GoodsSchedule::tableName();
        $deliveryStatusPrepare = OrderGoods::DELIVERY_STATUS_PREPARE;

        $ownerType = [GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA];
        $ownerId = null;

        $conditions = [
            'and',
            ['>',"{$goodsScheduleTable}.storage_sku_id",0],
        ];
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
        if (StringUtils::isNotBlank($scheduleId)){
            $conditions[] = ["{$orderGoodsTable}.schedule_id"=>$scheduleId];
        }
        $conditions[] = [
            "{$orderGoodsTable}.company_id"=>$companyIds,
            "{$orderTable}.order_status"=>Order::$activeStatusArr,
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];

        $query  = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->innerJoin($goodsScheduleTable,"{$orderGoodsTable}.schedule_id={$goodsScheduleTable}.id")
            ->select([
                "SUM({$orderGoodsTable}.num) as sold_amount",
                "SUM(case when  {$orderGoodsTable}.delivery_status in ({$deliveryStatusPrepare}) then {$orderGoodsTable}.num else 0 end) as un_delivery_amount",
                "{$orderGoodsTable}.expect_arrive_time",
                "{$goodsScheduleTable}.storage_sku_id",
                "{$goodsScheduleTable}.storage_sku_num",
                "{$orderGoodsTable}.schedule_id",
                "{$orderGoodsTable}.schedule_name",
                "{$orderGoodsTable}.goods_id",
                "{$orderGoodsTable}.goods_name",
                "{$orderGoodsTable}.sku_id",
                "{$orderGoodsTable}.sku_name",
                "{$orderTable}.delivery_id",
                "{$orderTable}.delivery_name",
                "{$orderTable}.delivery_phone",
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.schedule_id","{$orderGoodsTable}.delivery_id"])
            ->orderBy("{$orderGoodsTable}.delivery_id desc");

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' =>$pageNo-1,
                'pageSize'=>$pageSize,
            ],
        ]);
        return $provider;
    }

    /**
     * 仓库发货
     * @param $tradeNo
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyIds
     * @param $scheduleIds
     * @param $deliveryIds
     * @param $operatorId
     * @param $operatorName
     * @return array[]
     * @throws BusinessException
     */
    public static function deliveryOut($tradeNo,$expectArriveTime,$orderTimeStart, $orderTimeEnd,$companyIds,$scheduleIds,$deliveryIds,$operatorId,$operatorName){
        $storageDeliveryOut = StorageDeliveryOut::find()->where(['trade_no'=>$tradeNo])->one();
        ExceptionAssert::assertNull($storageDeliveryOut,StatusCode::createExpWithParams(StatusCode::STORAGE_DELIVERY_OUT_ERROR,'重复流水号'));
        $orderGoodsModels = self::getPrepareOrderGoods($expectArriveTime,$orderTimeStart, $orderTimeEnd,$deliveryIds, $companyIds, $scheduleIds);
        ExceptionAssert::assertNotEmpty( $orderGoodsModels,StatusCode::createExpWithParams(StatusCode::STORAGE_DELIVERY_OUT_ERROR,'本次无商品可发货'));
        $storageSkuStatistic = [];
        $orderGoodsIds = [];
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($orderGoodsModels as $orderGoodsModel){
                list($result,$error) = self::deliveryOutOrderGoodsI($operatorId,$operatorName,$orderGoodsModel);
                ExceptionAssert::assertTrue($result,BusinessException::create("子单{$orderGoodsModel['id']}发货失败:".$error));
                $orderGoodsIds[] = $orderGoodsModel['id'];
                if (!key_exists($orderGoodsModel['storage_sku_id'],$storageSkuStatistic)){
                    $storageSkuStatistic[$orderGoodsModel['storage_sku_id']]=['storage_sku_id'=>$orderGoodsModel['storage_sku_id'],'num'=>0,'storage_sku_num'=>$orderGoodsModel['storage_sku_num']];
                }
                $storageSkuStatistic[$orderGoodsModel['storage_sku_id']]['num'] = $orderGoodsModel['num'];
            }

            //保存流水记录
            $storageDeliveryOut = new StorageDeliveryOut();
            $storageDeliveryOut->trade_no = $tradeNo;
            $storageDeliveryOut->operator_id = $operatorId;
            $storageDeliveryOut->operator_name = $operatorName;
            $storageDeliveryOut->status = StorageDeliveryOut::STATUS_UN_CHECK;
            $storageDeliveryOut->order_goods_ids = implode(',',$orderGoodsIds);
            $storageDeliveryOut->storage_sku_statistic = Json::encode(array_values($storageSkuStatistic));
            ExceptionAssert::assertTrue($storageDeliveryOut->save(),BusinessException::create(InnerCommon::getModelErrors($storageDeliveryOut)));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            throw StatusCode::createExpWithParams(StatusCode::STORAGE_DELIVERY_OUT_ERROR,$e->getMessage());
        }
        return self::assembleStorageInfo($storageDeliveryOut);

    }


    /**
     * @param $expectArriveTime
     * @param $newExpectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyIds
     * @param $scheduleIds
     * @param $deliveryIds
     * @return int
     * @throws BusinessException
     */
    public static function modifyExpectArriveTimeI($expectArriveTime,$newExpectArriveTime,$orderTimeStart, $orderTimeEnd,$companyIds,$scheduleIds,$deliveryIds){
        $orderGoodsModels = self::getPrepareOrderGoods($expectArriveTime,$orderTimeStart, $orderTimeEnd,$deliveryIds, $companyIds, $scheduleIds);
        ExceptionAssert::assertNotEmpty($orderGoodsModels,StatusCode::createExpWithParams(StatusCode::STORAGE_MODIFY_EXPECT_ARRIVE_TIME_ERROR,'本次无商品可修改送达时间，请刷新重试'));
        $orderGoodsIds = ArrayUtils::getColumnWithoutNull('id',$orderGoodsModels);
        try {
            $updateCount = OrderGoods::updateAll(['expect_arrive_time'=>$newExpectArriveTime,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$orderGoodsIds,'expect_arrive_time'=>$expectArriveTime]);
            return $updateCount;
        }
        catch (\Exception $e){
            throw StatusCode::createExpWithParams(StatusCode::STORAGE_MODIFY_EXPECT_ARRIVE_TIME_ERROR,$e->getMessage());
        }
    }




    /**
     * @param $operatorId
     * @param $operatorName
     * @param $orderGoodsModel
     * @return array
     */
    private static function deliveryOutOrderGoodsI($operatorId,$operatorName,$orderGoodsModel){
        try {
            $updateCount = OrderGoods::updateAll(['delivery_status' => OrderGoods::DELIVERY_STATUS_DELIVERY, 'updated_at' => DateTimeUtils::parseStandardWLongDate()], ['id' => $orderGoodsModel['id'], 'delivery_status' => OrderGoods::DELIVERY_STATUS_PREPARE]);
            if ($updateCount<1){
                return [false,"此订单商品已发货"];
            }
            list($res,$error) = OrderService::deliveryOutOrderStatusForOrderGoodsForStorage(OrderLogs::ROLE_STORAGE,OrderLogs::ACTION_STORAGE_DELIVERY_OUT,$orderGoodsModel['order_no'],$orderGoodsModel['company_id'],$operatorId,$operatorName);
            if (!$res){
                return [false,$error];
            }
            $updateCount = GoodsSku::updateAllCounters(['sku_stock'=>$orderGoodsModel['num']],['company_id'=>$orderGoodsModel['company_id'],'id'=>$orderGoodsModel['sku_id']]);
            if ($updateCount<1){
                return [false,"属性{$orderGoodsModel['id']}更新库存失败"];
            }
            return [true,''];
        }
        catch (\Exception $e){
            return [false,$e->getMessage()];
        }
    }

    /**
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $deliveryIds
     * @param $companyIds
     * @param $scheduleIds
     * @return array
     */
    private static function getPrepareOrderGoods($expectArriveTime,$orderTimeStart, $orderTimeEnd,$deliveryIds, $companyIds, $scheduleIds)
    {
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $goodsScheduleTable = GoodsSchedule::tableName();
        $ownerType = [GoodsConstantEnum::OWNER_HA, GoodsConstantEnum::OWNER_SELF];
        $ownerId = null;
        $conditions = [
            'and',
            ['>',"{$goodsScheduleTable}.storage_sku_id",0],
        ];
        if (StringUtils::isNotBlank($orderTimeStart)){
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        if (StringUtils::isNotBlank($ownerType)) {
            $conditions[] = ["{$orderTable}.order_owner" => $ownerType];
        }
        if (StringUtils::isNotBlank($ownerId)) {
            $conditions[] = ["{$orderTable}.order_owner_id" => $ownerId];
        }
        if (StringUtils::isNotEmpty($deliveryIds)) {
            $conditions[] = ["{$orderTable}.delivery_id" => $deliveryIds];
        }
        $conditions[] = [
            "{$orderGoodsTable}.expect_arrive_time" => $expectArriveTime,
            "{$orderGoodsTable}.company_id" => $companyIds,
            "{$orderGoodsTable}.schedule_id" => $scheduleIds,
            "{$orderGoodsTable}.status" => CommonStatus::STATUS_ACTIVE,
            "{$orderGoodsTable}.delivery_status" => OrderGoods::DELIVERY_STATUS_PREPARE,
            "{$orderTable}.order_status" => [Order::ORDER_STATUS_PREPARE, Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY]
        ];

        $orderGoodsModels = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable, "{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->innerJoin($goodsScheduleTable, "{$goodsScheduleTable}.id={$orderGoodsTable}.schedule_id")
            ->where($conditions)->select(["{$orderGoodsTable}.*", "{$goodsScheduleTable}.storage_sku_id", "{$goodsScheduleTable}.storage_sku_num"])
            ->orderBy("{$goodsScheduleTable}.storage_sku_id,{$orderGoodsTable}.schedule_id")
            ->all();
        return $orderGoodsModels;
    }

}