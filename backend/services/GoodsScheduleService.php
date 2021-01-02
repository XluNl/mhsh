<?php


namespace backend\services;

use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

class GoodsScheduleService extends \common\services\GoodsScheduleService
{



    /**
     * 根据排期id批量获取（带商品&属性）
     * @param $scheduleIds
     * @param $companyId
     * @return array
     */
    public static function getActiveGoodsScheduleWithGoodsAndSkuB($scheduleIds, $companyId){
        return parent::getActiveGoodsScheduleWithGoodsAndSku($scheduleIds,$companyId,[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA],null);
    }

    /**
     * 获取商品排期 ,非空校验
     * @param $scheduleId
     * @param $companyId
     * @param $validateException
     * @param bool $model
     * @return array|bool|GoodsSchedule|\yii\db\ActiveRecord|null
     */
    public static function requireActiveGoodsSchedule($scheduleId, $companyId, $validateException, $model = false){
        $model = self::getActiveGoodsSchedule($scheduleId,$companyId,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 根据collectionId获取
     * @param $collectionId
     * @param $companyId
     * @param $validateException
     * @return array
     */
    public static function getActiveGoodsScheduleByCollectionId($collectionId, $companyId, $validateException=null){
        $conditions = ['collection_id' => $collectionId, 'schedule_status' =>GoodsConstantEnum::$activeStatusArr,'company_id'=>$companyId];
        $result = (new Query())->from(GoodsSchedule::tableName())->where($conditions)->all();
        if ($validateException!==null){
            BExceptionAssert::assertNotEmpty($result,$validateException);
        }
        return $result;
    }


    /**
     * 商品属性操作
     * @param $scheduleId
     * @param $commander
     * @param $companyId
     * @param $validateException
     */
    public static function operate($scheduleId, $commander, $companyId, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN,GoodsConstantEnum::STATUS_DELETED]),$validateException);
        $count = GoodsSchedule::updateAll(['schedule_status'=>$commander],['id'=>$scheduleId,'company_id'=>$companyId]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 根据goodsId获取
     * @param $goodsId
     * @param $companyId
     * @return array
     */
    public static function getSkuListByGoodsId($goodsId, $companyId){
        $conditions = [
            'goods_id' => $goodsId,
            'sku_status' => GoodsConstantEnum::$activeStatusArr,
            'company_id'=>$companyId
        ];
        $skuListArr = (new Query())->from(GoodsSku::tableName())->where($conditions)->all();
        return $skuListArr;
    }

    /**
     * 根据goodsId查询可投放的渠道
     * @param $goodsId
     * @param $companyId
     * @param $validateException
     * @return array
     */
    public static function getScheduleDisplayChannel($goodsId, $companyId, $validateException){
        $goodsModel = GoodsService::requireActiveGoods($goodsId,$companyId,$validateException);
        BExceptionAssert::assertTrue(key_exists($goodsModel->goods_owner,GoodsConstantEnum::$ownerArr),$validateException);
        $displayChannelIds = GoodsConstantEnum::$scheduleDisplayChannelMap[$goodsModel->goods_owner];
        $displayChannelArr = [];
        foreach ($displayChannelIds as $displayChannelId){
            $displayChannelArr[$displayChannelId] = GoodsConstantEnum::$scheduleDisplayChannelArr[$displayChannelId];
        }
        return $displayChannelArr;
    }

    /**
     * 生成排期formOptions(通过goodsId)
     * @param $goodsId
     * @param $companyId
     * @return array
     */
    public static function generateGoodsScheduleFormOptionsByGoodsId($goodsId, $companyId){
        $goodsArr = [];
        $goodsSkuArr = [];
        $scheduleDisplayChannelArr = [];
        try{
            $goodsModel = GoodsService::requireActiveGoods($goodsId,$companyId,BBusinessException::create("商品不能为空"),false);
            $goodsArr = GoodsService::getListByGoodsOwnerOptions($companyId,$goodsModel['goods_owner'],BBusinessException::create("根据goodsOwner获取商品列表失败"));
            $goodsSkuArr = GoodsSkuService::getSkuListByGoodsIdOptions($goodsId,$companyId);
            $scheduleDisplayChannelArr = GoodsSkuService::getScheduleDisplayChannel($goodsId,$companyId,BBusinessException::create("商品不存在"));
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
        }

        return [$goodsArr,$goodsSkuArr,$scheduleDisplayChannelArr];
    }

    /**
     * 生成排期formOptions(通过默认goodsOwner)
     * @param $goodsOwner
     * @param $companyId
     * @return array
     */
    public static function generateGoodsScheduleFormOptionsByGoodsOwner($goodsOwner, $companyId){
        $goodsArr = [];
        $goodsSkuArr = [];
        $scheduleDisplayChannelArr = [];
        try{
            $goodsArr = GoodsService::getListByGoodsOwnerOptions($companyId,$goodsOwner,BBusinessException::create("根据goodsOwner获取商品列表失败"));
            if (!empty($goodsArr)){
                $goodsSkuArr = GoodsSkuService::getSkuListByGoodsIdOptions(ArrayUtils::getFirstKeyFromArray($goodsArr),$companyId);
                $scheduleDisplayChannelArr = GoodsSkuService::getScheduleDisplayChannel(ArrayUtils::getFirstKeyFromArray($goodsArr),$companyId,BBusinessException::create("商品不存在"));
            }
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
        }

        return [$goodsArr,$goodsSkuArr,$scheduleDisplayChannelArr];
    }

    /**
     * @param $sourceModel GoodsSchedule
     * @param $schedule_id
     * @param $companyId
     */
    public static function copyTime(&$sourceModel, $schedule_id, $companyId){
        if (StringUtils::isBlank($schedule_id)){
            return;
        }
        $scheduleModel = self::getActiveGoodsSchedule($schedule_id,$companyId);
        if (!empty($scheduleModel)){
            $sourceModel->expect_arrive_time = $scheduleModel['expect_arrive_time'];
            $sourceModel->online_time = $scheduleModel['online_time'];
            $sourceModel->offline_time = $scheduleModel['offline_time'];
            $sourceModel->display_start = $scheduleModel['display_start'];
            $sourceModel->display_end = $scheduleModel['display_end'];
        }
    }

    /**
     * 命中优惠券批次统计
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function completeCouponBatchInfo($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        foreach ($models as $k=>$v){
            $cnt = (new Query())->from(CouponBatch::tableName())->where(['and',
                [
                    'use_limit_type'=>Coupon::LIMIT_TYPE_GOODS_SKU,
                    'use_limit_type_params'=>$v['sku_id'],
                    'status'=>CouponBatch::STATUS_ACTIVE],
                ['or',
                    ['<=','use_start_time',$v['online_time']],
                    ['>=','use_end_time',$v['offline_time']],
                ]
            ])->count();
            $v->coupon_batch_count = $cnt;
            $models[$k]= $v;
        }
        $dataProvider->setModels($models);
        return $dataProvider;
    }


    /**
     * @param $id
     * @param $commander
     * @param $companyId
     * @param $validateException
     */
    public static function recommendOperate($id, $commander, $companyId, $validateException){
        BExceptionAssert::assertTrue(key_exists($commander,GoodsSchedule::$isRecommendArr),$validateException);
        $count = GoodsSchedule::updateAll(['recommend'=>$commander],['id'=>$id,'company_id'=>$companyId]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }
    
}