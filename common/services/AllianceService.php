<?php


namespace common\services;


use alliance\services\GoodsDisplayDomainService;
use common\models\Alliance;
use common\utils\StringUtils;
use yii\db\Query;

class AllianceService
{

    public static function generateAuthOrderNo($alliance,$time){
        $str = "AUTH";
        $timeStr = date("YmdH",$time);
        return $str.StringUtils::fullZeroForNumber($alliance,10).$timeStr;
    }

    /**
     * 获取所有的
     * @param null $ids
     * @param null $companyId
     * @return array
     */
    public static function getAllActiveModel($ids=null, $companyId=null){
        $conditions = [];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        if (!StringUtils::isEmpty($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $result = (new Query())->from(Alliance::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 获取model
     * @param $id
     * @param null $companyId
     * @param bool $model
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $companyId=null, $model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isEmpty($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return Alliance::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Alliance::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $id
     * @param null $companyId
     * @param bool $model
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $companyId=null, $model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isEmpty($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return Alliance::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Alliance::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取model
     * @param $userId
     * @param bool $model
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByUserId($userId, $model = false){
        $conditions = ['user_id' => $userId];
        if ($model){
            return Alliance::find()->where($conditions)->all();
        }
        else{
            return (new Query())->from(Alliance::tableName())->where($conditions)->all();
        }
    }

    /**
     * @param $userId
     * @param bool $model
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getModelByUserId($userId, $model = false){
        $conditions = ['user_id' => $userId];
        if ($model){
            return Alliance::find()->where($conditions)->all();
        }
        else{
            return (new Query())->from(Alliance::tableName())->where($conditions)->all();
        }
    }

    /**
     *
     * @param $ids
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getActiveModels($ids, $company_id=null, $model = false){
        $conditions = ['id' => $ids];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Alliance::find()->where($conditions)->all();
        }
        else{
            $result = (new Query())->from(Alliance::tableName())->where($conditions)->all();
            return $result;
        }
    }

    /**
     * 根据用户id和配送点id
     * @param $id
     * @param $userId
     * @param $model boolean
     * @return array|bool|Alliance|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByIdAndUserId($id,$userId,$model=false){
        $conditions = ['id' => $id,'user_id'=>$userId];
        if ($model){
            return Alliance::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Alliance::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 处理展示层
     * @param $models
     */
    public static function batchGetDisplayVO(&$models){
        if (empty($models)){
            return;
        }
        foreach ($models as $k=>$v){
            self::getDisplayVO($v);
            $models[$k] = $v;
        }
        return;
    }

    /**
     * 处理展示层
     * @param $model
     */
    public static function getDisplayVO(&$model){
        if (empty($model)){
            return;
        }
        RegionService::setProvinceAndCityAndCounty($model);
        $model = GoodsDisplayDomainService::renameImageUrl($model,'head_img_url');
        $model = GoodsDisplayDomainService::renameImageUrl($model,'store_images');
        $model = GoodsDisplayDomainService::renameImageUrl($model,'qualification_images');
        $model = GoodsDisplayDomainService::renameImageUrl($model,'contract_images');
    }


    /**
     * 补全图片的url，新增一个字段
     * @param $alliance
     * @return mixed
     */
    public static function completeImageUrlText($alliance){
        $alliance = GoodsDisplayDomainService::renameImageUrl($alliance,'head_img_url','head_img_url_text');
        $alliance = GoodsDisplayDomainService::renameImageUrl($alliance,'qualification_images','qualification_images_text');
        $alliance = GoodsDisplayDomainService::renameImageUrl($alliance,'store_images','store_images_text');
        $alliance = GoodsDisplayDomainService::renameImageUrl($alliance,'contract_images','contract_images_text');
        return $alliance;
    }
}