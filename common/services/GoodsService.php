<?php


namespace common\services;


use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsService
{
    /**
     * 获取商品
     * @param $goodsId
     * @param $company_id
     * @param $goodsOwnerType
     * @param $goodsOwnerId
     * @param bool $model
     * @return array|bool|Goods|\yii\db\ActiveRecord|null
     */
    public static function getActiveOwnerGoods($goodsId, $company_id,$goodsOwnerType, $goodsOwnerId=null, $model = false){
        $conditions = ['id' => $goodsId, 'goods_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if (StringUtils::isNotBlank($goodsOwnerType)){
            $conditions['goods_owner'] = $goodsOwnerType;
        }
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['goods_owner_id'] = $goodsOwnerId;
        }
        if ($model){
            return Goods::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Goods::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getActiveOwnerGoodsList($company_id, $goodsOwnerId=null){
        $conditions = ['goods_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['goods_owner_id'] = $goodsOwnerId;
        }
        $result = (new Query())->from(Goods::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 批量获取
     * @param $goodsId
     * @param $company_id
     * @param null $goodsOwnerId
     * @return array|null
     */
    public static function getActiveOwnerGoodsArray($goodsId, $company_id, $goodsOwnerId=null){
        $conditions = ['id' => $goodsId, 'goods_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['goods_owner_id'] = $goodsOwnerId;
        }
        $result = (new Query())->from(Goods::tableName())->where($conditions)->all();
        return $result===false?null:$result;
    }

}