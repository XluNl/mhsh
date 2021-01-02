<?php


namespace common\services;


use common\models\StarExchangeLog;
use yii\db\Query;

class StarExchangeLogService
{

    /**
     * @param $tradeNo
     * @return array|bool|null
     */
    public static function getModelByTradeNo($tradeNo){
        $result = (new Query())->from(StarExchangeLog::tableName())->where(['trade_no'=>$tradeNo])->one();
        return $result===false?null:$result;
    }

}