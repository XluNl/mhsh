<?php


namespace inner\services;


use common\models\CommonStatus;
use common\models\Delivery;
use common\utils\StringUtils;
use yii\data\ActiveDataProvider;

class DeliveryService extends \common\services\DeliveryService
{
    /**
     * @param $companyIds
     * @param $pageNo
     * @param $pageSize
     * @return ActiveDataProvider
     */
    public static function getList($companyIds,$pageNo, $pageSize)
    {
        $conditions = [ 'status' =>CommonStatus::STATUS_ACTIVE];
        if (StringUtils::isNotEmpty($companyIds)){
            $conditions['company_id']=$companyIds;
        }
        $query = Delivery::find()->where($conditions);
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