<?php


namespace common\configuration;


use \Yii;

class DistributeSwitchUtil
{
    /**
     * 校验是否允许进行分润
     * @param $bizIdName
     * @return bool
     */
    public static function isDistributeOpen($bizIdName){
        return Yii::$app->params['distribute.switch'][$bizIdName];
    }
}