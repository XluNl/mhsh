<?php


namespace alliance\services;


use alliance\models\AllianceCommon;
use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\CommonStatus;
use common\models\User;
use common\models\UserInfo;
use Yii;
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
     * 用户信息注册
     * @param $phone
     * @param $name
     * @param $userModel
     * @throws BusinessException
     * @throws \yii\db\Exception
     */
    public static function register($phone,$name,$userModel){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $userInfo = UserInfo::findOne(['phone'=>$phone]);
            if (empty($userInfo)){
                $userInfo = new UserInfo();
                $userInfo->phone = $phone;
                $userInfo->nickname = $name;
                $userInfo->status = CommonStatus::STATUS_ACTIVE;
                ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,AllianceCommon::getModelErrors($userInfo)));

            }
            else {
                $exUserModel = User::findOne(['user_type'=>User::USER_TYPE_ALLIANCE,'user_info_id'=>$userInfo->id]);
                ExceptionAssert::assertNull($exUserModel, StatusCode::createExp(StatusCode::PHONE_USED));
                ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,AllianceCommon::getModelErrors($userInfo)));
            }
            $updateUserBool = parent::updateUserId($userModel->id,User::USER_TYPE_ALLIANCE,$userInfo->id);
            ExceptionAssert::assertTrue($updateUserBool, StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,'重复更新,请刷新重试'));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }
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

}