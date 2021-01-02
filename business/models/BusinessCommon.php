<?php
namespace business\models;

use business\services\DeliveryService;
use business\services\PopularizerService;
use business\services\UserInfoService;
use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\Common;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\User;
use Yii;
use yii\helpers\Url;

class BusinessCommon extends Common
{
    public static function requiredAccessToken(){
        $access_token = Yii::$app->request->get('token','E5PY26PMllcy-hBEhE6KKaDLQmNy7LIa');
        ExceptionAssert::assertNotBlank($access_token,StatusCode::createExp(StatusCode::NOT_LOGIN));
        return $access_token;
    }

    public static function requiredUserModel(){
        $access_token = self::requiredAccessToken();
        $user = User::findIdentityByAccessToken($access_token);
        ExceptionAssert::assertNotNull($user,StatusCode::createExp(StatusCode::NOT_LOGIN));
        ExceptionAssert::assertNotNull($user->status == CommonStatus::STATUS_ACTIVE,StatusCode::createExp(StatusCode::MINI_WECHAT_ACCOUNT_DISABLED));
        return $user;
    }

    public static function requiredUserId(){
        $user = self::requiredUserModel();
        ExceptionAssert::assertNotBlank($user->user_type==User::USER_TYPE_BUSINESS,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        ExceptionAssert::assertNotBlank($user->user_info_id,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        return $user->user_info_id;
    }

    public static function requiredOpenId(){
        $user = self::requiredUserModel();
        ExceptionAssert::assertNotBlank($user->user_type==User::USER_TYPE_BUSINESS,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        return $user->openid;
    }

    public static function requiredUserName(){
        $userId = self::requiredUserId();
        $userInfoModel = UserInfoService::requiredUserInfo($userId);
        return $userInfoModel['nickname'];
    }



    public static function getFCompanyId(){
        $deliveryModel = self::requiredDelivery();
        return $deliveryModel['company_id'];
    }

    public static function getDeliveryId(){
        $userId = self::requiredUserId();
        return DeliveryService::getSelectedDeliveryId($userId);
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

    public static function checkDeliveryPermission($deliveryId,$userId){
        $deliveryModel = DeliveryService::getActiveModelByIdAndUserId($deliveryId,$userId);
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExpWithParams(StatusCode::DISTRIBUTE_BALANCE_DETAIL_ERROR,'信息不存在'));

    }
    public static function checkPopularizerPermission($id,$userId){
        $popularizerModel = PopularizerService::getActiveModelByIdAndUserId($id,$userId);
        ExceptionAssert::assertNotNull($popularizerModel,StatusCode::createExpWithParams(StatusCode::PERMISSION_NOT_ALLOW,'信息不存在'));
    }


    public static function getAuthCallBackUrl(){
        $baseUrl = Yii::$app->request->hostInfo;
        return $baseUrl.'/delivery/delivery/notify';
    }


    /**
     * 判断团长订单
     * @param $order
     * @param $deliveryId
     */
    public static function isDeliveryOrder($order,$deliveryId){
        ExceptionAssert::assertTrue(GoodsConstantEnum::OWNER_DELIVERY==$order['order_owner'],StatusCode::createExp(StatusCode::ORDER_NOT_BELONG));
        ExceptionAssert::assertTrue($deliveryId==$order['order_owner_id'],StatusCode::createExp(StatusCode::ORDER_NOT_BELONG));
    }


    
    //跳转
    public static function skipInfo($code,$mainMessage, $subMessage, $url1, $url1_msg, $url2=null, $url2_msg=null) {
        Yii::$app->session->setFlash('code', $code);
        Yii::$app->session->setFlash('mainMessage', $mainMessage);
        Yii::$app->session->setFlash('subMessage', $subMessage);
        Yii::$app->session->setFlash('url1', $url1);
        Yii::$app->session->setFlash('url1_msg', $url1_msg);
        Yii::$app->session->setFlash('url2', $url2);
        Yii::$app->session->setFlash('url2_msg', $url2_msg);
        Yii::$app->response->redirect(Url::toRoute('/tips/info'), 301)->send();
        return \Yii::$app->end();
    }









    /**
     * 充值回调接口
     * @return string
     */
    public static function getChargeCallBackUrl(){
        $baseUrl = Yii::$app->request->hostInfo;
        return $baseUrl.'/delivery/distribute-balance/charge-notify';
    }

}