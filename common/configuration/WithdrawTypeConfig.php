<?php


namespace common\configuration;


use common\models\WithdrawApply;

class WithdrawTypeConfig
{
    /**
     * 获取默认开放的提现方式
     * @return array
     */
    public static function getDefaultConfig(){
        $withdrawType = [
            \Yii::$app->params['withdraw'][WithdrawApply::TYPE_OFFLINE],
            \Yii::$app->params['withdraw'][WithdrawApply::TYPE_WECHAT],
        ];
        return $withdrawType;
    }
}