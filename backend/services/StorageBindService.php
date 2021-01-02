<?php


namespace backend\services;

use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\response\StoreBaseResponseAssert;
use common\utils\ArrayUtils;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use Yii;

class StorageBindService extends \common\services\StorageBindService
{

    /**
     * @param $validateException RedirectParams
     * @return array
     */
    public static function getStorageSelect($validateException){
        $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/storeList/select");
        try {
            $response = HttpClientUtils::post($url,[]);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
            return ArrayUtils::map($data,'id','name');
        }
        catch (\Exception $e){
            $validateException->updateMessage($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException);
        }
        return null;
    }
}