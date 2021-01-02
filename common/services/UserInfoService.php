<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\User;
use common\models\UserInfo;
use common\utils\StringUtils;
use yii\db\Query;

class UserInfoService
{
    /**
     * 根据id获取model
     * @param $id
     * @param bool $model
     * @return array|bool|UserInfo|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $model = false){
        $conditions = ['id' => $id];
        if ($model){
            return UserInfo::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(UserInfo::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据id获取可用model
     * @param $id
     * @param bool $model
     * @return array|bool|UserInfo|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $model = false){
        $conditions = ['id' => $id, 'status' =>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return UserInfo::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(UserInfo::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 更新用户id
     * @param $userId
     * @param $userType
     * @param $userInfoId
     * @return bool
     */
    public static function updateUserId($userId,$userType,$userInfoId){
        $updateCount = User::updateAll(['user_info_id'=>$userInfoId],['id'=>$userId,'user_type'=>$userType]);
        if ($updateCount>0){
            return true;
        }
        else{
            return false;
        }
    }

}