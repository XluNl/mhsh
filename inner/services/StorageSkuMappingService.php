<?php


namespace inner\services;


use common\models\StorageSkuMapping;
use common\services\GoodsSkuService;
use inner\utils\ExceptionAssert;
use inner\utils\exceptions\BusinessException;
use inner\utils\StatusCode;
use Yii;
use yii\data\ActiveDataProvider;

class StorageSkuMappingService extends \common\services\StorageSkuMappingService
{

    /**
     * @param $goodsId
     * @param $skuId
     * @param $companyId
     * @param $storageSkuId
     * @param $storageSkuNum
     * @throws BusinessException
     */
    public static function bindStorageSkuI($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $skuModel = GoodsSkuService::getActiveGoodsSku($skuId,$goodsId,$companyId,false);
            ExceptionAssert::assertNotNull($skuModel,StatusCode::createExpWithParams(StatusCode::STORAGE_SKU_BIND_ERROR,'绑定的商品不存在'));
            list($res,$errorMsg) = self::bindStorageSku($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum);
            ExceptionAssert::assertTrue($res,StatusCode::createExpWithParams(StatusCode::STORAGE_SKU_BIND_ERROR,$errorMsg));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            throw StatusCode::createExpWithParams(StatusCode::STORAGE_SKU_BIND_ERROR,$e->getMessage());
        }
    }


    /**
     * 绑定列表
     * @param $companyIds
     * @param int $pageNo
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public static function bindList($companyIds,$pageNo=1,$pageSize=20){
        $condition = ['and',['company_id' => $companyIds]];
        $query = StorageSkuMapping::find()->where($condition)
            ->with(['goodsSku','goods']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'company_id' => SORT_ASC,
                    'id' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'page' =>$pageNo-1,
                'pageSize'=>$pageSize,
            ],
        ]);
        return $provider;

    }


    /**
     * @param $storageSkuMappingDataProvider ActiveDataProvider
     */
    public static function assembleStorageSkuMappingList(&$storageSkuMappingDataProvider)
    {
        $storageSkuMappingModels = $storageSkuMappingDataProvider->getModels();
        if (!empty($storageSkuMappingModels)){
            foreach ($storageSkuMappingModels as $k=>$v){
                if (!empty($v['goods'])){
                     GoodsDisplayDomainService::renameImageUrl($v['goods'],'goods_img');
                }
                if (!empty($v['goodsSku'])){
                     GoodsDisplayDomainService::renameImageUrl($v['goodsSku'],'sku_img');
                }
                $storageSkuMappingModels[$k] = $v;
            }
        }
        $storageSkuMappingDataProvider->setModels($storageSkuMappingModels);
    }

}