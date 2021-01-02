<?php


namespace common\services;


use common\models\Customer;
use common\models\CustomerInvitationLevel;
use common\models\UserInfo;
use yii\db\Query;

class CustomerInvitationLevelService
{
    /**
     * 根据customerId
     * @param $customerId
     * @param bool $model
     * @return array|bool|CustomerInvitationLevel|\yii\db\ActiveRecord|null
     */
    public static function getModelCustomerId($customerId, $model = false){
        $conditions = ['customer_id' => $customerId];
        if ($model){
            return CustomerInvitationLevel::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(CustomerInvitationLevel::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    public static function getModelWithUserInfo($customerId){
        $conditions = [Customer::tableName().".id" => $customerId];
        $result = (new Query())->from(Customer::tableName())
            ->leftJoin(CustomerInvitationLevel::tableName(),Customer::tableName().".id=".CustomerInvitationLevel::tableName().'.customer_id')
            ->leftJoin(UserInfo::tableName(),Customer::tableName().".user_id=".UserInfo::tableName().'.id')
            ->where($conditions)->one();
        CustomerInvitationDomainService::setInvitationLevelText($result);
        CustomerInvitationDomainService::setPhoneMarkText($result);
        $result= GoodsDisplayDomainService::renameImageUrl($result,'head_img_url');
        return $result===false?null:$result;
    }



}