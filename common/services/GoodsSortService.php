<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\GoodsSort;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsSortService
{
    /**
     * 获取商品分类model
     * @param $sortId
     * @param $company_id
     * @param bool $model
     * @return array|bool|GoodsSort|\yii\db\ActiveRecord|null
     */
    public static function getActiveGoodsSort($sortId,$company_id,$model = false){
        $conditions = ['id' => $sortId, 'sort_status' =>CommonStatus::STATUS_ACTIVE,'company_id'=>$company_id];
        if ($model){
            return GoodsSort::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GoodsSort::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    /**
     * 获取sort
     * @param $company_id
     * @param $sortOwner
     * @param int $parentId
     * @param bool $model
     * @param int $sortShow
     * @return array|\yii\db\ActiveQuery
     */
    public static function getSortByParent($company_id, $sortOwner, $parentId = 0,$sortShow = null, $model = false){
        if (StringUtils::isBlank($parentId)){
            return [];
        }
        $conditions = [
            'company_id'=>$company_id,
            'sort_status' => CommonStatus::STATUS_ACTIVE,
        ];
        if (!StringUtils::isBlank($sortOwner)){
            $conditions['sort_owner'] = $sortOwner;
        }
        if (!StringUtils::isBlank($sortShow)){
            $conditions['sort_show'] = $sortShow;
        }
        $conditions['parent_id'] = $parentId;

        if ($model==true){
            $sortModels = GoodsSort::find()->where($conditions)->orderBy("sort_order desc")->all();
        }
        else{
            $sortModels = (new Query())->from(GoodsSort::tableName())->where($conditions)->orderBy("sort_order desc")->all();
        }
        if (empty($sortModels)){
            return [];
        }
        return $sortModels;
    }


    /**
     *
     * @param $ids
     * @param null $company_id
     * @param bool $model
     * @return array|bool|GoodsSort|\yii\db\ActiveRecord|null
     */
    public static function getActiveModels($ids, $company_id=null, $model = false){
        $conditions = ['id' => $ids, 'sort_status' =>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return GoodsSort::find()->where($conditions)->all();
        }
        else{
            $result = (new Query())->from(GoodsSort::tableName())->where($conditions)->all();
            return $result;
        }
    }

    /**
     * 补全sortName
     * @param $scheduleArr
     */
    public static function completeSortName(&$scheduleArr){
        if (empty($scheduleArr)){
            return;
        }
        $bigSortIds = ArrayUtils::getColumnWithoutNull('sort_1',$scheduleArr);
        $smallSortIds = ArrayUtils::getColumnWithoutNull('sort_2',$scheduleArr);
        $sortIds = array_merge($bigSortIds,$smallSortIds);
        $sortModels = self::getActiveModels($sortIds);
        $sortModels = ArrayUtils::index($sortModels,'id');
        foreach ($scheduleArr as $k=>$v){
            if(key_exists('sort_1',$v)&&key_exists($v['sort_1'],$sortModels)){
                $v['sort_1_name'] = $sortModels[$v['sort_1']]['sort_name'];
            }
            else{
                $v['sort_1_name'] = "";
            }
            if(key_exists('sort_2',$v)&&key_exists($v['sort_2'],$sortModels)){
                $v['sort_2_name'] = $sortModels[$v['sort_2']]['sort_name'];
            }
            else{
                $v['sort_2_name'] = "";
            }
            $scheduleArr[$k] = $v;
        }
    }



    public static function completeSortNameWithSub(&$scheduleArr,...$subAttr){
        if (empty($scheduleArr)){
            return;
        }
        $bigSortIds = ArrayUtils::getSubColumnWithoutNull($scheduleArr,...array_merge($subAttr,['sort_1']));
        $smallSortIds = ArrayUtils::getSubColumnWithoutNull($scheduleArr,...array_merge($subAttr,['sort_2']));
        $sortIds = array_merge($bigSortIds,$smallSortIds);
        $sortModels = self::getActiveModels($sortIds);
        $sortModels = ArrayUtils::map($sortModels,'id','sort_name');
        foreach ($scheduleArr as $k=>$v){
            $sort1Name = "";
            $sort1 = ArrayUtils::getSubAttr($v,...array_merge($subAttr,['sort_1']));
            if (key_exists($sort1,$sortModels)){
                $sort1Name = $sortModels[$sort1];
            }
            ArrayUtils::setSubAttr($v,'sort_1_name',$sort1Name,...$subAttr);
            $sort2Name = "";
            $sort2 = ArrayUtils::getSubAttr($v,...array_merge($subAttr,['sort_2']));
            if (key_exists($sort2,$sortModels)){
                $sort2Name = $sortModels[$sort2];
            }
            ArrayUtils::setSubAttr($v,'sort_2_name',$sort2Name,...$subAttr);

            $scheduleArr[$k] = $v;
        }
    }


    /**
     * 列表
     * @param $companyId
     * @param $sortOwner
     * @param int $parentId
     * @return array|\yii\db\ActiveQuery
     */
    public static function getGoodsSortList($companyId, $sortOwner, $parentId = 0){
        $sortList = self::getSortByParent($companyId, $sortOwner, $parentId,GoodsSort::SHOW_STATUS_SHOW, false);
        $sortList = GoodsDisplayDomainService::batchRenameImageUrl($sortList,'pic_name');
        $sortList = GoodsDisplayDomainService::batchRenameImageUrl($sortList,'pic_icon');
        return $sortList;
    }


    public static function batchDisplayVO(){

    }


    public static function displayVO(){

    }
}