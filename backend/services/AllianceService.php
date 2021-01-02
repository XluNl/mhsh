<?php

namespace backend\services;
use backend\utils\BExceptionAssert;
use common\models\Alliance;
use common\models\Delivery;
use common\utils\DateTimeUtils;
use yii\db\Query;

class AllianceService extends \common\services\AllianceService
{
    /**
     * @param $company_id
     * @return array
     */
    public static function getAllAlliance($company_id){
        $deliveryArray = (new Query())->from(Alliance::tableName())->where(['company_id'=>$company_id])->all();
        return $deliveryArray;
    }

    public static function generateOptions($models){
        if (empty($models)){
            return [];
        }
        $options = [];
        foreach ($models as $model){
            $options[$model['id']] = "{$model['nickname']}({$model['realname']}-{$model['phone']})";
        }
        return $options;
    }



    /**
     * 状态操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operateStatus($id, $commander, $company_id, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[Alliance::STATUS_ONLINE,Alliance::STATUS_PENDING,Alliance::STATUS_OFFLINE]),$validateException);
        $count = Alliance::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }


    /**
     * 获取并校验
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$company_id,$validateException,$model = false){
        $model = self::getActiveModel($id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }


}