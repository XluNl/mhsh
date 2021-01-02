<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace frontend\services;


use common\models\Goods;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class GoodsSkuService extends \common\services\GoodsSkuService
{


    public static function getSkuInfoQuery(){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $select = "{$goodsTable}.id as goods_id,goods_name, goods_img,goods_describe,sort_1,sort_2,".
            "{$skuTable}.id as sku_id,sku_name,sku_img,sku_unit,sku_describe,sku_price as price,sku_stock as stock,limit_cycle,limit_quantity";
        $goodsSkuQuery = (new Query())->from($goodsTable)->select($select)->leftJoin($skuTable,"{$goodsTable}.id=goods_id");
        return $goodsSkuQuery;
    }

    public static function getSkuImage($skuIds,$companyId){
        $skuTable = GoodsSku::tableName();
        $query = self::getSkuInfoQuery()->select("{$skuTable}.id,goods_img,sku_img")
        ->where(["{$skuTable}.id"=>$skuIds,"{$skuTable}.company_id"=>$companyId]);
        return $query->all();
    }

    public static function getSkuInfoListQuery($company_id,$bigSort,$smallSort){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $condition = ['goods_status' => Goods::STATUS_UP, 'sku_status' => Goods::STATUS_UP,Goods::tableName().'.company_id'=>$company_id];
        if ($bigSort!==null){
            $condition['sort_1'] = $bigSort;
        }
        if ($smallSort!==null){
            $condition['sort_2'] = $smallSort;
        }
        $goodsSkuQuery = GoodsSkuService::getSkuInfoQuery()
            ->where($condition)->orderBy("{$goodsTable}.display_order desc,{$skuTable}.display_order desc");
        return $goodsSkuQuery;
    }

    public static function assembleSkuInfoList($goodsSku){
        $goods = [];
        if (!empty($goodsSku)){
            foreach ($goodsSku as $sku){
                $goods_id = $sku['goods_id'];
                $sku_id = $sku['sku_id'];
                if (!ArrayHelper::keyExists($goods_id,$goods)){
                    $goods[$goods_id] = [];
                    $goods[$goods_id]['goods_id'] = $sku['goods_id'];
                    $goods[$goods_id]['goods_name'] = $sku['goods_name'];
                    $goods[$goods_id]['goods_img'] = $sku['goods_img'];
                    $goods[$goods_id]['goods_describe'] = $sku['goods_describe'];
                    $goods[$goods_id]['skus'] = [];
                }
                $goods[$goods_id]['skus'][$sku_id] = $sku;
                unset($goods[$goods_id]['skus'][$sku_id]['goods_id']);
                unset($goods[$goods_id]['skus'][$sku_id]['goods_name']);
                unset($goods[$goods_id]['skus'][$sku_id]['goods_img']);
                unset($goods[$goods_id]['skus'][$sku_id]['goods_describe']);
            }
        }
        return $goods;
    }

    /**
     * 减库存
     * @param $schedule_id
     * @param $skuId
     * @param $num
     */
    public static function reduceStock($schedule_id,$skuId,$num){
        $skuUpdateCount = GoodsSku::updateAllCounters(['sku_sold'=>$num],['id'=>$skuId]);
        ExceptionAssert::assertTrue($skuUpdateCount>0,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,"商品：{$skuId}总库存不足"));
        $scheduleUpdateCount = GoodsSchedule::updateAllCounters(['schedule_sold'=>$num],['and',['id'=>$schedule_id],"schedule_stock-schedule_sold>={$num}"]);
        ExceptionAssert::assertTrue($scheduleUpdateCount>0,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,"商品：{$skuId}活动库存不足"));
    }

    /**
     * 回库存
     * @param $schedule_id
     * @param $skuId
     * @param $num
     */
    public static function addStock($schedule_id,$skuId,$num){
        list($result,$error) = parent::addStock($schedule_id,$skuId,$num);
        ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));
    }
}