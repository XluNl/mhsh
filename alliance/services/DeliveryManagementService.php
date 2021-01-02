<?php


namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\RoleEnum;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;

class DeliveryManagementService  extends \common\services\DeliveryManagementService
{
    /**
     * 获取预计送达数据
     * @param $expectArriveTime
     * @param $orderTimeStart
     * @param $orderTimeEnd
     * @param $companyId
     * @param $allianceId
     * @return array
     */
    public static function getDeliveryDataByExpectArriveTimeA($expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, $allianceId){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        if (StringUtils::isNotBlank($orderTimeStart)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeStart),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单开始时间格式错误"));
        }
        if (StringUtils::isNotBlank($orderTimeEnd)){
            ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($orderTimeEnd),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"订单截止时间格式错误"));
        }
        return parent::getDeliveryDataByExpectArriveTime(GoodsConstantEnum::OWNER_HA,$expectArriveTime, $orderTimeStart, $orderTimeEnd, $companyId, $allianceId)->all();
    }


    /**
     * 发货管理时修改预计送达时间
     * @param $scheduleId
     * @param $expectArriveTime
     * @param $companyId
     * @param $allianceId
     */
    public static function modifyExpectArriveTime($scheduleId, $expectArriveTime, $companyId, $allianceId){
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormat($expectArriveTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"预计送达时间格式错误"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $scheduleModel = GoodsScheduleService::getActiveGoodsSchedule($scheduleId,$companyId);
            ExceptionAssert::assertNotNull($scheduleModel,BusinessException::create("排期{$scheduleId}不存在"));
            ExceptionAssert::assertTrue($scheduleModel['owner_type']==GoodsConstantEnum::OWNER_HA&&$scheduleModel['owner_id']==$allianceId,BusinessException::create("排期无权操作"));
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
     * @param $allianceId
     * @param $operatorId
     * @param $operatorName
     */
    public static function deliveryOut($scheduleIds, $orderTimeStart, $orderTimeEnd, $companyId, $allianceId, $operatorId, $operatorName){
        $flag = true;
        $resultMsg = [];
        $scheduleModels = GoodsScheduleService::getActiveGoodsScheduleWithGoodsAndSkuA($scheduleIds,$companyId,null);
        $scheduleModels = ArrayUtils::index($scheduleModels,'schedule_id');
        foreach ($scheduleIds as $scheduleId){
            $transaction = Yii::$app->db->beginTransaction();
            try{
                ExceptionAssert::assertTrue(key_exists($scheduleId,$scheduleModels),BusinessException::create("排期不存在"));
                $scheduleModel = $scheduleModels[$scheduleId];
                $count = ScheduleOutStockBatchService::deliveryOutA($scheduleId,$orderTimeStart,$orderTimeEnd,$companyId,$operatorId,$operatorName,$allianceId);
                list($result,$errorMsg)= GoodsSkuStockService::deliveryOutAndLog($companyId,$count,$scheduleId,$scheduleModel['sku_id'],$scheduleModel['goods_id'],$operatorId,$operatorName,RoleEnum::ROLE_HA);
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
}