<?php


namespace inner\services;


use common\utils\ArrayUtils;

class GoodsSortService extends \common\services\GoodsSortService
{

    /**
     * 获取sortOptions
     * @param $company_id
     * @param $sortOwner
     * @param int $parentId
     * @return array
     */
    public static function getGoodsSortOptions($company_id, $sortOwner, $parentId = 0){
        $arr = self::getSortByParent($company_id, $sortOwner, $parentId,null, false);
        $map = ArrayUtils::map($arr,'id','sort_name');
        return ArrayUtils::mapToArray($map,'id','name');
    }
}