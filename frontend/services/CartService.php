<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 1:55
 */

namespace frontend\services;

use common\models\Cart;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CartService
{
    /**
     * 拼装购物车商品详情
     * @param $userId
     * @param $company_id
     * @param $delivery_id
     * @param $goods_list
     * @return array
     */
    public static function getCartGoodsDetail($userId,$company_id,$delivery_id,$goods_list){
        $price_total = 0;
        $goods_total = 0;
        if (!empty($goods_list)){
            $scheduleIds = array_keys($goods_list);
            $skuModels = GoodsScheduleService::getDisplayUpByIds(null,$company_id,$scheduleIds,null,$delivery_id);
            if (!empty($skuModels)){
                $skuModels = ArrayHelper::index($skuModels,'sku_id');
            }
            else{
                $skuModels = [];
            }
            foreach ($goods_list as $key => $value) {
                if (ArrayHelper::keyExists($key,$skuModels)){
                    $model = $skuModels[$key];
                    $price_total += $model['price'] * $value;
                    $goods_total += $value;
                    $goods = $model;
                    $goods["num"] = $value;
                    $goods_list[$key] = $goods;
                }
                else{
                    unset($goods_list[$key]);
                    CartOperationService::modifyGoods($userId,$key,0);
                }
            }
            $goods_list = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($goods_list);
        }
        $cart = ['goods_list' => array_values($goods_list),'price_total' => $price_total,'goods_total'=>$goods_total];
        return $cart;
    }

    /**
     * 查找购物车信息
     * @param $userId
     * @param null $scheduleIds
     * @return array
     */
    public static function getCartByUserId($userId,$scheduleIds=null){
        $conditions= ['user_id'=>$userId];
        if (!empty($scheduleIds)){
            $conditions['schedule_id'] = $scheduleIds;
        }
        $cartInfos = (new Query())->from(Cart::tableName())->where($conditions)->all();
        return empty($cartInfos)?[]:$cartInfos;
    }


}