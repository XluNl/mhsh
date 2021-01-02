<?php
namespace alliance\models;

use alliance\services\AllianceService;
use alliance\services\UserInfoService;
use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\models\Alliance;
use common\models\Common;
use common\models\CommonStatus;
use common\models\User;
use Yii;

class AllianceCommon extends Common
{
    public static function requiredAccessToken(){
        $access_token = Yii::$app->request->get('token','PWh9yb29bhRalIIdXTbqTSETDL9RJu9c');
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
        $deliveryModel = self::requiredAlliance();
        return $deliveryModel['company_id'];
    }

    public static function getAllianceId(){
        $userId = self::requiredUserId();
        return AllianceService::getSelectedId($userId);
    }
    public static function requiredAllianceId(){
        $allianceId =  self::getAllianceId();
        ExceptionAssert::assertNotNull($allianceId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_ALLIANCE));
        return $allianceId;
    }
    public static function requiredAlliance(){
        $allianceId = self::getAllianceId();
        ExceptionAssert::assertNotNull($allianceId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_ALLIANCE));
        $allianceModel = Alliance::find()->where(['id'=>$allianceId])->one();
        ExceptionAssert::assertNotNull($allianceModel,StatusCode::createExp(StatusCode::ALLIANCE_NOT_EXIST));
        return $allianceModel;
    }

    public static function requiredCanOrderAlliance(){
        $allianceId = self::getAllianceId();
        ExceptionAssert::assertNotNull($allianceId,StatusCode::createExp(StatusCode::STATUS_NOT_SELECTED_ALLIANCE));
        $allianceModel = Alliance::find()->where(['id'=>$allianceId])->one();
        ExceptionAssert::assertNotNull($allianceModel,StatusCode::createExp(StatusCode::ALLIANCE_NOT_EXIST));
        ExceptionAssert::assertTrue($allianceModel->status!=Alliance::STATUS_ONLINE,StatusCode::createExp(StatusCode::ALLIANCE_NOT_ONLINE));
        return $allianceModel;
    }

    public static function checkAlliancePermission($id, $userId){
        $allianceModel = AllianceService::getActiveModelByIdAndUserId($id,$userId);
        ExceptionAssert::assertNotNull($allianceModel,StatusCode::createExpWithParams(StatusCode::PERMISSION_NOT_ALLOW,'信息不存在'));
    }






    public static function getAuthCallBackUrl(){
        $baseUrl = Yii::$app->request->hostInfo;
        return $baseUrl.'/alliance/alliance/notify';
    }

}