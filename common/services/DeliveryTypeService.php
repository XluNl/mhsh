<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\Delivery;
use common\models\DeliveryType;
use common\utils\StringUtils;
use yii\db\Query;

class DeliveryTypeService
{
    /**
     * 获取model
     * @param $id
     * @param null $deliveryId
     * @param null $company_id
     * @param bool $model
     * @return array|bool|DeliveryType|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id,$deliveryId=null, $company_id=null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($deliveryId)){
            $conditions['delivery_id'] = $deliveryId;
        }
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return DeliveryType::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(DeliveryType::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据deliveryId获取配送方案
     * @param $deliveryId
     * @return array
     */
    public static function getActiveModelByDelivery($deliveryId){
        $conditions = ['delivery_id' => $deliveryId,'status'=>CommonStatus::STATUS_ACTIVE];
        $result = (new Query())->from(DeliveryType::tableName())->where($conditions)->all();
        return $result;
    }

}