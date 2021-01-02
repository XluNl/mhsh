<?php


namespace backend\services;


use common\models\Common;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use yii\data\ActiveDataProvider;

class GoodsDisplayDomainService extends \common\services\GoodsDisplayDomainService
{
    /**
     * @param $goodsWithSkuDataProvider ActiveDataProvider
     */
    public static function assembleGoodsList(&$goodsWithSkuDataProvider)
    {
        $goodsModels = $goodsWithSkuDataProvider->getModels();
        if (!empty($goodsModels)){
            $goodsModels = parent::batchRenameImageUrlOrSetDefault($goodsModels,'goods_img');
        }
        $goodsWithSkuDataProvider->setModels($goodsModels);
    }


    /**
     * 补全仓库商品名称
     * @param $goodsWithSkuDataProvider
     */
    public static function completeStorageSkuName(&$goodsWithSkuDataProvider)
    {
        $goodsModels = $goodsWithSkuDataProvider->getModels();
        if (!empty($goodsModels)){
            $storageSkuIds = [];
            foreach ($goodsModels as $k=>$v){
                if (!empty($v['goodsSku'])){
                    $goodsSkuList = $v['goodsSku'];
                    if (!empty($goodsSkuList)){
                        foreach ($goodsSkuList as $kk=>$vv){
                            $goodsSku = $vv;
                            if (!empty($goodsSku['storageSkuMapping'])){
                                $storageSkuIds[] = $goodsSku['storageSkuMapping']['storage_sku_id'];
                            }
                        }
                    }
                }
            }
            if (!empty($storageSkuIds)){
                $storageSkuList = StorageSkuMappingService::getStorageSkuList($storageSkuIds);
                $storageSkuList = ArrayUtils::index($storageSkuList,'id');
                foreach ($goodsModels as $k=>$v){
                    if (!empty( $v['goodsSku'])){
                        $goodsSkuList = $v['goodsSku'];
                        if (!empty($goodsSkuList)){
                            foreach ($goodsSkuList as $kk=>$vv){
                                $goodsSku = $vv;
                                if (!empty($goodsSku['storageSkuMapping'])){
                                    if (key_exists($goodsSku['storageSkuMapping']['storage_sku_id'],$storageSkuList)){
                                        $goodsSku['storageSkuMapping']['storage_sku_name'] = $storageSkuList[$goodsSku['storageSkuMapping']['storage_sku_id']]['name'];
                                    }
                                    else{
                                        $goodsSku['storageSkuMapping']['storage_sku_name'] = $goodsSku['storageSkuMapping']['storage_sku_id'];
                                    }
                                }
                                $goodsSkuList[$kk] = $goodsSku;
                            }
                        }
                       // $v['goodsSku'] = $goodsSkuList;
                        $goodsModels[$k] = $v;
                    }
                }
            }
        }
        $goodsWithSkuDataProvider->setModels($goodsModels);
    }


}