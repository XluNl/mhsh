<?php

namespace backend\services;
use backend\utils\BExceptionAssert;
use common\models\CommonStatus;
use common\models\DeliveryType;
use common\utils\DateTimeUtils;

class DeliveryTypeService extends \common\services\DeliveryTypeService
{

    /**
     * 操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($id, $commander, $company_id, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CommonStatus::STATUS_DISABLED,CommonStatus::STATUS_DELETED,CommonStatus::STATUS_ACTIVE]),$validateException);
        if (in_array($commander,[CommonStatus::STATUS_DISABLED,CommonStatus::STATUS_ACTIVE])){
            $count = DeliveryType::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id,'company_id'=>$company_id]);
            BExceptionAssert::assertTrue($count>0,$validateException);
        }
        else{
            $count = DeliveryType::deleteAll(['id'=>$id,'company_id'=>$company_id]);
            BExceptionAssert::assertTrue($count>0,$validateException);
        }

    }


    /**
     * 获取并校验
     * @param $id
     * @param $delivery_id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|\common\models\DeliveryType|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$delivery_id,$company_id,$validateException,$model = false){
        $model = parent::getActiveModel($id,$delivery_id,$company_id,true);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }
}