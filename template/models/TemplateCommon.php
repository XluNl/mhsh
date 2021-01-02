<?php
namespace template\models;

use common\models\Common;
use common\models\CommonStatus;
use common\models\User;
use common\utils\StringUtils;
use template\utils\ExceptionAssert;
use template\utils\StatusCode;
use Yii;

class TemplateCommon extends Common
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
        ExceptionAssert::assertNotNull($user->status == CommonStatus::STATUS_ACTIVE,StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_DISABLED));
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
        ExceptionAssert::assertNotBlank($user->user_type==User::USER_TYPE_OFFICIAL,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        ExceptionAssert::assertNotBlank($user->user_info_id,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        return $user->user_info_id;
    }



}