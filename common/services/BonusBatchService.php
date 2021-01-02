<?php


namespace common\services;



use common\models\BizTypeEnum;
use common\models\BonusBatch;
use common\models\BonusBatchDrawLog;
use common\models\CouponBatch;
use common\models\CustomerBalanceItem;
use common\models\DistributeBalanceItem;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;

class BonusBatchService
{

    /**
     * 根据ID获取数据
     * @param $id
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function getActiveModel($id, $model = false){
        return self::getDisplayModel($id,BonusBatch::STATUS_ACTIVE,$model);
    }

    /**
     * 根据ID获取数据
     * @param $id
     * @param $statusArr
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function getDisplayModel($id, $statusArr, $model = false){
        $conditions = ['id' => $id, 'status' =>$statusArr];
        if ($model){
            return BonusBatch::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(BonusBatch::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据batchNo查找
     * @param $batchNo
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function getActiveByBatchNo($batchNo, $model = false){
        return self::getDisplayModelByBatchNo($batchNo,BonusBatch::STATUS_ACTIVE);
    }

    /**
     * 根据batchNos查找
     * @param $batchNos
     * @return array
     */
    public static function getActiveModelByBatchNos($batchNos){
        $conditions = ['batch_no' => $batchNos,'status'=>BonusBatch::STATUS_ACTIVE];
        $result = (new Query())->from(BonusBatch::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 根据batchNo查找
     * @param $batchNo
     * @param $statusArr
     * @param bool $model
     * @return array|bool|BonusBatch|null
     */
    public static function getDisplayModelByBatchNo($batchNo, $statusArr, $model = false){
        $conditions = ['batch_no' => $batchNo];
        if ($statusArr!==null){
            $conditions['status'] = $statusArr;
        }
        if ($model){
            return BonusBatch::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(BonusBatch::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function drawBonus($company_id, $bizType, $bizId, $bonusDrawType, $bonusDrawTypeId, $walletDrawType, $walletDrawTypeId, $batchNo, $num, $operatorId, $operatorName, $operatorType, $remark=''){
        $bonusBatchModel = self::getDisplayModelByBatchNo($batchNo,BonusBatch::STATUS_ACTIVE,$company_id);
        if (empty($bonusBatchModel)){
            return [false,'批次不存在'];
        }
    /*    $distributeBalanceModel = DistributeBalanceService::getModelByBiz($bizId,$bizType);
        if (empty($distributeBalanceModel)){
            return [false,'客户不存在'];
        }*/
        /* 校验领取条件*/
        list($validateDrawLimitResult,$validateDrawLimitError) = self::validateDrawLimit($bonusBatchModel);
        if ($validateDrawLimitResult==false){
            return [$validateDrawLimitResult,$validateDrawLimitError];
        }

        list($reduceStockResult,$reduceStockError) = self::reduceStock($bonusBatchModel['id'],$num,$bonusBatchModel['version']);
        if (!$reduceStockResult){
            return [false,$reduceStockError];
        }

        list($addDrawLogResult,$addDrawLogError) = self::addBonusBatchDrawLog($bonusBatchModel,$num,$bizType,$bizId,$bonusDrawType, $bonusDrawTypeId,$operatorId,$operatorName,$operatorType,$remark);
        if (!$addDrawLogResult){
            return [false,$addDrawLogError];
        }
        list($addBonusResult,$addBonusError) = self::addBonus($bonusBatchModel['id'],$bizType,$bizId,$walletDrawType, $walletDrawTypeId,$num,$operatorId,$operatorName,$remark);
        if (!$addBonusResult){
            return [false,$addBonusError];
        }
        return [true,$bonusBatchModel['id']];
    }


    /**
     * 校验领取条件
     * @param $bonusBatchModel BonusBatch
     * @return array
     */
    private static function validateDrawLimit($bonusBatchModel){
        $nowTime = time();
        if (!DateTimeUtils::isBetween($nowTime,$bonusBatchModel['draw_start_time'],$bonusBatchModel['draw_end_time'])){
            return  [false,'未在领取期间'];
        }
        return [true,''];
    }



    /**
     * 判断是否可以再领取
     * @param $couponBatchModel CouponBatch
     * @param $num
     * @return array
     */
    private static function validateCouponBatchStockLimit($couponBatchModel,$num){
        if ($couponBatchModel['draw_amount']+$num>$couponBatchModel['amount']){
            return [false,'超过最大可领张数'];
        }
        return [true,''];
    }


    private static function reduceStock($batchId,$num,$version){
        $skuUpdateCount = BonusBatch::updateAllCounters(['draw_amount'=>$num,'version'=>1],['and',['id'=>$batchId,'version'=>$version],"amount-draw_amount>={$num}"]);
        if ($skuUpdateCount>0){
            return [true,'更新成功'];
        }
        else{
            return [false,'库存扣减失败'];
        }
    }

    /**
     * 增加日志
     * @param $bonusBatchModel
     * @param $num
     * @param $bizType
     * @param $bizId
     * @param $drawType
     * @param $drawTypeId
     * @param $operatorId
     * @param $operatorName
     * @param $operatorType
     * @param $remark
     * @return array
     */
    public static function addBonusBatchDrawLog($bonusBatchModel, $num,$bizType, $bizId,$drawType, $drawTypeId, $operatorId, $operatorName, $operatorType, $remark){
        $log = new BonusBatchDrawLog();
        $log->batch_id = $bonusBatchModel['id'];
        $log->num = $num;
        $log->biz_type = $bizType;
        $log->biz_id = $bizId;
        $log->draw_type = $drawType;
        $log->draw_type_id =$drawTypeId;
        $log->operator_id = $operatorId;
        $log->operator_name = $operatorName;
        $log->operator_type = $operatorType;
        $log->remark = $remark;
        if ($log->save()){
            return [true,'添加日志成功'];
        }
        else{
            return [false,'添加日志失败'];
        }
    }

    public static function addBonus($batchId, $bizType, $bizId,$drawType, $drawTypeId,$num, $operatorId, $operatorName, $remark){
        if ($bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_WALLET){
            list($success,$msg,$balanceId) = CustomerBalanceService::adjustBalanceCommon(
                CustomerBalanceItem::BIZ_TYPE_ADD_BONUS,
                $batchId,
                $bizId,
                $num,
                $remark,
                $operatorId,
                $operatorName
            );
            return [$success,$msg];
        }
        else{
            return self::addBonusForDistributeBalance($bizType, $bizId, $batchId, $num, $operatorId, $operatorName, $drawType, $remark, $drawTypeId);
        }
    }

    /**
     * @param $bizType
     * @param $bizId
     * @param $batchId
     * @param $num
     * @param $operatorId
     * @param $operatorName
     * @param $drawType
     * @param $remark
     * @param $drawTypeId
     * @return array
     */
    private static function addBonusForDistributeBalance($bizType, $bizId, $batchId, $num, $operatorId, $operatorName, $drawType, $remark, $drawTypeId): array
    {
        $company_id = Yii::$app->params['option.init.companyId'];
        if ($bizType == BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE) {
            $company_id = Yii::$app->params['option.init.companyId'];
        } else if ($bizType == BizTypeEnum::BIZ_TYPE_POPULARIZER) {
            $popularizerModel = PopularizerService::getActiveModel($bizId);
            if (empty($popularizerModel)) {
                return [false, '分享者无法找到'];
            }
            $company_id = $popularizerModel['company_id'];
        } else if ($bizType == BizTypeEnum::BIZ_TYPE_DELIVERY) {
            $deliveryModel = DeliveryService::getActiveModel($bizId);
            if (empty($deliveryModel)) {
                return [false, '配送团长无法找到'];
            }
            $company_id = $deliveryModel['company_id'];
        } else if ($bizType == BizTypeEnum::BIZ_TYPE_PAYMENT_HANDLING_FEE) {
            $company_id = Yii::$app->params['option.init.companyId'];
        } else if ($bizType == BizTypeEnum::BIZ_TYPE_AGENT) {
            $company_id = $bizId;
        }
        return DistributeBalanceService::createItem($bizType,
            $bizId,
            $company_id,
            (string)$batchId,
            null,
            $num,
            null,
            $operatorId,
            $operatorName,
            $drawType,
            $remark,
            $drawTypeId);
    }


}