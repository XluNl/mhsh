<?php


namespace common\utils;


class ModelUtils
{

    /**
     * 从模型中取指定列
     * @param $models array
     * @param $key
     * @return array
     */
    public static function getColFromModels($models,$key){
        if (empty($models)){
            return [];
        }
        $cols = [];
        foreach ($models as $model){
            if (key_exists($key,$model->attributes)){
                $cols[] = $model->$key;
            }
        }
        return $cols;
    }


    /**
     * 如果不为空则赋值
     * @param $model
     * @param $srcValue
     * @param $destKey
     */
    public static function setIfNotExist(&$model,$srcValue,$destKey){
        if (!StringUtils::isBlank($srcValue)){
            $model->$destKey = $srcValue;
        }
    }
}