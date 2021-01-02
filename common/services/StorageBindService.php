<?php


namespace common\services;


use common\models\Common;
use common\models\StorageBind;
use yii\db\Query;

class StorageBindService
{

    /**
     * @param $companyId
     * @param bool $model
     * @return array|bool|\yii\db\ActiveRecord|null|StorageBind
     */
    public static function getModel($companyId,$model=false){
        $conditions = ['company_id' => $companyId];
        if ($model){
            return StorageBind::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(StorageBind::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $companyIds
     * @return array|null
     */
    public static function getModels($companyIds){
        $conditions = ['company_id' => $companyIds];
        $result = (new Query())->from(StorageBind::tableName())->where($conditions)->all();
        return $result===false?null:$result;
    }


    /**
     * 绑定
     * @param StorageBind $model
     * @return array
     */
    public static function bind(StorageBind $model){
        $existModel = StorageBind::findOne(['company_id'=>$model['id']]);
        if (!empty($existModel)){
            return [false,"已绑定过，不允许重新绑定"];
        }
        if (!$model->save()){
            return [false,Common::getModelErrors($model)];
        }
        return [true,''];
    }

}