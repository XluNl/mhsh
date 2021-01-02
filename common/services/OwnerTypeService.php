<?php


namespace common\services;


use common\models\GoodsConstantEnum;

class OwnerTypeService
{

    public static function getOptionsByOwnerType($ownerType, $companyId=null){
        if ($ownerType==GoodsConstantEnum::OWNER_SELF){
            $result = [];
            return [GoodsConstantEnum::OWNER_SELF_ID=>'代理商自营'];
        }
        else if ($ownerType==GoodsConstantEnum::OWNER_HA){
            $allianceModels = AllianceService::getAllActiveModel(null,$companyId);
            $result = [];
            if (!empty($allianceModels)){
                foreach ($allianceModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['realname']}-{$v['phone']}";
                }
            }
            return $result;
        }
        else if ($ownerType==GoodsConstantEnum::OWNER_DELIVERY){
            $deliveryModels = DeliveryService::getAllActiveModel(null,$companyId);
            $result = [];
            if (!empty($deliveryModels)){
                foreach ($deliveryModels as $v){
                    $result[$v['id']] = "{$v['id']}-{$v['nickname']}-{$v['phone']}";
                }
            }
            return $result;
        }
        else{
            return [];
        }
    }

}