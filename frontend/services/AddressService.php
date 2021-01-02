<?php


namespace frontend\services;


use common\models\CustomerAddress;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;

class AddressService
{
    public static function getAddressList($customerId){
        $addressList = (new Query())->from(CustomerAddress::tableName())->where(['customer_id'=>$customerId])->all();
        RegionService::batchSetProvinceAndCityAndCounty($addressList);
        return $addressList;
    }

    public static function getDefaultAddressList($customerId){
        $address = (new Query())->from(CustomerAddress::tableName())->where(['customer_id'=>$customerId])->orderBy("is_default desc,updated_at desc")->one();
        $address =  $address===false?null:$address;
        RegionService::setProvinceAndCityAndCounty($address);
        return $address;
    }

    public static function getAddressById($addressId,$customerId){
        $address = (new Query())->from(CustomerAddress::tableName())->where(['id'=>$addressId,'customer_id'=>$customerId])->one();
        $address =  $address===false?null:$address;
        ExceptionAssert::assertNotNull($address,StatusCode::createExp(StatusCode::ADDRESS_NOT_EXIST));
        RegionService::setProvinceAndCityAndCounty($address);
        return $address;
    }

    //todo 实现自提的地址
    public static function getDeliveryAddress($deliveryId){

        return [];
    }

    /**
     * 设置默认地址
     * @param $addressId
     * @param $customerId
     * @throws BusinessException
     * @throws \Exception
     */
    public static function setDefaultAddress($addressId,$customerId){
        ExceptionAssert::assertNotNull($addressId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"address_id"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $model = CustomerAddress::find()->where(['id'=>$addressId,'customer_id'=>$customerId])->one();
            ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::ADDRESS_NOT_EXIST));
            CustomerAddress::updateAll(['is_default'=>CustomerAddress::DEFAULT_FALSE],['customer_id'=>$customerId]);
            $model->is_default = CustomerAddress::DEFAULT_TRUE;
            ExceptionAssert::assertNotNull($model->save(),StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,"更新默认地址失败"));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,$e->getMessage()));
        }

    }

    /**
     * 移除地址
     * @param $addressId
     * @param $customerId
     */
    public static function remove($addressId,$customerId){
        ExceptionAssert::assertNotNull($addressId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"address_id"));
        $updateCount = CustomerAddress::deleteAll(['customer_id'=>$customerId,'id'=>$addressId]);
        ExceptionAssert::assertTrue($updateCount>0,StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,"更新默认地址失败"));
    }


}