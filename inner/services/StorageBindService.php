<?php


namespace inner\services;


use common\models\StorageBind;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class StorageBindService extends \common\services\StorageBindService
{

    /**
     * @param $storageId
     * @param $pageNo
     * @param $pageSize
     * @return ActiveDataProvider
     */
    public static function getModelsByStorageId($storageId,$pageNo,$pageSize){

        $conditions = [ 'storage_id' => $storageId];
        $query = (new Query())->from(StorageBind::tableName())->where($conditions)->orderBy('company_id desc');
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'page' =>$pageNo-1,
                'pageSize'=>$pageSize,
            ],
        ]);
        return $provider;
    }

}