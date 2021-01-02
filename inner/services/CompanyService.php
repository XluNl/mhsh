<?php


namespace inner\services;


use common\models\CommonStatus;
use common\models\Company;
use yii\data\ActiveDataProvider;

class CompanyService extends \common\services\CompanyService
{

    public static function getList($pageNo, $pageSize)
    {
        $conditions = [ 'status' =>CommonStatus::STATUS_ACTIVE];
        $query = Company::find()->where($conditions);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'page' =>$pageNo-1,
                'pageSize'=>$pageSize,
            ],
        ]);
        return $provider;
    }

}