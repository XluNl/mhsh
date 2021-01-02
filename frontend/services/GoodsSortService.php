<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\GoodsSort;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsSortService  extends \common\services\GoodsSortService
{

    public static function getSortByParentId($parentId,$companyId,$sortOwner){
        $condition = ['sort_status' => CommonStatus::STATUS_ACTIVE, 'sort_show' => GoodsSort::SHOW_STATUS_SHOW, 'parent_id' => $parentId,'company_id'=>$companyId];
        if (!StringUtils::isBlank($sortOwner)){
            $condition['sort_owner'] = $sortOwner;
        }
        $sorts = (new Query())->from(GoodsSort::tableName())->orderBy("sort_order")
            ->where($condition)
            ->all();
        return $sorts;
    }
}