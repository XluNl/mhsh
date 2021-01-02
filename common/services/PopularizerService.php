<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\Popularizer;
use common\utils\StringUtils;
use yii\db\Query;

class PopularizerService
{

    /**
     * 获取所有的
     * @param null $ids
     * @param null $company_id
     * @return array
     */
    public static function getAllActiveModel($ids=null,$company_id=null){
        $conditions = ['status'=>CommonStatus::STATUS_ACTIVE];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        $result = (new Query())->from(Popularizer::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 获取model
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Popularizer|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $company_id=null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Popularizer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Popularizer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    /**
     * 获取model
     * @param $userId
     * @param null $company_id
     * @param bool $model
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getActiveModelByUserId($userId, $company_id=null, $model = false){
        $conditions = ['user_id' => $userId,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Popularizer::find()->where($conditions)->all();
        }
        else{
            return $result = (new Query())->from(Popularizer::tableName())->where($conditions)->all();
        }
    }

    /**
     * @param $userId
     * @param null $company_id
     * @param bool $model
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getModelByUserId($userId, $company_id=null, $model = false){
        $conditions = ['user_id' => $userId];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Popularizer::find()->where($conditions)->all();
        }
        else{
            return $result = (new Query())->from(Popularizer::tableName())->where($conditions)->all();
        }
    }

    /**
     * 根据userId和分享者id
     * @param $id
     * @param $userId
     * @param bool $model
     * @return array|bool|Popularizer|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByIdAndUserId($id, $userId, $model = false){
        $conditions = ['id' => $id,'user_id'=>$userId,'status'=>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Popularizer::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Popularizer::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
}