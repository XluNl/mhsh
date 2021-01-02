<?php


namespace common\services;


use common\models\CustomerBalanceItem;
use yii\db\Query;

class CustomerBalanceItemService
{
    public static function getModelByWithdrawId($withdrawId,$model = false){
        $conditions = ['biz_code' => $withdrawId,'biz_type'=>CustomerBalanceItem::BIZ_TYPE_CUSTOMER_WITHDRAW];
        if ($model){
            return CustomerBalanceItem::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CustomerBalanceItem::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}