<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\response\StoreBaseResponseAssert;
use common\models\StorageSkuMapping;
use common\utils\ArrayUtils;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use common\utils\StringUtils;
use Yii;

class StorageSkuMappingService extends \common\services\StorageSkuMappingService
{
    /**
     * 绑定
     * @param $goodsId
     * @param $skuId
     * @param $companyId
     * @param $storageSkuId
     * @param $storageSkuNum
     * @param $expectSoldNum
     */
    public static function bindStorageSkuB($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum,$expectSoldNum){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            list($res,$errorMsg) = self::bindStorageSku($goodsId,$skuId,$companyId,$storageSkuId,$storageSkuNum);
            BExceptionAssert::assertTrue($res,BStatusCode::createExpWithParams(BStatusCode::STORAGE_SKU_BIND_ERROR,$errorMsg));

            self::notifyStorageExpectSoldNum($storageSkuId, $expectSoldNum);
            $transaction->commit();

        }
        catch (BBusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            throw BStatusCode::createExpWithParams(BStatusCode::STORAGE_SKU_BIND_ERROR,$e->getMessage());
        }
    }


    public static function getStorageSkuList($storageSkuIds){
        if (empty($storageSkuIds)){
            return [];
        }
        $storageSkuIds = array_unique($storageSkuIds);
        $request = ['ids'=>implode(",",$storageSkuIds)];
        $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/CommodityManage/details");
        try {
            $response = HttpClientUtils::post($url,$request);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
            if (!empty($data)){
                return $data;
            }
            return [];
        }
        catch (\Exception $e){

            return [];
        }
    }


    public static function getStorageSortSelect($storageId){
        $request = ['storeId'=>$storageId];
        $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/CommodityNav/select");
        try {
            $response = HttpClientUtils::post($url,$request);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
            return ArrayUtils::map($data,'id','name');
        }
        catch (\Exception $e){
            Yii::error("getStorageSortSelect error:".$e->getMessage());
            return [];
        }
    }

    /**
     * @param $storageId
     * @param $storageSortId
     * @param $storageSkuName
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     * @throws BBusinessException
     */
    public static function getStorageSkuSelect($storageId,$storageSortId,$storageSkuName,$pageNo=1,$pageSize=50){
        $data= [
            'pageId'=>$pageNo,
            'pageSize'=>$pageSize,
        ];
        if (StringUtils::isNotBlank($storageId)){
            $data['storeId'] = $storageId;
        }
        if (StringUtils::isNotBlank($storageSortId)){
            $data['navId'] = $storageSortId;
        }
        if (StringUtils::isNotBlank($storageSkuName)){
            $data['name'] = $storageSkuName;
        }
        $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/commodityManage/list");
        try {
            $response = HttpClientUtils::post($url,$data);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
            return ArrayUtils::map($data,'id','name');
        }
        catch (\Exception $e){
            throw BBusinessException::create($e->getMessage());
        }
    }

    /**
     * notify下游仓库预计售卖数量
     * @param $storageSkuId
     * @param $expectSoldNum
     * @throws BBusinessException
     */
    private static function notifyStorageExpectSoldNum($storageSkuId, $expectSoldNum)
    {
        $data = ['id' => $storageSkuId, 'lifeClaim' => $expectSoldNum];
        $url = PathUtils::join(Yii::getAlias("@storeUrl"), "/commodityManage/claim");
        try {
            $response = HttpClientUtils::post($url, $data);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
        } catch (\Exception $e) {
            Yii::error("getStorageSortSelect error:" . $e->getMessage());
            throw BBusinessException::create($e->getMessage());
        }
    }


}