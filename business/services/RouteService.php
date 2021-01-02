<?php


namespace business\services;


use business\utils\exceptions\BusinessException;
use business\utils\response\StoreBaseResponseAssert;
use common\services\StorageBindService;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use Yii;

class RouteService extends  \common\services\RouteService
{


    public static function getStorageRoute($deliveryDate,$companyId,$deliveryId){
        $res = [
            'deliveryDate'=>$deliveryDate,
            'routeList'=>[],
        ];
        $storageBind = StorageBindService::getModel($companyId);
        if (empty($storageBind)){
            return $res;
        }
        $data= [
            'storeId'=>$storageBind['storage_id'],
            'deliveryDate'=>$deliveryDate,
            'company_id'=>$companyId,
            'deliveryId'=>$deliveryId,
            'from'=>1,
        ];
        $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/lifeRoute/arriveTime");
        try {
            $response = HttpClientUtils::post($url,$data);
            $responseData = StoreBaseResponseAssert::assertSuccessData($response);
            $res['routeList']  = $responseData;
            return $res;
        }
        catch (\Exception $e){
            throw BusinessException::create($e->getMessage());
        }
    }

}