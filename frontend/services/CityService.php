<?php


namespace frontend\services;


use common\models\Region;
use common\utils\StringUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;

class CityService
{
    public static function searchCityByName($name){
        $name = StringUtils::removeSubString($name,'市','自治州','县');
        $cityModel = (new Query())->from(Region::tableName())->where([
            'and',
            ['level'=>1],
            ['like','name',$name]
        ])->one();
        ExceptionAssert::assertTrue($cityModel!==false,StatusCode::createExpWithParams(StatusCode::CITY_SEARCH_ERROR));
        return $cityModel;
    }

    public static function getOpenCities(){
        $data = [
            [
                'title'=>'热门城市',
                'value'=>[
                    [
                        'id'=>'330100',
                        'name'=>'杭州',
                    ]
                ],
            ],
            [
                'title'=>'H',
                'value'=>[
                    [
                        'id'=>'330100',
                        'name'=>'杭州',
                    ]
                ],
            ],
            [
                'title'=>'S',
                'value'=>[
                    [
                        'id'=>'330600',
                        'name'=>'绍兴',
                    ]
                ],
            ]
        ];
        return $data;
    }
}