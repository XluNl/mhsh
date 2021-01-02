<?php


namespace backend\services;


use common\models\Tag;

class TagService extends \common\services\TagService
{

    public static function getOptionsByGroupId($groupId){
        return Tag::getGroupTagArr($groupId);
    }

    /**
     * 返回平台提成比例
     * @param $companyId
     * @param $deliveryId
     * @return array|bool|null
     */
    public static function getPlatformRoyaltyTag($companyId, $deliveryId){
        $tag = self::getCommonTag($companyId,Tag::GROUP_DELIVERY,Tag::TAG_DELIVERY_PLATFORM_ROYALTY,$deliveryId);
        return $tag;
    }

    public static function setPlatformRoyaltyTag($companyId, $deliveryId, $deliveryName, $tagValue, $id=null){
        return self::setAddOrUpdate($id,$companyId,Tag::GROUP_DELIVERY,Tag::TAG_DELIVERY_PLATFORM_ROYALTY,$deliveryId,$deliveryName,$tagValue);
    }
}