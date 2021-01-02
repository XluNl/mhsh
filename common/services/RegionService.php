<?php


namespace common\services;


use common\models\Region;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class RegionService
{

    public static function getRegionById($pid)
    {
        $models = (new Query())->from(Region::tableName())->where(['parent_id'=>$pid])->select('id,name')->all();
        if (!empty($models)){
            return ArrayHelper::map($models,"id","name");
        }
        return [];
    }

    public static function getRegionByParentId($parentId=0){
        return (new Query())->from(Region::tableName())->where(['parent_id'=>$parentId])->all();
    }

    /**
     * 获取地址名称
     * @param $ids
     * @return array
     */
    public static function getListByIds($ids){
        return (new Query())->from(Region::tableName())->where(['id'=>$ids])->all();
    }

    /**
     * 批量设置地址文本
     * @param $models
     * @param $regions
     * @param $srcKey
     * @param $destKey
     */
    public static function batchSetRegionText(&$models, $regions, $srcKey, $destKey){
        if (empty($models)){
            return;
        }
        $regions = empty($regions)?[]:ArrayHelper::index($regions,'id');
        foreach ($models as $k=>$v){
            self::setRegionText($v,$regions,$srcKey,$destKey);
            $models[$k] = $v;
        }
    }

    /**
     * 单个设置地址文本
     * @param $model
     * @param $regions
     * @param $srcKey
     * @param $destKey
     */
    public static function setRegionText(&$model, $regions, $srcKey, $destKey){
        if (empty($model)){
            return;
        }
        $regions = empty($regions)?[]: ArrayHelper::index($regions,'id');
        if (key_exists($model[$srcKey],$regions)){
            $model[$destKey]= $regions[$model[$srcKey]]['name'];
        }
        else{
            $model[$destKey] = '';
        }
    }


    /**
     * 批量设置省市县的名称
     * @param $models
     */
    public static function batchSetProvinceAndCityAndCounty(&$models){
        if (empty($models)){
            return;
        }
        $regionIds = [];
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'province_id'));
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'city_id'));
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'county_id'));
        $regionIds = array_unique($regionIds);
        $regions = self::getListByIds($regionIds);
        self::batchSetRegionText($models,$regions,'province_id','province_text');
        self::batchSetRegionText($models,$regions,'city_id','city_text');
        self::batchSetRegionText($models,$regions,'county_id','county_text');
    }

    /**
     * 批量设置省市县的名称(For 订单)
     * @param $models
     */
    public static function batchSetProvinceAndCityAndCountyForOrder(&$models){
        if (empty($models)){
            return;
        }
        $regionIds = [];
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'accept_province_id'));
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'accept_city_id'));
        $regionIds = array_merge($regionIds,ArrayHelper::getColumn($models,'accept_county_id'));
        $regionIds = array_unique($regionIds);
        $regions = self::getListByIds($regionIds);
        self::batchSetRegionText($models,$regions,'accept_province_id','accept_province_text');
        self::batchSetRegionText($models,$regions,'accept_city_id','accept_city_text');
        self::batchSetRegionText($models,$regions,'accept_county_id','accept_county_text');
    }


    /**
     * 单个设置省市县的名称
     * @param $model
     */
    public static function setProvinceAndCityAndCounty(&$model){
        if (empty($model)){
            return;
        }
        $regionIds = [$model['province_id'],$model['city_id'],$model['county_id']];
        $regionIds = array_unique($regionIds);
        $regions = self::getListByIds($regionIds);
        self::setRegionText($model,$regions,'province_id','province_text');
        self::setRegionText($model,$regions,'city_id','city_text');
        self::setRegionText($model,$regions,'county_id','county_text');
    }

    /**
     * 单个设置省市县的名称FOR订单
     * @param $model
     */
    public static function setProvinceAndCityAndCountyForOrder(&$model){
        if (empty($model)){
            return;
        }
        $regionIds = [$model['accept_province_id'],$model['accept_city_id'],$model['accept_county_id']];
        $regionIds = array_unique($regionIds);
        $regions = self::getListByIds($regionIds);
        self::setRegionText($model,$regions,'accept_province_id','accept_province_text');
        self::setRegionText($model,$regions,'accept_city_id','accept_city_text');
        self::setRegionText($model,$regions,'accept_county_id','accept_county_text');
    }
}