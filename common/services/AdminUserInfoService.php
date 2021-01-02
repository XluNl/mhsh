<?php


namespace common\services;


use common\models\AdminUserInfo;
use yii\db\Query;

class AdminUserInfoService
{

    public static function getModelByUserId($userId, $model = false){
        $conditions = ['user_id' => $userId];
        if ($model){
            return AdminUserInfo::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(AdminUserInfo::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}