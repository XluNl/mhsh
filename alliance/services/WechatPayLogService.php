<?php


namespace alliance\services;
use common\models\WechatPayLog;

class WechatPayLogService extends \common\services\WechatPayLogService
{
    /**
     * 保证金支付记录
     * @param $allianceId
     * @return |null
     */
    public static function getAllianceAuthPayLog($allianceId){
        $res = parent::getByBiz(WechatPayLog::BIZ_TYPE_ALLIANCE_AUTH,$allianceId);
        if (empty($res)){
            return null;
        }
        return $res[0];
    }
}