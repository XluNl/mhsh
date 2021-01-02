<?php


namespace common\services;


use common\models\GoodsDetail;
use yii\db\Query;

class GoodsDetailService
{
    public static function getById($goodsId,$company_id,$model = false){
        $conditions = ['goods_id'=>$goodsId,'company_id'=>$company_id];
        if ($model){
            $goodsDetail =  GoodsDetail::findOne($conditions);
        }
        else{
            $goodsDetail = (new Query())->from(GoodsDetail::tableName())->where($conditions)->one();
            $goodsDetail = $goodsDetail===false?null:$goodsDetail;
        }
        return $goodsDetail;
    }


    public static function getByIds($goodsIds,$company_id){
        $conditions = ['goods_id'=>$goodsIds,'company_id'=>$company_id];
        $goodsDetails = (new Query())->from(GoodsDetail::tableName())->where($conditions)->all();
        $goodsDetails = $goodsDetails===false?null:$goodsDetails;
        return $goodsDetails;
    }
}