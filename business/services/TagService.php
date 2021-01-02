<?php


namespace business\services;


use common\models\Tag;

class TagService extends \common\services\TagService
{

    /**
     * 返回平台提成比例
     * @param $companyId
     * @param $deliveryId
     * @return mixed
     */
    public static function getPlatformRoyaltyValue($companyId,$deliveryId){
        $v =  self::getCommonTagValue($companyId,Tag::GROUP_DELIVERY,Tag::TAG_DELIVERY_PLATFORM_ROYALTY,$deliveryId);
        return $v/100;
    }

}