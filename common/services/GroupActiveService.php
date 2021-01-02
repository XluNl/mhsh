<?php


namespace common\services;


use common\models\GroupActive;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\Json;

class GroupActiveService
{
    /**
     * @param $id
     * @param null $companyId
     * @param false $model
     * @return array|bool|GroupActive|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $companyId=null, $model=false){
        $conditions = ['id' => $id,'status'=>GroupActive::$activeStatusArr];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return GroupActive::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GroupActive::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    /**
     * @param null $id
     * @param null $activeNo
     * @param null $companyId
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelWithSchedule($id=null,$activeNo=null, $companyId=null){
        $conditions = ['status'=>GroupActive::$activeStatusArr];
        if (StringUtils::isNotBlank($id)){
            $conditions['id'] = $id;
        }
        if (StringUtils::isNotBlank($activeNo)){
            $conditions['active_no'] = $activeNo;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $model = GroupActive::find()->where($conditions)->with('schedule')->asArray()->one();
        $model = self::displayVO($model);
        return $model;
    }

    /**
     * @param $id
     * @param null $companyId
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelWithScheduleGoodsSku($id, $companyId=null){
        $conditions = ['id' => $id,'status'=>GroupActive::$activeStatusArr];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $model = GroupActive::find()->where($conditions)->with(['schedule','schedule.goods', 'schedule.goodsSku'])->asArray()->one();
        $model = self::displayVO($model);
        return $model;
    }


    /**
     * @param null $id
     * @param null $activeNo
     * @param null $companyId
     * @param false $model
     * @return array|bool|GroupActive|\yii\db\ActiveRecord|null
     */
    public static function getModel($id=null,$activeNo=null, $companyId = null, $model = false)
    {
        $conditions = [];
        if (StringUtils::isNotBlank($id)) {
            $conditions['id'] = $id;
        }
        if (StringUtils::isNotBlank($companyId)) {
            $conditions['company_id'] = $companyId;
        }
        if (StringUtils::isNotBlank($activeNo)) {
            $conditions['active_no'] = $activeNo;
        }
        if ($model) {
            return GroupActive::find()->where($conditions)->one();
        } else {
            $result = (new Query())->from(GroupActive::tableName())->where($conditions)->one();
            return $result === false ? null : $result;
        }
    }

    /**
     * @param $activeNo
     * @param null $companyId
     * @return array
     */
    public static function getModelVO($activeNo, $companyId = null)
    {
        $result = self::getModel(null,$activeNo,$companyId,false);
        $result = self::displayVO($result);
        return $result;
    }

    public static function batchDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=> $v){
            $list[$k] = self::displayVO($v);
        }
        return $list;
    }



    public static function displayVO($model){
        if (empty($model)){
            return [];
        }
        list($rules, $maxLevel) = self::decodeRules($model['rule_desc']);
        $model['ruleDescText'] = $rules;
        $model['maxLevel'] = $maxLevel;
        $model['minLevel'] = 1;
        return $model;
    }


    public static function decodeRules($source){
        $rules = Json::decode($source);
        $ruleDesc = [];
        $maxLevel = 1;
        foreach ($rules as $key => $value) {
            if(empty($value)){
                continue;
            }
            $maxLevel = $key>$maxLevel?$key:$maxLevel;

            $item = [];
            $item['num'] = $key;
            $item['text'] = $key.'人团';
            $item['price'] = $value;
            $ruleDesc[] = $item;
        }
        return [$ruleDesc,$maxLevel];
    }

}