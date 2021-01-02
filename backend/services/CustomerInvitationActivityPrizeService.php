<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\CommonStatus;
use common\models\CustomerInvitationActivityPrize;
use common\utils\DateTimeUtils;

class CustomerInvitationActivityPrizeService extends \common\services\CustomerInvitationActivityPrizeService
{
    /**
     * 获取可展示，非空校验
     * @param $id
     * @param $activity_id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|\common\models\CustomerInvitationActivityPrize|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id,$activity_id,$company_id,$validateException,$model = false){
        $model = self::getModel($id,$activity_id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }


    /**
     * 操作状态
     * @param $id
     * @param $commander
     * @param $activityId
     * @param $validateException RedirectParams
     */
    public static function operate($id, $commander, $activityId, $validateException){
        BExceptionAssert::assertTrue(key_exists($commander,CommonStatus::$StatusArr),$validateException);
        $count = CustomerInvitationActivityPrize::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'activity_id'=>$activityId]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 校验批次号
     * @param $prizeType
     * @param $batchNo
     * @param $companyId
     * @return bool
     */
    public static function validateBatchNo($prizeType,$batchNo,$companyId){
        if ($prizeType==CustomerInvitationActivityPrize::TYPE_COUPON){
            $couponBatch = CouponBatchService::getDisplayModelByBatchNo($batchNo,null,$companyId);
            if ($couponBatch===null){
                return false;
            }
        }
        else if ($prizeType==CustomerInvitationActivityPrize::TYPE_BONUS){
            $batchBatch = BonusBatchService::getDisplayModelByBatchNo($batchNo,null);
            if ($batchBatch===null){
                return false;
            }
        }
        return true;
    }


}