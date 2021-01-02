<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace alliance\services;


use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\utils\CopyUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;

class GoodsSkuService extends \common\services\GoodsSkuService
{

    public static function getPageFilter($allianceId, $status,$bigSortId,$smallSortId,$goodsName, $pageNo=1, $pageSize=20){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $select = "{$goodsTable}.*,{$skuTable}.*,{$skuTable}.id as sku_id";
        $condition = [
            'AND',
            [
                "{$goodsTable}.goods_owner"=>GoodsConstantEnum::OWNER_HA,
                'goods_owner_id' => $allianceId
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
        $skuList = $query->where($condition)->orderBy("{$skuTable}.updated_at desc")
            ->all();
        //处理状态展示文本
        $skuList = GoodsDisplayDomainService::assembleAllianceGoods($skuList);
        return $skuList;
    }


    public static function getSkuInfo($skuId, $allianceId, $company_id){
        $goodsSkuList = parent::getSkuInfoCommon($skuId,null,$company_id,GoodsConstantEnum::OWNER_HA,$allianceId);
        ExceptionAssert::assertNotEmpty($goodsSkuList,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_NOT_EXIST,"不存在"));
        self::completeDetail($goodsSkuList,$company_id);
        CopyUtils::batchCopyAttr($goodsSkuList,'sku_img','sku_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'sku_img_text');
        CopyUtils::batchCopyAttr($goodsSkuList,'goods_img','goods_img_text');
        $goodsSkuList = GoodsDisplayDomainService::batchRenameImageUrl($goodsSkuList,'goods_img_text');
        return $goodsSkuList[0];
    }

    /**
     * 更改状态
     * @param $skuId
     * @param $allianceId
     * @param $companyId
     * @param $status
     * @throws BusinessException
     */
    public static function changeGoodsStatus($skuId, $allianceId, $companyId,$status){
        ExceptionAssert::assertTrue(in_array($status,[GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_UP,GoodsConstantEnum::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN]),StatusCode::createExpWithParams(StatusCode::GOODS_SKU_STATUS_OPERATION_ERROR,"异常状态"));
        $goodsSkuList = parent::getSkuInfoCommon($skuId,null,$companyId,GoodsConstantEnum::OWNER_HA,$allianceId);
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
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_STATUS_OPERATION_ERROR,$e->getMessage()));
        }
    }








}