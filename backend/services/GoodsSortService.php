<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\CommonStatus;
use common\models\GoodsSchedule;
use common\models\GoodsSort;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class GoodsSortService extends \common\services\GoodsSortService
{

    /**
     * 获取商品分类 ,非空校验
     * @param $sortId
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|GoodsSchedule|\yii\db\ActiveRecord|null
     */
    public static function requireActiveGoodsSort($sortId,$company_id,$validateException,$model = false){
        $model = self::getActiveGoodsSort($sortId,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }


    /**
     * 是否显示操作
     * @param $sortId
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($sortId,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,GoodsSort::$showStatusArr),$validateException);
        $count = GoodsSort::updateAll(['sort_show'=>$commander],['id'=>$sortId,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * @param $sortId
     * @param $company_id
     * @param $validateException RedirectParams
     */
    public static function delete($sortId,$company_id,$validateException){
        $goodsModels = GoodsService::getListByBigSort($company_id,$sortId);
        BExceptionAssert::assertEmpty($goodsModels,$validateException->updateMessage("此分类存在商品"));
        $goodsModels = GoodsService::getListBySmallSort($company_id,$sortId);
        BExceptionAssert::assertEmpty($goodsModels,$validateException->updateMessage("此分类存在商品"));
        $count = GoodsSort::updateAll(['sort_status'=>CommonStatus::STATUS_DISABLED],['id'=>$sortId,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException->updateMessage("分类删除失败"));
    }


    /**
     * 获取sortOptions
     * @param $company_id
     * @param $sortOwner
     * @param int $parentId
     * @return array
     */
    public static function getGoodsSortOptions($company_id, $sortOwner, $parentId = 0){
        $arr = self::getSortByParent($company_id, $sortOwner, $parentId,null, false);
        return ArrayHelper::map($arr,'id','sort_name');
    }



    /**
     * 根据名称查找
     * @param $goodsSortName
     * @param $parentId
     * @param $company_id
     * @return array|bool|null
     */
    public static function getByGoodsSortName($goodsSortName,$parentId,$company_id){
        $conditions = ['sort_name' => $goodsSortName,'parent_id'=>$parentId, 'sort_status' => CommonStatus::STATUS_ACTIVE,'company_id'=>$company_id];
        $result = (new Query())->from(GoodsSort::tableName())->where($conditions)->one();
        return $result===false?null:$result;
    }

}