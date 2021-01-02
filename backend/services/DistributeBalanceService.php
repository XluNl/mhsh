<?php


namespace backend\services;


use common\models\DistributeBalanceItem;

class DistributeBalanceService extends  \common\services\DistributeBalanceService
{
    public static function claim($companyId, $bizType, $bizId, $type, $num, $operatorId, $operatorName, $remark){
        if (!in_array($type,array_keys(DistributeBalanceItem::$claimTypeArr))){
            return [false,'不支持的赔款方式'];
        }
        return DistributeBalanceService::createOutItem(
            $bizType,
            $bizId,
            $companyId,
            null,
            null,
            $num,
            null,
            $operatorId,
            $operatorName,
            $type,
            $remark,
            0);
    }
}