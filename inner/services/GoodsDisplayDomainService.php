<?php


namespace inner\services;


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
}