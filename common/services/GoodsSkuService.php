<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace common\services;


use alliance\services\GoodsDetailService;
use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsSkuService
{
    /**
     * 获取商品
     * @param $skuId
     * @param $goodsId
     * @param $company_id
     * @param bool $model
     * @return array|bool|GoodsSku|\yii\db\ActiveRecord|null
     */
    public static function getActiveGoodsSku($skuId,$goodsId,$company_id,$model = false){
        $conditions = ['id' => $skuId, 'sku_status' =>GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if (!StringUtils::isBlank($goodsId)){
            $conditions['goods_id'] = $goodsId;
        }
        if ($model){
            return GoodsSku::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GoodsSku::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据IDs获取数据
     * @param $skuIds
     * @param null $companyId
     * @return array
     */
    public static function getGoodsSkuList($skuIds,$companyId=null){
        $conditions = ['id'=>$skuIds];
        if (StringUtils::isBlank($companyId)){
            $conditions['company_id']= $companyId;
        }
        $goodsSku = (new Query())->from(GoodsSku::tableName())->where($conditions)->all();
        return $goodsSku;
    }

    public static function getSkuInfoCommon($skuIds, $goodsIds, $company_id=null, $goodsOwner=null, $goodsOwnerId=null){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $select = "{$goodsTable}.*,{$skuTable}.*,{$skuTable}.id as sku_id";
        $onGoodsTableCondition = [
            "AND",
            "{$goodsTable}.id={$skuTable}.goods_id"
        ];
        if (!empty($skuIds)){
            $onGoodsTableCondition[] =  ["{$skuTable}.id"=>$skuIds];
        }
        if (!empty($goodsIds)){
            $onGoodsTableCondition[] =  ["{$skuTable}.goods_id"=>$goodsIds];
        }
        if (!empty($goodsOwner)){
            $onGoodsTableCondition[] =  [
                "{$goodsTable}.goods_owner"=>$goodsOwner,
            ];
        }
        if (!empty($goodsOwnerId)){
            $onGoodsTableCondition[] =  [
                "{$goodsTable}.goods_owner_id"=>$goodsOwnerId,
            ];
        }
        if (!empty($company_id)){
            $onGoodsTableCondition[] =  ["{$skuTable}.company_id"=>$company_id];
        }
        $goodsSkuQuery = (new Query())->select($select)->from($skuTable)->innerJoin($goodsTable,$onGoodsTableCondition);
        $goodsSku = $goodsSkuQuery->all();
        return $goodsSku;
    }


    public static function addStock($schedule_id,$skuId,$num){
        $skuUpdateCount = GoodsSku::updateAllCounters(['sku_sold'=>-$num],['id'=>$skuId]);
        if ($skuUpdateCount<1){
            return [false,"商品：{$skuId}恢复库存失败"];
        }
        $scheduleUpdateCount = GoodsSchedule::updateAllCounters(['schedule_sold'=>-$num],['id'=>$schedule_id]);
        if ($scheduleUpdateCount<1){
            return [false,"商品：{$skuId}恢复活动库存失败"];
        }
        return [true,''];
    }


    public static function completeDetail(&$goodsSkuList,$company_id){
        if (empty($goodsSkuList)){
            return;
        }
        $goodsIds = ArrayUtils::getColumnWithoutNull("goods_id",$goodsSkuList);
        $goodsDetails = GoodsDetailService::getByIds($goodsIds,$company_id);
        $goodsDetails = ArrayUtils::index($goodsDetails,'goods_id');
        foreach ($goodsSkuList as $k=>$v){
            if (key_exists($v['goods_id'],$goodsDetails)){
                $v['goods_detail_src'] = $goodsDetails[$v['goods_id']]['goods_detail'];
                $v['goods_detail_ab'] = self::extractGoodsDetailPictures($v['goods_detail_src']);
                $v['goods_detail_re'] = self::getRelativeGoodsDetailPictures($v['goods_detail_ab']);
                //unset($v['goods_detail_src']);
            }
            $goodsSkuList[$k] = $v;
        }
    }

    protected static function extractGoodsDetailPictures($details){
        $pattern = "/<img((?!src).)*src[\s]*=[\s]*[\'\"](?<src>[^\'\"]*)[\'\"]/";
        $images = [];
        if (preg_match_all($pattern,$details,$matches)!==false){
            foreach ($matches[2] as $v){
                $images[] =$v;
            }
        }
        return $images;
    }

    protected static function getRelativeGoodsDetailPictures($arr){
        if (empty($arr)){
            return[];
        }
        $res = [];
        foreach ($arr as $v){
            $res[] =Common::removeAbsoluteUrl($v);
        }
        return $res;
    }
}