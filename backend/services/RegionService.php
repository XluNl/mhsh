<?php


namespace backend\services;


use yii\data\ActiveDataProvider;

class RegionService extends \common\services\RegionService
{
    /**
     * 批量处理provider中的省市县文本
     * @param $dataProvider ActiveDataProvider
     */
    public static function batchSetProvinceAndCityAndCountyForDataProvider(&$dataProvider)
    {
        if (empty($dataProvider)){
            return;
        }
        $models = $dataProvider->getModels();
        parent::batchSetProvinceAndCityAndCounty($models);
        $dataProvider->setModels($models);
    }

    public static function batchSetProvinceAndCityAndCountyForOrderProvider(&$dataProvider)
    {
        if (empty($dataProvider)){
            return;
        }
        $models = $dataProvider->getModels();
        parent::batchSetProvinceAndCityAndCountyForOrder($models);
        $dataProvider->setModels($models);
    }
}