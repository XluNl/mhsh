<?php


namespace frontend\services;


use common\models\Common;
use common\models\Delivery;
use common\models\GoodsConstantEnum;

class OuterService
{

    public static function getNearByGoods($lat,$lng){
        $cooperateModels = DeliveryService::getNearBy($lat,$lng,Delivery::TYPE_COOPERATE);
        if (empty($cooperateModels)){
            return [];
        }
        $firstModel = $cooperateModels[0];
        $todayDiscount = GoodsScheduleService::getDisplayUpToday(GoodsConstantEnum::OWNER_SELF,$firstModel['company_id'],GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,null,null,$firstModel['id'],null);
        $todayDiscount = self::assembleSku($todayDiscount);
        return $todayDiscount;
    }

    private static function assembleSku($skuData){
        if (empty($skuData)){
            return [];
        }
        $skuShowData = [];
        foreach ($skuData as $value){
            $item = [];
            $item['price'] = Common::showAmount($value['price']);
            $item['goods_name'] = $value['goods_name'];
            $item['sku_name'] = $value['sku_name'];
            $item['describe'] = $value['sku_describe'];
            $item['tag'] = '秒杀价';
            $skuShowData[] = $item;
        }
        return $skuShowData;
    }
}