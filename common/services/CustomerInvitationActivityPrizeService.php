<?php


namespace common\services;


use common\models\Common;
use common\models\CommonStatus;
use common\models\CustomerInvitationActivityPrize;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CustomerInvitationActivityPrizeService
{
    /**
     * 根据ID获取
     * @param $id
     * @param null $activity_id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|CustomerInvitationActivityPrize|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$activity_id=null,$company_id=null, $model = false){
        $conditions = ['id' => $id];
        if (StringUtils::isNotBlank($activity_id)){
            $conditions['activity_id'] = $activity_id;
        }
        if (StringUtils::isNotBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return CustomerInvitationActivityPrize::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CustomerInvitationActivityPrize::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取奖品列表
     * @param $activityId
     * @param $companyId
     * @return array
     */
    public static function getActiveModelsByActivityId($activityId,$companyId){
        $conditions = ['activity_id' => $activityId,'status'=>CommonStatus::STATUS_ACTIVE];
        $prizes = (new Query())->from(CustomerInvitationActivityPrize::tableName())->where($conditions)->orderBy('level_type')->all();
        if (empty($prizes)){
            return [];
        }
        $couponBatchNo = [];
        $bonusBatchNo = [];
        foreach ($prizes as $k=>$v){
            if ($v['type']==CustomerInvitationActivityPrize::TYPE_COUPON){
                $couponBatchNo[] = $v['batch_no'];
            }
            else if ($v['type']==CustomerInvitationActivityPrize::TYPE_BONUS){
                $bonusBatchNo[] = $v['batch_no'];
            }
        }
        $couponBatchModels = [];
        if (!empty($couponBatchNo)){
            $couponBatchModels = CouponBatchService::getActiveModelByBatchNos($couponBatchNo,$companyId);
            $couponBatchModels = empty($couponBatchModels)?[]:ArrayHelper::index($couponBatchModels,'batch_no');
        }

        $bonusBatchModels = [];
        if (!empty($bonusBatchNo)){
            $bonusBatchModels = BonusBatchService::getActiveModelByBatchNos($bonusBatchNo);
            $bonusBatchModels = empty($bonusBatchModels)?[]:ArrayHelper::index($bonusBatchModels,'batch_no');
        }
        foreach ($prizes as $k=>$v){
            if ($v['type']==CustomerInvitationActivityPrize::TYPE_COUPON){
                if (!key_exists($v['batch_no'],$couponBatchModels)){
                    unset($prizes[$k]);
                }
            }
            else if ($v['type']==CustomerInvitationActivityPrize::TYPE_BONUS){
                if (!key_exists($v['batch_no'],$bonusBatchModels)){
                    unset($prizes[$k]);
                }
            }
            else if ($v['type']==CustomerInvitationActivityPrize::TYPE_OTHER){

            }
            else{
                unset($prizes[$k]);
            }
        }
        $prizes = array_values($prizes);
        return $prizes;
    }

    /**
     * 生成数量文本
     * @param $type
     * @param $num
     * @return string
     */
    public static function exportNumText($type,$num){
        if ($type==CustomerInvitationActivityPrize::TYPE_COUPON){
            return $num.'个';
        }
        else if ($type==CustomerInvitationActivityPrize::TYPE_BONUS){
            return Common::showAmountWithYuan($num);
        }
        else if ($type==CustomerInvitationActivityPrize::TYPE_COUPON){
            return $num.'个';
        }
        return $num;
    }

    /**
     * 扣减奖品库存
     * @param $id
     * @param $activityId
     * @param $num
     * @return array
     */
    public static function reducePrize($id,$activityId,$num){
        $skuUpdateCount = CustomerInvitationActivityPrize::updateAllCounters(['real_quantity'=>$num],['and',['id'=>$id,'activity_id'=>$activityId],"expect_quantity-real_quantity>={$num}"]);
        if ($skuUpdateCount>0){
            return [true,'邀请活动奖品库存扣减成功'];
        }
        else{
            return [false,'邀请活动奖品库存扣减失败'];
        }
    }

    /**
     * 恢复奖品库存
     * @param $id
     * @param $activityId
     * @param $num
     * @return array
     */
    public static function recoveryPrize($id,$activityId,$num){
        $skuUpdateCount = CustomerInvitationActivityPrize::updateAllCounters(['real_quantity'=>-$num],['and',['id'=>$id,'activity_id'=>$activityId]]);
        if ($skuUpdateCount>0){
            return [true,'邀请活动奖品库存恢复成功'];
        }
        else{
            return [false,'邀请活动奖品库存恢复失败'];
        }
    }

}