<?php


namespace frontend\services;


use common\models\GoodsConstantEnum;

class LocationService  extends  \common\services\LocationService
{

    public static function toDeliveryDistance($delivery,&$skuList){
        if (empty($delivery)){
            return;
        }
        self::toLatLngDistance($delivery['lat'],$delivery['lng'],$skuList);
    }

    public static function toLatLngDistance($srcLat,$srcLng,&$skuList){
        if (!empty($skuList)){
            foreach ($skuList as $k=>$v){
                if ($v['goods_owner']==GoodsConstantEnum::OWNER_HA&&key_exists('alliance',$v)){
                    $v['distance'] = parent::getDistance($srcLng,$srcLat,$v['alliance']['lng'],$v['alliance']['lat']);
                }
                else{
                    $v['distance'] = 0;

                }
                $v['distance_text'] = parent::resolveDistance($v['distance']);
                $skuList[$k] = $v;
            }
        }
    }

}