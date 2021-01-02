<?php


namespace common\services;


use common\models\BusinessApply;
use common\models\Common;
use common\utils\ArrayUtils;

class BusinessApplyDisplayVOService
{
    /**
     * 设置申请状态
     * @param $model
     * @return mixed
     */
    public static function setVOText($model){
        if (empty($model)){
            return $model ;
        }
        if (key_exists('action',$model)){
            $model['action_text'] = ArrayUtils::getArrayValue($model['action'],BusinessApply::$actionArr,'');
        }
        return $model;
    }

    /**
     * 批量设置申请状态
     * @param $list
     * @return array
     */
    public static function batchSetVOText($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setVOText($v);
            $list[$k] = $v;
        }
        RegionService::batchSetProvinceAndCityAndCounty($list);
        return $list;
    }
}