<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\models\RoleEnum;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class CouponBatchService extends \common\services\CouponBatchService
{

    public static function getPageFilterList($companyId,$deliveryId,$isPublic,$status,$pageNo=1,$pageSize=20){
        $condition = ['owner_id' => $deliveryId,'owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,'company_id'=>$companyId];
        if (StringUtils::isNotBlank($isPublic)){
            $condition['is_public'] = $isPublic;
        }
        if (StringUtils::isNotBlank($status)){
            $condition['status'] = $status;
        }
        else{
            $condition['status'] = [CouponBatch::STATUS_ACTIVE,CouponBatch::STATUS_DISABLED];
        }
        $query = CouponBatch::find()->offset(($pageNo - 1) * $pageSize)->limit($pageSize)->orderBy("created_at desc");
        $batches = $query->where($condition)
            ->asArray()
            ->all();
        //处理状态展示文本
        $batches = self::batchSetDisplayVO($batches);
        //补全优惠券使用数量
        $batches = self::fillUsedCouponNum($batches);
        return $batches;
    }

    /**
     * 补全优惠券使用数量
     * @param $models
     * @return mixed
     */
    public static function fillUsedCouponNum($models){
        if (StringUtils::isEmpty($models)){
            return $models;
        }
        $batchIds = ArrayUtils::getColumnWithoutNull('id',$models);
        $usedCouponStatistic =(new Query())->from(Coupon::tableName())->where(['status'=>Coupon::STATUS_USED,'batch'=>$batchIds])
            ->select([
                "batch",
                "COUNT(*) as num"
            ])
            ->groupBy('batch')->all();
        $usedCouponStatistic = ArrayUtils::index($usedCouponStatistic,'batch');
        foreach ($models as $k=>$v){
            if (key_exists($v['id'],$usedCouponStatistic)){
                $v['used_num'] = $usedCouponStatistic[$v['id']]['num'];
            }
            else{
                $v['used_num'] = 0;
            }
            $models[$k] = $v;
        }
        return $models;
    }




    public static function getUseLimitOption($useLimitType,$companyId,$deliveryId){
        if ($useLimitType == Coupon::LIMIT_TYPE_OWNER) {
            return [GoodsConstantEnum::OWNER_DELIVERY=>GoodsConstantEnum::$ownerArr[GoodsConstantEnum::OWNER_DELIVERY]];
        }
        else if ($useLimitType == Coupon::LIMIT_TYPE_SORT){
            $sortList = GoodsSortService::getGoodsSortList($companyId,GoodsConstantEnum::OWNER_DELIVERY,0);
            return ArrayUtils::map($sortList,'id','sort_name');
        }
        else if ($useLimitType == Coupon::LIMIT_TYPE_GOODS_SKU){
            $goodsList = GoodsSkuService::getSkuInfoCommon(null,null,$companyId,GoodsConstantEnum::OWNER_DELIVERY,$deliveryId);
            return ArrayUtils::map($goodsList,'id','goods_name','sku_name');
        }
        return [];
    }


    public static function getInfo($id, $deliveryId, $companyId, $model=false){
        $model = parent::getDisplayModel($id,null,$companyId,$model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::COUPON_BATCH_NOT_EXIST));
        ExceptionAssert::assertTrue($model['owner_type']==GoodsConstantEnum::OWNER_DELIVERY&&$model['owner_id']==$deliveryId,StatusCode::createExp(StatusCode::COUPON_BATCH_NOT_BELONG));
        return $model;
    }

    public static function statusOperation($deliveryModel,$id,$status){
        ExceptionAssert::assertTrue(in_array($status,[CouponBatch::STATUS_ACTIVE,CouponBatch::STATUS_DISABLED]),StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_STATUS_OPERATION_ERROR,'不支持的操作'));
        list($result,$errMsg) = parent::statusOperationP($deliveryModel['company_id'],GoodsConstantEnum::OWNER_DELIVERY,$deliveryModel['id'],$id,$status);
        ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_STATUS_OPERATION_ERROR,$errMsg));
    }

    /**
     * 发放团长优惠券
     * @param $companyId
     * @param $batch
     * @param $customerId
     * @param $num
     * @param $delivery
     * @param $remark
     */
    public static function manualDrawCoupon($companyId,$batch,$customerId,$num,$delivery,$remark){
        list($result,$errMsg) = parent::drawCoupon($companyId,$customerId,$batch['batch_no'],$num,$batch['is_public'],$delivery['id'],$delivery['nickname'],RoleEnum::ROLE_DELIVERY,$remark);
        ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_STATUS_OPERATION_ERROR,$errMsg));
    }
}