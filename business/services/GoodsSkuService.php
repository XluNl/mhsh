<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\models\GoodsSoldChannel;
use common\utils\CopyUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Exception;
use Yii;
use yii\db\Query;

class GoodsSkuService extends \common\services\GoodsSkuService
{


    /**
     * @param $deliveryId
     * @param $bigSortId
     * @param $smallSortId
     * @param $status
     * @param $goodsName
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getPageFilter($deliveryId,$bigSortId,$smallSortId, $status,$goodsName, $pageNo=1, $pageSize=20){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $select = "{$goodsTable}.*,{$skuTable}.*,{$skuTable}.id as sku_id";
        $condition = [
            'AND',
            [
                "{$goodsTable}.goods_owner"=>GoodsConstantEnum::OWNER_DELIVERY,
                'goods_owner_id' => $deliveryId
            ]
        ];
        if (StringUtils::isNotBlank($goodsName)){
            $condition[] = ["like","{$goodsTable}.goods_name",$goodsName];
        }
        if (StringUtils::isNotBlank($bigSortId)){
            $condition[] = ["{$goodsTable}.sort_1"=>$bigSortId];
        }
        if (StringUtils::isNotBlank($smallSortId)){
            $condition[] = ["{$goodsTable}.sort_2"=>$smallSortId];
        }
        $query =(new Query())->select($select)->from($skuTable)->innerJoin($goodsTable,"{$goodsTable}.id={$skuTable}.goods_id")->offset(($pageNo - 1) * $pageSize)->limit($pageSize);
        switch ($status){
            case GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_UP:
                $condition[] = [
                    "{$skuTable}.sku_status"=>GoodsConstantEnum::STATUS_UP,
                    "{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP
                ];
                break;
            case GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN:
                $condition[] = [
                    "OR",
                    ['<>',"{$skuTable}.sku_status",GoodsConstantEnum::STATUS_UP],
                    ['<>',"{$goodsTable}.goods_status",GoodsConstantEnum::STATUS_UP]
                ];
                break;
            default:
        }
        $skuList = $query->where($condition)->orderBy("{$skuTable}.created_at desc")
            ->all();
        //处理状态展示文本
        $skuList = GoodsDisplayDomainService::assembleAllianceGoods($skuList);
        GoodsSortService::completeSortName($skuList);
        return $skuList;
    }


    public static function getHAPageFilter($companyId, $deliveryId, $bigSortId, $smallSortId, $goodsName, $pageNo=1, $pageSize=20){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $goodsSoldTable = GoodsSoldChannel::tableName();
        $select = "{$goodsTable}.*,{$skuTable}.*,{$skuTable}.id as sku_id";
        $condition = [
            'AND',
            [
                "{$goodsTable}.goods_owner"=>GoodsConstantEnum::OWNER_HA,
                "{$goodsTable}.company_id"=>$companyId,
            ]
        ];
        $condition[] = [
            'OR',
            ["{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT],
            [
                "{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY,
                "{$goodsSoldTable}.sold_channel_biz_id"=>$deliveryId,
            ]
        ];
        if (StringUtils::isNotBlank($goodsName)){
            $condition[] = ["like","{$goodsTable}.goods_name",$goodsName];
        }
        if (StringUtils::isNotBlank($bigSortId)){
            $condition[] = ["{$goodsTable}.sort_1"=>$bigSortId];
        }
        if (StringUtils::isNotBlank($smallSortId)){
            $condition[] = ["{$goodsTable}.sort_2"=>$smallSortId];
        }
        $query =(new Query())->select($select)->from($skuTable)
            ->innerJoin($goodsTable,"{$goodsTable}.id={$skuTable}.goods_id")
            ->leftJoin($goodsSoldTable,"{$goodsTable}.id={$goodsSoldTable}.goods_id and {$goodsTable}.goods_sold_channel_type = ".Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY)
            ->offset(($pageNo - 1) * $pageSize)->limit($pageSize);
        $condition[] = [
            "{$skuTable}.sku_status"=>GoodsConstantEnum::STATUS_UP,
            "{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP
        ];
        $skuList = $query->where($condition)->orderBy("{$skuTable}.created_at desc")
            ->all();
        //处理状态展示文本
        $skuList = GoodsDisplayDomainService::assembleAllianceGoods($skuList);
        self::completeDetail($goodsSkuList,$companyId);
        CopyUtils::batchCopyAttr($goodsSkuList,'sku_img','sku_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'sku_img_text');
        CopyUtils::batchCopyAttr($goodsSkuList,'goods_img','goods_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'goods_img_text');
        return $skuList;
    }


    public static function getSkuInfo($skuId, $deliveryId, $companyId,$ownerType){
        $goodsSkuList = parent::getSkuInfoCommon($skuId,null,$companyId,$ownerType,$deliveryId);
        ExceptionAssert::assertNotEmpty($goodsSkuList, StatusCode::createExpWithParams(StatusCode::GOODS_SKU_NOT_EXIST,"不存在"));
        self::completeDetail($goodsSkuList,$companyId);
        CopyUtils::batchCopyAttr($goodsSkuList,'sku_img','sku_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'sku_img_text');
        CopyUtils::batchCopyAttr($goodsSkuList,'goods_img','goods_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'goods_img_text');
        return $goodsSkuList[0];
    }

    /**
     * 更改状态
     * @param $skuId
     * @param $deliveryId
     * @param $companyId
     * @param $status
     * @throws BusinessException
     */
    public static function changeGoodsStatus($skuId, $deliveryId, $companyId, $status){
        ExceptionAssert::assertTrue(in_array($status,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN]),StatusCode::createExpWithParams(StatusCode::GOODS_SKU_STATUS_OPERATION_ERROR,"异常状态"));
        $goodsSkuList = parent::getSkuInfoCommon($skuId,null,$companyId,GoodsConstantEnum::OWNER_DELIVERY,$deliveryId);
        ExceptionAssert::assertNotEmpty($goodsSkuList,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_STATUS_OPERATION_ERROR,"商品不存在"));
        $goodsSku = $goodsSkuList[0];
        $transaction = Yii::$app->db->beginTransaction();
        try{
            GoodsSku::updateAll(['sku_status'=>$status,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$goodsSku['sku_id'],'company_id'=>$companyId]);
            Goods::updateAll(['goods_status'=>$status,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$goodsSku['goods_id'],'company_id'=>$companyId]);
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw  $e;
        }
        catch (Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_STATUS_OPERATION_ERROR,$e->getMessage()));
        }
    }

    public static function getSkuInfoById($skuIds,$goodsIds,$company_id=null){
        return parent::getSkuInfoCommon($skuIds,$goodsIds,$company_id);
    }

}