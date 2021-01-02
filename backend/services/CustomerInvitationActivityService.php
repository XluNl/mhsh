<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\CommonStatus;
use common\models\CustomerInvitationActivity;
use common\utils\DateTimeUtils;
use Yii;

class CustomerInvitationActivityService extends \common\services\CustomerInvitationActivityService
{
    /**
     * 获取可展示，非空校验
     * @param $id
     * @param $company_id
     * @param $validateException RedirectParams
     * @param bool $model
     * @return array|bool|\common\models\CustomerInvitationActivity|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id,$company_id,$validateException,$model = false){
        BExceptionAssert::assertNotBlank($id,$validateException);
        $model = self::getModel($id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 操作状态
     * @param $id
     * @param $commander
     * @param $companyId
     * @param $validateException RedirectParams
     */
    public static function operate($id,$commander,$companyId,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,CommonStatus::$StatusArr),$validateException);
        if ($commander==CommonStatus::STATUS_ACTIVE){
            CustomerInvitationActivity::updateAll(['status'=>CommonStatus::STATUS_DISABLED,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['status'=>CommonStatus::STATUS_ACTIVE,'company_id'=>$companyId]);
        }
        $count = CustomerInvitationActivity::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'company_id'=>$companyId]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 预统计活动奖品
     * @param $activityId
     * @param $companyId
     * @param $validateException RedirectParams
     * @return mixed
     */
    public static function preStatisticData($activityId, $companyId,$validateException)
    {
        list($res,$data) = parent::preStatistic($activityId,$companyId);
        BExceptionAssert::assertTrue($res,$validateException->updateMessage($data));
        return $data;
    }

    /**
     * 活动结算
     * @param $try
     * @param $activityId
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     * @param $validateException BBusinessException
     * @param string $remark
     * @return mixed
     */
    public static function settleActivityResult($try,$activityId, $companyId, $operatorId, $operatorName,$validateException, $remark = '')
    {
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($res,$data) = self::settleActivity($activityId, $companyId, $operatorId, $operatorName, $remark);
            if (!$res){
                BExceptionAssert::assertTrue($res,BBusinessException::create($data));
            }

            if ($try){
                $transaction->rollBack();
            }
            else{
                $transaction->commit();
            }
            return $data;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $validateException->updateMessage($e->getMessage());
        }
    }
}