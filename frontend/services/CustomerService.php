<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\Customer;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class CustomerService extends \common\services\CustomerService
{
    /**
     * 根据customerId查询客户信息，$validate校验客户状态
     * @param $customerId
     * @param bool $validate
     * @return array|Customer|\yii\db\ActiveRecord|null
     */
    public static function getModelById($customerId,$validate=false){
        ExceptionAssert::assertNotEmpty($customerId,StatusCode::createExp(StatusCode::STATUS_PARAMS_MISS));
        $cModel = Customer::find()->where(['id' => $customerId])->one();
        ExceptionAssert::assertNotNull($cModel,StatusCode::createExp(StatusCode::CUSTOMER_NOT_EXIST));
        if ($validate){
            ExceptionAssert::assertTrue($cModel->status==Customer::STATUS_ACTIVE,StatusCode::createExp(StatusCode::RECORD_ITEM_DISABLE));
        }
        return $cModel;
    }

    public static function getModelByUserId($uid){
        return Customer::find()->where(['user_id' => $uid])->asArray()->one();
    }


    public static function getActiveModelByUserId($uid){
        $cModel = self::getModelByUserId($uid);
        if (empty($cModel)){
            return null;
        }
        if ( $cModel['status']!=Customer::STATUS_ACTIVE){
            return null;
        }
        return $cModel;
    }

    /**
     * 根据uid查询客户信息
     * @param $uid
     * @return array|Customer|\yii\db\ActiveRecord|null
     */
    public static function requiredModelByUserId($uid){
        ExceptionAssert::assertNotEmpty($uid,StatusCode::createExp(StatusCode::STATUS_PARAMS_MISS));
        $cModel = Customer::find()->where(['user_id' => $uid])->one();
        ExceptionAssert::assertNotNull($cModel,StatusCode::createExp(StatusCode::CUSTOMER_NOT_EXIST));
        return $cModel;
    }

    /**
     * 根据uid查询客户信息，校验客户状态
     * @param $uid
     * @return array|Customer|\yii\db\ActiveRecord|null
     */
    public static function requiredActiveModelByUserId($uid){
        $cModel = self::requiredModelByUserId($uid);
        ExceptionAssert::assertTrue($cModel->status==Customer::STATUS_ACTIVE,StatusCode::createExp(StatusCode::RECORD_ITEM_DISABLE));
        return $cModel;
    }

    /**
     * 创建用户
     * @param $name
     * @param $phone
     * @param $userId
     * @return Customer
     */
    public static function createCustomer($name,$phone,$userId){
        $customer = new Customer();
        $customer->status = CommonStatus::STATUS_ACTIVE;
        $customer->nickname = $name;
        $customer->phone = $phone;
        $customer->user_id = $userId;
        $customer->invite_code = $customer->createInviteCode();
        ExceptionAssert::assertTrue($customer->save(),StatusCode::createExpWithParams(StatusCode::CUSTOMER_CREATE_ERROR,FrontendCommon::getModelErrors($customer)));
        return $customer;
    }




}