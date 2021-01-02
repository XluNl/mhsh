<?php


namespace common\services;


use common\models\DistributeBalanceItem;
use common\utils\StringUtils;
use yii\db\Query;

class DistributeBalanceItemService
{
    /**
     * 获取model
     * @param $id
     * @param null $bizType
     * @param null $bizId
     * @param bool $model
     * @return array|bool|DistributeBalanceItem|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$bizType=null,$bizId=null,$model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isBlank($bizType)){
            $conditions['biz_type'] = $bizType;
        }
        if (!StringUtils::isBlank($bizId)){
            $conditions['biz_id'] = $bizId;
        }
        if ($model){
            return DistributeBalanceItem::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(DistributeBalanceItem::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据提现id查找日志
     * @param $withdrawId
     * @param bool $model
     * @return array|bool|DistributeBalanceItem|\yii\db\ActiveRecord|null
     */
    public static function getModelByWithdrawId($withdrawId,$model = false){
        $conditions = ['type_id' => $withdrawId,'type'=>DistributeBalanceItem::TYPE_WITHDRAW];
        if ($model){
            return DistributeBalanceItem::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(DistributeBalanceItem::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}