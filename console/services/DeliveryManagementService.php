<?php


namespace console\services;


use common\models\StorageDeliveryOut;
use common\utils\DateTimeUtils;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use console\utils\ExceptionAssert;
use console\utils\response\StoreBaseResponseAssert;
use console\utils\StatusCode;
use Yii;
use yii\helpers\Json;

class DeliveryManagementService extends \inner\services\DeliveryManagementService
{
    /**
     * 批量校验并组装通知信息
     * @param $nowTime
     * @return array[]
     */
    public static function batchNotifyStorage($nowTime){
        $storageDeliveryOutModels = StorageDeliveryOut::find()->where(['status'=>StorageDeliveryOut::STATUS_UN_CHECK])->asArray()->all();
        $successList = [];
        $failedList = [];
        if (!empty($storageDeliveryOutModels)){
            foreach ($storageDeliveryOutModels as $storageDeliveryOutModel){
                if (self::notifyStorage($storageDeliveryOutModel['trade_no'])){
                    $successList[] = $storageDeliveryOutModel['trade_no'];
                }
                else{
                    $failedList[] = $storageDeliveryOutModel['trade_no'];
                }
            }
        }
        return [$successList,$failedList];
    }

    /**
     * 校验并组装通知信息
     * @param $tradeNo
     * @return bool
     */
    public static function notifyStorage($tradeNo){
        $request = [];
        try {
            $storageDeliveryOut = StorageDeliveryOut::find()->where(['trade_no'=>$tradeNo])->one();
            ExceptionAssert::assertNotNull($storageDeliveryOut,StatusCode::createExpWithParams(StatusCode::NOTIFY_STORAGE_DELIVERY_OUT_ERROR,'不存在流水记录'));
            ExceptionAssert::assertTrue($storageDeliveryOut['status']==StorageDeliveryOut::STATUS_UN_CHECK,StatusCode::createExpWithParams(StatusCode::NOTIFY_STORAGE_DELIVERY_OUT_ERROR,'该流水已处理'));
            $request = self::assembleStorageInfo($storageDeliveryOut);
            $request['tradeNo'] = $tradeNo;
            $url = PathUtils::join(Yii::getAlias("@storeUrl"),"/lifeDelivery/receiveDelivery");
            $response = HttpClientUtils::post($url,$request);
            $data = StoreBaseResponseAssert::assertSuccessData($response);
            StorageDeliveryOut::updateAll(['status'=>StorageDeliveryOut::STATUS_CHECKED,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['trade_no'=>$tradeNo]);
            return true;
        }
        catch (\Exception $e){
            echo "tradeNo:{$tradeNo} notifyStorage error,request:".Json::encode($request).";error-info:".$e->getMessage().PHP_EOL;
            return false;
        }
    }
}