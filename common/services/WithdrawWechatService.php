<?php


namespace common\services;


use common\models\WithdrawWechat;
use yii\db\Query;

class WithdrawWechatService
{
    /**
     * 根据$withdrawApplyId查询
     * @param $withdrawApplyId
     * @param bool $model
     * @return array|bool|WithdrawWechat|\yii\db\ActiveRecord|null
     */
    public static function getModel($withdrawApplyId,$model = false){
        $conditions = ['withdraw_apply_id' => $withdrawApplyId];
        if ($model){
            return WithdrawWechat::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(WithdrawWechat::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}