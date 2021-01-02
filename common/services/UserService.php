<?php


namespace common\services;


use common\models\User;
use common\utils\ArrayUtils;
use common\utils\StringUtils;

class UserService
{


    public static function getMulCustomerOfficialOpenId($customerIds){
        $customers = CustomerService::getAllModel($customerIds);
        if (empty($customers)){
            return [];
        }
        //customerId =>user_id
        $customersMaps = ArrayUtils::map($customers,'id','user_id');
        //customerId =>user_id => unionid
        $userIds  = array_values($customersMaps);
        $customerUser = User::find()->where(['user_info_id'=>$userIds,'user_type'=>User::USER_TYPE_CUSTOMER])->asArray()->all();
        $customerUser = ArrayUtils::map($customerUser,'user_info_id','unionid');
        foreach ($customersMaps as $k=>$v){
            if (key_exists($v,$customerUser)){
                $customersMaps[$k] = $customerUser[$v];
            }
            else{
                unset($customersMaps[$k]);
            }
        }
        if (empty($customersMaps)){
            return [];
        }

        //customerId  =>user_id => unionid => openid
        $unionIds = array_values($customersMaps);
        $officialUser = User::find()->where(['unionid'=>$unionIds,'user_type'=>User::USER_TYPE_OFFICIAL])->asArray()->all();
        $officialUser = ArrayUtils::map($officialUser,'unionid','openid');
        foreach ($customersMaps as $k=>$v){
            if (key_exists($v,$officialUser)){
                $customersMaps[$k] = $officialUser[$v];
            }
            else{
                unset($customersMaps[$k]);
            }
        }
        return $customersMaps;
    }


    public static function getAllianceOfficialOpenId($allianceId){
        $alliance = AllianceService::getModel($allianceId);
        if (empty($alliance)){
            return null;
        }
        $allianceUser = User::find()->where(['user_info_id'=>$alliance['user_id'],'user_type'=>User::USER_TYPE_ALLIANCE])->asArray()->one();
        if (empty($allianceUser)||StringUtils::isBlank($allianceUser['unionid'])){
            return null;
        }
        return self::getUserByUnionIdAndType($allianceUser['unionid'],User::USER_TYPE_OFFICIAL);
    }

    public static function getDeliveryOfficialOpenId($deliveryId){
        $delivery = DeliveryService::getModel($deliveryId);
        if (empty($delivery)){
            return null;
        }
        $deliveryUser = User::find()->where(['user_info_id'=>$delivery['user_id'],'user_type'=>User::USER_TYPE_BUSINESS])->asArray()->one();
        if (empty($deliveryUser)||StringUtils::isBlank($deliveryUser['unionid'])){
            return null;
        }
        return self::getUserByUnionIdAndType($deliveryUser['unionid'],User::USER_TYPE_OFFICIAL);
    }

    public static function getUserByUnionIdAndType($unionid,$userType){
        $user = User::find()->where(['unionid'=>$unionid,'user_type'=>$userType])->asArray()->one();
        if (empty($user)){
            return null;
        }
        return $user['openid'];
    }
}