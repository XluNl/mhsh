<?php


namespace frontend\services;


class UserService extends \common\services\UserService
{
    public static function getPopularizer($userId,$companyId){
        $popularizerModels = PopularizerService::getActiveModelByUserId($userId,$companyId);
        if (empty($popularizerModels)){
            return null;
        }
        else{
            return $popularizerModels[0];
        }
    }

    public static function getDeliveriesByCompanyId($userId,$companyId){
        return DeliveryService::getActiveModelByUserIdAndCompanyId($userId,$companyId);
    }

    public static function getDeliveries($userId){
        return DeliveryService::getActiveModelByUserId($userId);
    }
}