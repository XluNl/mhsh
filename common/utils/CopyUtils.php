<?php


namespace common\utils;


class CopyUtils
{
    /**
     * 复制object
     * @param $src
     * @param $dist
     * @throws \ReflectionException
     */
    public static function copyFromArrayToObject($src,$dist){
        if (is_object($src)&&is_object($dist)){
            $class = new \ReflectionClass($src);
            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $k = $property->getName();
                    $v = $property->getValue();
                    if ($dist->hasAttribute($k)){
                        $dist->$k = $v;
                    }

                }
            }
        }
        else if (is_object($src)&&is_array($dist)){
            $class = new \ReflectionClass($src);
            foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $k = $property->getName();
                    $v = $property->getValue();
                    $dist[$k] = $v;
                }
            }
        }
        else if (is_array($src)&&is_array($dist)){
            foreach ($src as $k=>$v){
                $dist[$k] = $v;
            }
        }
        else if (is_array($src)&&is_object($dist)){
            foreach ($src as $k=>$v){
                if ($dist->hasAttribute($k)){
                    $dist->$k = $v;
                }
            }
        }
    }



    public static function batchCopyAttr(&$models,$src,$dist){
        if (empty($models)){
            return;
        }
        foreach ($models as $k=>$v){
            self::copyAttr($v,$src,$dist);
            $models[$k] = $v;
        }
    }

    public static function copyAttr(&$model,$src,$dist){
        $model[$dist] = $model[$src];
    }

}