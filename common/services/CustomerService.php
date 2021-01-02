<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\CouponBatch;
use common\models\Customer;
use common\models\UserInfo;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CustomerService
{

    /**
     * 获取所有的
     * @param null $ids
     * @return array
     */
    public static function getAllActiveModel($ids=null){
        $conditions = ['status' =>CommonStatus::STATUS_ACTIVE];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        $result = (new Query())->from(Customer::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * @param null $ids
     * @return array
     */
    public static function getAllModel($ids=null){
        $conditions = [];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        return (new Query())->from(Customer::tableName())->where($conditions)->all();
    }

    /**
     * 根据ID获取customer
     * @param $id
     * @param bool $model
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $model = false){
        $conditions = ['id' => $id];
        if ($model){
            return Customer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Customer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 带user信息
     * @param $id
     * @param null $companyId
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getModelWithUser($id,$companyId=null){
        $conditions = ['id' => $id];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        return Customer::find()->where($conditions)->with('user')->asArray()->one();
    }

    /**
     * 根据ID获取customer
     * @param $id
     * @param bool $model
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $model = false){
        $conditions = ['id' => $id, 'status' =>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Customer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Customer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据
     * @param $userInfoId
     * @param bool $model
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByUserInfoId($userInfoId, $model = false){
        $conditions = ['user_id' => $userInfoId, 'status' =>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Customer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Customer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $userInfoId
     * @param bool $model
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getModelByUserInfoId($userInfoId, $model = false){
        $conditions = ['user_id' => $userInfoId, 'status' =>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Customer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Customer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取激活的用户
     * @param $inviteCode
     * @param bool $model
     * @return array|bool|Customer|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByInvitation($inviteCode, $model = false){
        $conditions = ['invite_code' => $inviteCode, 'status' =>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Customer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Customer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 补全信息
     * @param $customerArr
     * @param string $idKey
     */
    public static function completeCustomerInfo(&$customerArr,$idKey="id"){
        if (empty($customerArr)){
            return;
        }
        $customerIds = ArrayUtils::getColumnWithoutNull($idKey,$customerArr);
        $customerModels = self::getAllActiveModel($customerIds);
        $customerModels = empty($customerModels)?[]:ArrayHelper::index($customerModels,'id');
        foreach ($customerArr as $k=>$v){
            if (key_exists($v[$idKey],$customerModels)){
                $v['customer_name'] = $customerModels[$v[$idKey]]['nickname'];
                $v['customer_phone'] = $customerModels[$v[$idKey]]['phone'];
            }
            else{
                $v['customer_name'] = "";
                $v['customer_phone'] = "";
            }
            $customerArr [$k] = $v;
        }
    }

    /**
     * 查找用户
     * @param $keyword
     * @return array|CouponBatch[]|Customer[]|\common\models\DistributeBalanceItem[]|\common\models\Order[]|\common\models\OrderCustomerServiceGoods[]|\yii\db\ActiveRecord[]
     */
    public static function searchCustomerUserP($keyword){
        $conditions = ['and',['status'=>CommonStatus::STATUS_ACTIVE]];
        if (StringUtils::isNotBlank($keyword)){
            $conditions[] = [
                'or',
                ['like','nickname',$keyword],
                ['like','realname',$keyword],
                ['phone'=>$keyword]
            ];
        }
        return Customer::find()->where($conditions)->with('user')->asArray()->all();
    }


    /**
     * @param $phone
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function searchActiveCustomerByPhone($phone){
        $conditions = ['and',['status'=>CommonStatus::STATUS_ACTIVE]];
        if (StringUtils::isNotBlank($phone)){
            $conditions[] = ['phone'=>$phone];
        }
        return Customer::find()->where($conditions)->one();
    }


    /**
     * @param $customerId
     * @return mixed|string
     */
    public static function getNicknameById($customerId){
        $customer = self::getModel($customerId);
        return empty($customer)?"":$customer['nickname'];
    }

    /**
     * @param $phone
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function searchCustomerByPhone($phone){
        $conditions = ['and'];
        if (StringUtils::isNotBlank($phone)){
            $conditions[] = ['phone'=>$phone];
        }
        return Customer::find()->where($conditions)->one();
    }


    /**
     * 返回用户基本信息
     * @param $customerIds
     * @return array
     */
    public static function getBaseInfoWithHeadImageUrl($customerIds){
        if (empty($customerIds)){
            return [];
        }
        $customerIds = array_unique($customerIds);
        $customerTable = Customer::tableName();
        $userInfoTable = UserInfo::tableName();
        $data = (new Query())->select(["{$customerTable}.id","{$customerTable}.nickname","{$userInfoTable}.head_img_url"])->from($customerTable)->leftJoin($userInfoTable,
            "{$customerTable}.user_id={$userInfoTable}.id"
        )->where(["{$customerTable}.id" => $customerIds])->all();
        $data = GoodsDisplayDomainService::batchRenameImageUrl($data,'head_img_url');
        $data = ArrayUtils::index($data,'id');
        return $data;
    }
}