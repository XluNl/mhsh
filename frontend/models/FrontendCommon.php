<?php
namespace frontend\models;

use common\models\Common;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\User;
use common\utils\StringUtils;
use frontend\services\CustomerService;
use frontend\services\DeliveryService;
use frontend\services\UserInfoService;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;

class FrontendCommon extends Common
{

    public static function getPaymentCallBackUrl(){
        $baseUrl = Yii::$app->request->hostInfo;
        return $baseUrl.'/customer/order/notify';
    }

    public static function requiredAccessToken(){
        $access_token = Yii::$app->request->get('token','eTlX6dWcZ2yipp8F8xTwwOr5pjb82vo8');
        ExceptionAssert::assertNotBlank($access_token,StatusCode::createExp(StatusCode::NOT_LOGIN));
        return $access_token;
    }

    /**
     * @return User|\yii\web\IdentityInterface|null
     */
    public static function requiredUserModel(){
        $access_token = self::requiredAccessToken();
        $user = User::findIdentityByAccessToken($access_token);
        ExceptionAssert::assertNotNull($user,StatusCode::createExp(StatusCode::NOT_LOGIN));
        ExceptionAssert::assertNotNull($user->status == CommonStatus::STATUS_ACTIVE,StatusCode::createExp(StatusCode::MINI_WECHAT_ACCOUNT_DISABLED));
        return $user;
    }

    /**
     * 获取userId  可能为空
     * @return int|null
     */
    public static function getUserId(){
        $access_token = Yii::$app->request->get('token','A9vkKOYVw6ZJkwb9-aTCp0kzqanjjjOV');
        if (StringUtils::isBlank($access_token)){
            return null;
        }
        $user = User::findIdentityByAccessToken($access_token);
        if ($user==null){
            return null;
        }
        return $user->user_info_id;
    }

    public static function requiredUserId(){
        $user = self::requiredUserModel();
        ExceptionAssert::assertNotBlank($user->user_type==User::USER_TYPE_CUSTOMER,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        ExceptionAssert::assertNotBlank($user->user_info_id,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        return $user->user_info_id;
    }

    public static function requiredUserName(){
        list($userId,$userName) = self::requiredUserIdAndUserName();
        return $userName;
    }

    public static function requiredUserIdAndUserName(){
        $userId = self::requiredUserId();
        $userInfo = UserInfoService::requiredUserInfo($userId);
        return [$userInfo['id'],$userInfo['nickname']];
    }

    public static function requiredFCompanyId(){
        $deliveryModel = FrontendCommon::requiredDelivery();
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExp(StatusCode::DELIVERY_NOT_EXIST));
        return $deliveryModel->company_id;
    }

    public static function requiredCustomer(){
        $userId = FrontendCommon::requiredUserId();
        return CustomerService::requiredModelByUserId($userId);
    }

    public static function getCustomerId(){
        $userId = FrontendCommon::requiredUserId();
        $customer = CustomerService::getActiveModelByUserId($userId);
        if (empty($customer)){
            return null;
        }
        return $customer['id'];
    }

    public static function requiredActiveCustomer(){
        $userId = FrontendCommon::requiredUserId();
        return CustomerService::requiredActiveModelByUserId($userId);
    }

    public static function requiredCustomerId(){
        return self::requiredCustomer()['id'];
    }

    public static function requiredActiveCustomerId(){
        return self::requiredActiveCustomer()['id'];
    }

    public static function getDeliveryId(){
        $userModel = self::requiredUserModel();
        return DeliveryService::getSelectedDeliveryId($userModel);
    }
    public static function requiredDeliveryId(){
        $deliveryId =  self::getDeliveryId();
        ExceptionAssert::assertNotNull($deliveryId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_DELIVERY));
        return $deliveryId;
    }
    public static function requiredDelivery(){
        $deliveryId = self::getDeliveryId();
        ExceptionAssert::assertNotNull($deliveryId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_DELIVERY));
        $deliveryModel = Delivery::find()->where(['id'=>$deliveryId])->one();
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExp(StatusCode::DELIVERY_NOT_EXIST));
        return $deliveryModel;
    }

    public static function requiredCanOrderDelivery(){
        $deliveryId = self::getDeliveryId();
        ExceptionAssert::assertNotNull($deliveryId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_DELIVERY));
        $deliveryModel = Delivery::find()->where(['id'=>$deliveryId])->one();
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExp(StatusCode::DELIVERY_NOT_EXIST));
        ExceptionAssert::assertTrue($deliveryModel->allow_order!=Delivery::ALLOW_ORDER_FALSE,StatusCode::createExp(StatusCode::DELIVERY_NOT_ALLOW_ORDER));
        return $deliveryModel;
    }


}