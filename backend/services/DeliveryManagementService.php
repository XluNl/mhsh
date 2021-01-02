<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\RoleEnum;
use common\models\RouteDelivery;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use kartik\grid\EditableColumnAction;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DeliveryManagementService extends \common\services\DeliveryManagementService
{
    /**
     * 获取预计送达数据
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @return Query
     */
    public static function getDeliveryDataByExpectArriveTimeB($expectArriveTime,$orderTimeStart,$orderTimeEnd,$companyId){
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),RedirectParams::create("时间格式错误：{$expectArriveTime}",['order/index']));
        if (StringUtils::isNotBlank($orderTimeStart)){
            BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeStart),RedirectParams::create("订单开始时间格式错误",['order/index']));
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeEnd),RedirectParams::create("订单截止时间格式错误",['order/index']));
        }
        return self::getDeliveryDataByExpectArriveTime([GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA],$expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, null);
    }


    /**
     * 发货管理时修改预计送达时间
     * @param $scheduleId
     * @param $expectArriveTime
     * @param $companyId
     * @return array
     * @throws \yii\db\Exception
     */
    public static function modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $scheduleModel = GoodsScheduleService::getActiveGoodsSchedule($scheduleId,$companyId);
            BExceptionAssert::assertNotNull($scheduleModel,BBusinessException::create("排期{$scheduleId}不存在"));
            BExceptionAssert::assertTrue($scheduleModel['expect_arrive_time']!=$expectArriveTime,BBusinessException::create("预计送达日期并未修改"));
            GoodsScheduleService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId);
            OrderService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId);
            $transaction->commit();
            return [true,''];
        }
        catch (\Exception $e){
            $transaction->rollBack();
            return [false,$e->getMessage()];
        }
    }


    /**
     * 批量发货
     * @param $scheduleIds
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    public static function deliveryOut($scheduleIds,$orderTimeStart,$orderTimeEnd,$companyId,$operatorId,$operatorName){
        $flag = true;
        $resultMsg = [];
        $scheduleModels = GoodsScheduleService::getActiveGoodsScheduleWithGoodsAndSkuB($scheduleIds,$companyId);
        $scheduleModels = ArrayUtils::index($scheduleModels,'schedule_id');
        foreach ($scheduleIds as $scheduleId){
            $transaction = Yii::$app->db->beginTransaction();
            try{
                BExceptionAssert::assertTrue(key_exists($scheduleId,$scheduleModels),BBusinessException::create("排期不存在"));
                $scheduleModel = $scheduleModels[$scheduleId];
                $count = ScheduleOutStockBatchService::deliveryOutB($scheduleId,$orderTimeStart,$orderTimeEnd,$companyId,$operatorId,$operatorName,BStatusCode::createExpWithParams(BStatusCode::DELIVERY_OUT_ERROR,'排期发货失败'));
                list($result,$errorMsg)= GoodsSkuStockService::deliveryOutAndLog($companyId,$count,$scheduleId,$scheduleModel['sku_id'],$scheduleModel['goods_id'],$operatorId,$operatorName,RoleEnum::ROLE_AGENT);
                BExceptionAssert::assertTrue($result,BBusinessException::create($errorMsg));
                $resultMsg[] = "排期{$scheduleId}:已发货{$count}件";
                $transaction->commit();
            }
            catch (\Exception $e){
                $flag = false;
                $resultMsg[] = "排期{$scheduleId}发货失败:".$e->getMessage();
                $transaction->rollBack();
            }
        }
        return [$flag,implode(PHP_EOL,$resultMsg)];
    }

    public static function getOrderDeliveryByExpectArriveTime($ownerType,$expectArriveTime,$companyId,$lessGoodsNum,$lessGoodsAmount,$orderStatus,$deliveryId){
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),RedirectParams::create("时间格式错误：{$expectArriveTime}",['order/index']));
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $conditions = ['and'];
        if (StringUtils::isNotBlank($deliveryId)){
            $conditions[] = ["{$orderTable}.delivery_id"=>$deliveryId];
        }
        if (StringUtils::isNotBlank($orderStatus)){
            $conditions[] = ["{$orderTable}.order_status"=>$orderStatus];
        }
        else{
            $conditions[] = ["{$orderTable}.order_status"=>Order::$activeStatusArr];
        }
        if (StringUtils::isNotBlank($ownerType)){
            $conditions[] = ["{$orderTable}.order_owner"=>$ownerType];
        }
        /*else{
            $conditions[] = ["{$orderTable}.order_owner"=>[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA]];
        }*/

        $conditions[] = [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderGoodsTable}.delivery_status"=>OrderGoods::DELIVERY_STATUS_PREPARE,
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];

        $havingConditions = ['and'];
        if ($lessGoodsNum){
            $havingConditions[]=['<','goods_num_count',$lessGoodsNum];
        }
        if ($lessGoodsAmount){
            $havingConditions[]=['<','goods_amount_count',$lessGoodsAmount];
        }
        $query  = (new Query())->from($orderGoodsTable)->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->select([
            "SUM({$orderGoodsTable}.num) as goods_num_count",
            "SUM({$orderGoodsTable}.amount) as goods_amount_count",
            "{$orderTable}.*",
        ])->where($conditions)->groupBy(['order_no']
        )->orderBy('id desc');
        if (count($havingConditions)>1){
            $query->having($havingConditions);
        }
        return $query;
    }




    public static function getDeliveryGoodsList($ownerType,$expectArriveTime,$companyId,$deliveryId){
        BExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),RedirectParams::create("时间格式错误：{$expectArriveTime}",['order/index']));
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $deliveryTable = Delivery::tableName();
        $conditions= [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$activeStatusArr,
            "{$orderGoodsTable}.delivery_status"=>[OrderGoods::DELIVERY_STATUS_PREPARE],
            "{$orderGoodsTable}.expect_arrive_time"=>$expectArriveTime,
        ];
        if (StringUtils::isNotBlank($ownerType)){
            $conditions["{$orderTable}.order_owner"] = $ownerType;
        }
        if (NumberUtils::notNullAndPositiveInteger($deliveryId)){
            $conditions["{$orderTable}.delivery_id"] = $deliveryId;
        }

        $query = (new Query())->from($orderGoodsTable)
            ->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->leftJoin($deliveryTable,"{$deliveryTable}.id={$orderTable}.delivery_id")
            ->select([
                "{$orderTable}.order_owner",
                "SUM({$orderGoodsTable}.num) as goods_num_count",
                "SUM({$orderGoodsTable}.amount) as goods_amount_count",
                "{$deliveryTable}.*",
            ])->where($conditions)->groupBy(["{$orderGoodsTable}.delivery_id,{$orderTable}.order_owner"]
            )->orderBy("goods_num_count asc,goods_amount_count asc");
        return $query;
    }

}