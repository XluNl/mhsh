<?php


namespace common\services;


use common\models\Route;
use common\models\RouteDelivery;
use common\utils\StringUtils;
use yii\db\Query;

class RouteService
{
    /**
     * 获取
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Route|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $company_id=null, $model = false){
        $conditions = ['id' => $id,'status'=>[Route::STATUS_ACTIVE,Route::STATUS_DISABLED]];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Route::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Route::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 批量获取
     * @param $ids
     * @param null $company_id
     * @param bool $model
     * @return array|\common\models\Delivery[]|Route[]|\yii\db\ActiveRecord[]|null
     */
    public static function getActiveModels($ids, $company_id=null, $model = false){
        $conditions = ['id' => $ids,'status'=>[Route::STATUS_ACTIVE,Route::STATUS_DISABLED]];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Route::find()->where($conditions)->all();
        }
        else{
            $result = (new Query())->from(Route::tableName())->where($conditions)->all();
            return $result===false?null:$result;
        }
    }


    /**
     * 获取
     * @param $company_id
     * @return array
     */
    public static function getActiveByCompanyId($company_id){
        $conditions = ['status'=>[Route::STATUS_ACTIVE,Route::STATUS_DISABLED]];
        $conditions['company_id'] = $company_id;
        $result = (new Query())->from(Route::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 获取配送点和路线之间的绑定关系
     * @param $deliveryId
     * @param null $company_id
     * @param bool $model
     * @return array|bool|RouteDelivery|\yii\db\ActiveRecord|null
     */
    public static function getRouteDeliveryModel($deliveryId,$company_id = null, $model = false){
        $conditions = ['delivery_id' => $deliveryId];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return RouteDelivery::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(RouteDelivery::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据配送点获取配送路线
     * @param $deliveryId
     * @param $companyId
     * @return array|bool|Route|\yii\db\ActiveRecord|null
     */
    public static function getRouteInfoByDeliveryId($deliveryId, $companyId = null){
        $routeDelivery = self::getRouteDeliveryModel($deliveryId,$companyId);
        if (empty($routeDelivery)){
            return null;
        }
        return self::getActiveModel($routeDelivery['route_id']);
    }

}