<?php


namespace common\services;


use common\models\Common;
use common\models\StorageSkuMapping;
use common\utils\StringUtils;
use yii\db\Query;

class StorageSkuMappingService
{
    /**
     * @param $goodsId
     * @param $skuId
     * @param $companyId
     * @param $storageSkuId
     * @param $storageSkuNum
     * @return array
     */
    public static function bindStorageSku($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum){
        $model = self::getModel($skuId,$companyId,true);
        if (empty($model)){
            $model = new StorageSkuMapping();
        }
        $model->company_id = $companyId;
        $model->goods_id = $goodsId;
        $model->sku_id = $skuId;
        $model->storage_sku_id = $storageSkuId;
        $model->storage_sku_num = $storageSkuNum;
        if ($model->save()){
            return [true,""];
        }
        else{
            return [false,Common::getModelErrors($model)];
        }
    }

    /**
     * @param $skuId
     * @param $companyId
     * @param false $model
     * @return array|bool|\yii\db\ActiveRecord|null|StorageSkuMapping
     */
    public static function getModel($skuId,$companyId,$model=false){
        $conditions = ['sku_id' => $skuId];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return StorageSkuMapping::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(StorageSkuMapping::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}