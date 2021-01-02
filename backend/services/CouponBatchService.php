<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\RoleEnum;
use common\utils\DateTimeUtils;
use common\utils\ModelUtils;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CouponBatchService  extends \common\services\CouponBatchService
{


    /**
     * 获取可展示，非空校验
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|CouponBatch|null
     */
    public static function requireDisplayModel($id, $company_id, $validateException, $model = false){
        $model = self::getDisplayModel($id,[CouponBatch::STATUS_ACTIVE,CouponBatch::STATUS_DISABLED],$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 获取进行中，非空校验
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|CouponBatch|null
     */
    public static function requireActiveModel($id, $company_id, $validateException, $model = false){
        $model = self::getDisplayModel($id,CouponBatch::STATUS_ACTIVE,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }


    /**
     * 优惠券活动状态操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,CouponBatch::$statusArr),$validateException);
        $count = CouponBatch::updateAll(['status'=>$commander],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 更新优惠券活动是否弹窗
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function popOperate($id,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(key_exists($commander,CouponBatch::$isPopArr),$validateException);
        $count = CouponBatch::updateAll(['is_pop'=>$commander],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 补全券批次使用信息
     * @param $dataProvider
     * @return mixed
     */
    public static function completeUsedInfo($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        $ids = ModelUtils::getColFromModels($models,'id');
        if (empty($ids)){
            return $dataProvider;
        }
        $counts = (new Query())->from(Coupon::tableName())
            ->select(['COUNT(*) count','batch'])
            ->where(['batch'=>$ids,'status'=>Coupon::STATUS_USED])
            ->groupBy('batch')->all();
        $counts = empty($counts)?[]:ArrayHelper::map($counts,'batch','count');
        foreach ($models as $k=>$v){
            if (key_exists($v['id'],$counts)){
                $v->used_count = $counts[$v['id']];
            }
            else{
                $v->used_count = 0;
            }
            $models[$k]= $v;
        }
        $dataProvider->setModels($models);
        return $dataProvider;
    }


    public static function getModifyInitInfo($company_id,$use_limit_type,$use_limit_type_params){
        $sortArr = [];
        $goodsArr = [];
        $skusArr = [];
        $sortId = null;
        $goodId = null;
        $skuId = null;
        if ($use_limit_type==Coupon::LIMIT_TYPE_ALL){

        }
        else if ($use_limit_type==Coupon::LIMIT_TYPE_OWNER){

        }
        else if ($use_limit_type==Coupon::LIMIT_TYPE_SORT){
            $sortId = $use_limit_type_params;
            $sortModel = GoodsSortService::getActiveGoodsSort($use_limit_type_params,$company_id);
            if (!empty($sortModel)){
                $sortOwner = $sortModel['sort_owner'];
                $sortArr = GoodsSortService::getGoodsSortOptions($company_id,$sortOwner,0);
            }
        }
        else if ($use_limit_type==Coupon::LIMIT_TYPE_GOODS_SKU){
            $skuId = $use_limit_type_params;
            $skuModel = GoodsSkuService::getActiveGoodsSku($use_limit_type_params,null,$company_id);
            if (!empty($skuModel)){
                $goodsId = $skuModel['goods_id'];
                $skusArr = GoodsSkuService::getSkuListByGoodsIdOptions($goodsId,$company_id);
                $goodsModel = GoodsService::getActiveGoods($goodsId,$company_id);
                $goodId = $goodsId;
                if (!empty($goodsModel)){
                    $bigSort = $goodsModel['sort_1'];
                    $goodsArr = GoodsService::getListByBigSort($company_id,$bigSort);
                    $goodsArr = empty($goodsArr)?[]:ArrayHelper::map($goodsArr,'id','goods_name');
                    $sortModel = GoodsSortService::getActiveGoodsSort($bigSort,$company_id);
                    $sortId = $bigSort;
                    if (!empty($sortModel)){
                        $sortOwner = $sortModel['sort_owner'];
                        $sortArr = GoodsSortService::getGoodsSortOptions($company_id,$sortOwner,0);
                    }
                }
            }
        }
        return [$sortArr,$goodsArr,$skusArr,$sortId,$goodId,$skuId];
    }


    /**
     * 领取内部优惠券
     * @param $company_id
     * @param $customerId
     * @param $batchNo
     * @param $num
     * @param $operatorId
     * @param $operatorName
     * @param $remark
     */
    public static function drawPrivateCoupon($company_id,$customerId,$batchNo,$num,$operatorId,$operatorName,$remark){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::drawCoupon($company_id,$customerId,$batchNo,$num,false,$operatorId,$operatorName,RoleEnum::ROLE_ADMIN,$remark);
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::DRAW_COUPON_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BStatusCode::createExpWithParams(BStatusCode::DRAW_COUPON_ERROR,$e->getMessage()));
        }
    }



    public static function discardAll($batchId,$company_id,$operatorId,$operatorName){
        return Coupon::updateAll([
            'status'=>Coupon::STATUS_DISCARD,
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(time()),
            'remark'=>"{$operatorName}({$operatorId})作废优惠券",
        ],['batch'=>$batchId,'company_id'=>$company_id,'status'=>Coupon::STATUS_ACTIVE]);
    }
}