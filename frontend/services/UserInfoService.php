<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\User;
use common\models\UserInfo;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;

class UserInfoService extends \common\services\UserInfoService
{
    /**
     * 获取用户信息
     * @param $userId
     * @param bool $validate
     * @param bool $model
     * @return array|bool|UserInfo|null
     */
    public static function requiredUserInfo($userId, $validate=false,$model=false){
        ExceptionAssert::assertNotNull($userId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'userId'));
        if ($model){
            $userInfoModel = UserInfo::findOne(['id'=>$userId]);
        }
        else{
            $userInfoModel = (new Query())->from(UserInfo::tableName())->where(['id'=>$userId])->one();
            $userInfoModel = $userInfoModel===false?null:$userInfoModel;
        }
        ExceptionAssert::assertNotNull($userInfoModel,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        if ($validate){
            ExceptionAssert::assertTrue($userInfoModel->status == CommonStatus::STATUS_ACTIVE,StatusCode::createExp(StatusCode::USER_INFO_NOT_EXIST));
        }
        return $userInfoModel;
    }

    /**
     * 通过手机号获取用户信息
     * @param $phone
     * @return UserInfo|null
     */
    public static function getActiveUserInfoByPhone($phone){
        ExceptionAssert::assertNotNull($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        $userInfoModel = UserInfo::findOne(['phone'=>$phone,'status'=>CommonStatus::STATUS_ACTIVE]);
        return $userInfoModel;
    }

    /**
     * 用户信息注册失败
     * @param $phone
     * @param $name
     * @param $headImageUrl
     * @param $userModel User
     * @return \common\models\Customer
     */
    public static function register($phone,$name,$headImageUrl,$userModel){
        $userInfo = UserInfo::findOne(['phone'=>$phone]);
        if (empty($userInfo)){
            $userInfo = new UserInfo();
            $userInfo->phone = $phone;
            $userInfo->nickname = $name;
            $userInfo->status = CommonStatus::STATUS_ACTIVE;
            $userInfo->is_customer = CommonStatus::STATUS_ACTIVE;
            $userInfo->head_img_url = $headImageUrl;
            ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,FrontendCommon::getModelErrors($userInfo)));
        }
        else {
            $exUserModel = User::findOne(['user_type'=>User::USER_TYPE_CUSTOMER,'user_info_id'=>$userInfo->id]);
            ExceptionAssert::assertNull($exUserModel,StatusCode::createExp(StatusCode::PHONE_USED));
            $userInfo->is_customer=CommonStatus::STATUS_ACTIVE;
            if (StringUtils::isNotBlank($headImageUrl)){
                $userInfo->head_img_url = $headImageUrl;
            }
            ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,FrontendCommon::getModelErrors($userInfo)));
        }
        $updateUserBool = parent::updateUserId($userModel->id,User::USER_TYPE_CUSTOMER,$userInfo->id);
        ExceptionAssert::assertTrue($updateUserBool,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,'重复更新,请刷新重试'));
        $customer = CustomerService::createCustomer($name,$phone,$userInfo->id);
        return $customer;
    }

    /**
     * 更新坐标
     * @param $userInfoId
     * @param $lat
     * @param $lng
     */
    public static function updateUserInfoLatLng($userInfoId,$lat,$lng){
        UserInfo::updateAll(['lat'=>$lat,'lng'=>$lng],['id'=>$userInfoId]);
    }

    /**
     * @param $userId
     * @return mixed|null
     */
    public static function getUserSexAndHead($userId){
        $user = User::findOne(['user_info_id'=>$userId]);
        if (!empty($user)){
            return [$user['sex'],$user['headimgurl']];
        }
        return  null;
    }
}