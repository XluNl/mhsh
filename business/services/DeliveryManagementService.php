<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\RoleEnum;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;

class DeliveryManagementService  extends \common\services\DeliveryManagementService
{
    /**
     * 获取预计送达数据
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $deliveryId
     * @return array
     */
    public static function getDeliveryDataByExpectArriveTimeB($expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, $deliveryId){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        if (StringUtils::isNotBlank($orderTimeStart)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeStart),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单开始时间格式错误"));
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeEnd),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单截止时间格式错误"));
        }
        return parent::getDeliveryDataByExpectArriveTime(GoodsConstantEnum::OWNER_DELIVERY,$expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, $deliveryId)->all();
    }


    /**
     * 发货管理时修改预计送达时间
     * @param $scheduleId
     * @param $expectArriveTime
     * @param $companyId
     * @param $deliveryId
     */
    public static function modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId,$deliveryId){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $scheduleModel = GoodsScheduleService::getActiveGoodsSchedule($scheduleId,$companyId);
            ExceptionAssert::assertNotNull($scheduleModel,BusinessException::create("排期{$scheduleId}不存在"));
            ExceptionAssert::assertTrue($scheduleModel['owner_type']==GoodsConstantEnum::OWNER_DELIVERY&&$scheduleModel['owner_id']==$deliveryId,BusinessException::create("排期无权操作"));
            ExceptionAssert::assertTrue($scheduleModel['expect_arrive_time']!=$expectArriveTime,BusinessException::create("预计送达日期并未修改"));
            GoodsScheduleService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId);
            OrderService::modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId);
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,$e->getMessage()));
        }
    }


    /**
     * 批量发货
     * @param $scheduleIds
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $deliveryId
     * @param $operatorId
     * @param $operatorName
     */
    public static function deliveryOut($scheduleIds,$orderTimeStart,$orderTimeEnd,$companyId,$deliveryId,$operatorId,$operatorName){
        $flag = true;
        $resultMsg = [];
        $scheduleModels = GoodsScheduleService::getActiveGoodsScheduleWithGoodsAndSkuB($scheduleIds,$companyId,$deliveryId);
        $scheduleModels = ArrayUtils::index($scheduleModels,'schedule_id');
        foreach ($scheduleIds as $scheduleId){
            $transaction = Yii::$app->db->beginTransaction();
            try{
                ExceptionAssert::assertTrue(key_exists($scheduleId,$scheduleModels),BusinessException::create("排期不存在"));
                $scheduleModel = $scheduleModels[$scheduleId];
                $count = ScheduleOutStockBatchService::deliveryOutB($scheduleId,$orderTimeStart,$orderTimeEnd,$companyId,$operatorId,$operatorName,$deliveryId);
                list($result,$errorMsg)= GoodsSkuStockService::deliveryOutAndLog($companyId,$count,$scheduleId,$scheduleModel['sku_id'],$scheduleModel['goods_id'],$operatorId,$operatorName,RoleEnum::ROLE_DELIVERY);
                ExceptionAssert::assertTrue($result,BusinessException::create($errorMsg));
                $resultMsg[] = "排期{$scheduleId}:已发货{$count}件";
                $transaction->commit();
            }
            catch (\Exception $e){
                $flag = false;
                $resultMsg[] = "排期{$scheduleId}发货失败:".$e->getMessage();
                $transaction->rollBack();
            }
        }
        ExceptionAssert::assertTrue($flag,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,implode(PHP_EOL,$resultMsg)));
    }


    public static function getDeliveryReceiveDataByExpectArriveTime($expectArriveTime,$orderTimeStart,$orderTimeEnd,$companyId,$deliveryId,$ownerType){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $deliveryStatusPrepare = OrderGoods::DELIVERY_STATUS_PREPARE;
        $conditions = ['and'];
        if (StringUtils::isNotBlank($orderTimeStart)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeStart),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单开始时间格式错误"));
            $conditions[] = [">=","{$orderTable}.created_at",$orderTimeStart];
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeEnd),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单截止时间格式错误"));
            $conditions[] = ["<=","{$orderTable}.created_at",$orderTimeEnd];
        }
        if (StringUtils::isNotBlank($ownerType)){
            $conditions[] = ["{$orderTable}.order_owner"=>$ownerType];
        }
        $conditions[] = [
            "{$orderGoodsTable}.company_id"=>$companyId,
            "{$orderTable}.order_status"=>Order::$activeStatusArr,
            "{$orderTable}.delivery_id"=>$deliveryId,
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
            ])->where($conditions)->groupBy(['schedule_id']
            )->orderBy('schedule_id');
        return $query->all();
    }

}