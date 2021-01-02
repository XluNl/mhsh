<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use yii\db\Query;

class GoodsSkuService extends \common\services\GoodsSkuService
{


    /**
     * 获取商品 ,非空校验
     * @param $skuId
     * @param $goodsId
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|GoodsSku|\yii\db\ActiveRecord|null
     */
    public static function requireActiveGoodsSku($skuId,$goodsId,$company_id,$validateException,$model = false){
        $model = self::getActiveGoodsSku($skuId,$goodsId,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }


    /**
     * 商品属性操作
     * @param $skuId
     * @param $goodsId
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($skuId,$goodsId,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN,GoodsConstantEnum::STATUS_DELETED]),$validateException);
        $count = GoodsSku::updateAll(['sku_status'=>$commander],['id'=>$skuId,'goods_id'=>$goodsId,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 根据goodsId获取
     * @param $goodsId
     * @param $company_id
     * @return array
     */
    public static function getSkuListByGoodsId($goodsId,$company_id){
        $conditions = [
            'goods_id' => $goodsId,
            'sku_status' => GoodsConstantEnum::$activeStatusArr,
            'company_id'=>$company_id
        ];
        return (new Query())->from(GoodsSku::tableName())->where($conditions)->all();
    }

    /**
     * 根据goodsId获取(列表专用)
     * @param $goodsId
     * @param $company_id
     * @return array
     */
    public static function getSkuListByGoodsIdOptions($goodsId,$company_id){
        $skuListArr = self::getSkuListByGoodsId($goodsId,$company_id);
        $skuListArr = ArrayUtils::map($skuListArr,'id','sku_name');
        return $skuListArr;
    }

    /**
     * 根据goodsId查询可投放的渠道
     * @param $goodsId
     * @param $company_id
     * @param $validateException
     * @return array
     */
    public static function getScheduleDisplayChannel($goodsId,$company_id,$validateException){
        $goodsModel = GoodsService::requireActiveGoods($goodsId,$company_id,$validateException);
        BExceptionAssert::assertTrue(key_exists($goodsModel['goods_owner'],GoodsConstantEnum::$ownerArr),$validateException);
        $displayChannelIds = GoodsConstantEnum::$scheduleDisplayChannelMap[$goodsModel['goods_owner']];
        $displayChannelArr = [];
        foreach ($displayChannelIds as $displayChannelId){
            $displayChannelArr[$displayChannelId] = GoodsConstantEnum::$scheduleDisplayChannelArr[$displayChannelId];
        }
        return $displayChannelArr;
    }

    /**
     * 根据名称查询
     * @param $goodsSkuName
     * @param $goodsId
     * @param $company_id
     * @param bool $model
     * @return array|bool|GoodsSku|\yii\db\ActiveRecord|null
     */
    public static function getByGoodsSkuName($goodsSkuName,$goodsId,$company_id,$model=false){
        $conditions = ['sku_name' => $goodsSkuName,'goods_id'=>$goodsId, 'sku_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if ($model){
            return GoodsSku::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GoodsSku::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $skuIds
     * @param $goodsIds
     * @param null $company_id
     * @return array
     */
    public static function getSkuInfo($skuIds,$goodsIds,$company_id=null){
        return parent::getSkuInfoCommon($skuIds,$goodsIds,$company_id);
    }


}