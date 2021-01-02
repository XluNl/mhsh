<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\Company;
use common\models\Customer;
use yii\db\Query;

class CompanyService
{
    /**
     * 获取所有
     * @param null $ids
     * @return array
     */
    public static function getAllActiveModel($ids=null){
        $conditions = [ 'status' =>CommonStatus::STATUS_ACTIVE];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        $result = (new Query())->from(Company::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * @param null $ids
     * @return array
     */
    public static function getAllModel($ids=null){
        $conditions = [ ];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        $result = (new Query())->from(Company::tableName())->where($conditions)->orderBy('id desc')->all();
        return $result;
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
            return Company::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Company::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getModel($id, $model = false){
        $conditions = ['id' => $id];
        if ($model){
            return Company::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Company::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


}