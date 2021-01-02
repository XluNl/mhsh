<?php


namespace common\services;


use business\services\GoodsDisplayDomainService;
use common\models\DeliveryComment;
use common\utils\ArrayUtils;

class DeliveryCommentVOService
{
    public static function batchDefineStatusVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::defineStatusVO($v);
            $list[$k] = $v;
        }
        return $list;
    }

    public static function defineStatusVO($entity){
        $entity['status_text'] = ArrayUtils::getArrayValue($entity['status'],DeliveryComment::$statusArr);
        $entity = GoodsDisplayDomainService::renameImageUrl($entity,'images');
        $entity['goods'] = GoodsDisplayDomainService::renameImageUrl($entity['goods'],'goods_img');
        $entity['goodsSku'] = GoodsDisplayDomainService::renameImageUrl($entity['goodsSku'],'sku_img');
        $entity['delivery'] = GoodsDisplayDomainService::renameImageUrl($entity['delivery'],'head_img_url');
        return $entity;
    }
}