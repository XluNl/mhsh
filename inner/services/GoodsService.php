<?php


namespace inner\services;


use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use yii\data\ActiveDataProvider;

class GoodsService extends \common\services\GoodsService
{
    /**
     * @param $companyId
     * @param $goodsOwner
     * @param $bigSort
     * @param $smallSort
     * @param $keyword
     * @param int $pageNo
     * @param int $pageSize
     * @return ActiveDataProvider
     */
    public static function getPageFilter($companyId,$goodsOwner,$bigSort,$smallSort,$keyword,$pageNo=1,$pageSize=20){
        $condition = ['and',['company_id' => $companyId,'goods_status'=>GoodsConstantEnum::$activeStatusArr]];
        if (StringUtils::isNotBlank($goodsOwner)){
            $condition[] = ['goods_owner'=>$goodsOwner];
        }
        if (StringUtils::isNotBlank($bigSort)){
            $condition[] = ['sort_1'=>$bigSort];
        }
        if (StringUtils::isNotBlank($smallSort)){
            $condition[] = ['sort_2'=>$smallSort];
        }
        if (StringUtils::isNotBlank($keyword)){
            $condition[] = ['like','goods_name',$keyword];
        }
        $query = Goods::find()->where($condition)
            ->with(['goodsSku.storageSkuMapping']);
        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'company_id' => SORT_ASC,
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