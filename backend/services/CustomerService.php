<?php


namespace backend\services;


use common\models\CommonStatus;
use common\models\Customer;
use common\utils\PhoneUtils;
use common\utils\StringUtils;
use yii\db\Query;

class CustomerService extends \common\services\CustomerService
{


    public static function getModelsByUserInfoId($userInfoIds){
        $conditions = ['user_id' => $userInfoIds];
        return (new Query())->from(Customer::tableName())->where($conditions)->all();
    }


    public static function searchCustomerList($keyword){
        $conditions = ['and',['status'=>CommonStatus::STATUS_ACTIVE]];
        if (StringUtils::isNotBlank($keyword)){
            $conditions[] = [
                'or',
                ['like','nickname',$keyword],
                ['like','realname',$keyword],
                ['phone'=>$keyword]
            ];
        }
        $customerModels =  Customer::find()->where($conditions)->asArray()->limit(20)->all();
        $result = [];
        if (!empty($customerModels)){
            foreach ($customerModels as $v){
                $result[] = ['id'=>$v['id'],'text'=>$v['nickname'],'phone'=>PhoneUtils::dataDesensitization($v['phone'],3,4)];
            }
        }
        return $result;
    }

    public static function searchCustomerOne($id){
        if (StringUtils::isBlank($id)){
            return [];
        }
        $conditions = ['and',['status'=>CommonStatus::STATUS_ACTIVE,'id'=>$id]];
        $customerModel =  Customer::find()->where($conditions)->asArray()->one();
        $result = [];
        if (!empty($customerModel)){
            $result[$customerModel['id']] = $customerModel['nickname'];
        }
        return $result;
    }
}