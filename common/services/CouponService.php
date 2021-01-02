<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:21
 */

namespace common\services;


use common\models\Common;
use common\models\Coupon;
use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class CouponService
{

    public static function generateDescModel($couponModel){
        return self::generateCouponDesc($couponModel['type'],$couponModel['startup'],$couponModel['discount'],$couponModel['limit_type'],$couponModel['limit_type_params']);
    }

    /**
     * 优惠券描述信息
     * @param $type
     * @param $startup
     * @param $discount
     * @param $limitType
     * @param $limitTypeParams
     * @return string
     */
    public static function generateCouponDesc($type, $startup, $discount, $limitType, $limitTypeParams){
        $remark = '';
        if ($limitType==Coupon::LIMIT_TYPE_ALL){
            $remark = Coupon::$limitTypeArr[$limitType];
        }
        else if ($limitType==Coupon::LIMIT_TYPE_OWNER){
            if (key_exists($limitTypeParams,GoodsConstantEnum::$ownerArr)){
                $ownerArr = GoodsConstantEnum::$ownerArr;
                $remark ="限{$ownerArr[$limitTypeParams]}大品类";
            }
            else{
                return "unknown";
            }
        }
        else if ($limitType==Coupon::LIMIT_TYPE_SORT){
            $sortModel = (new Query())->from(GoodsSort::tableName())->where(['id'=>$limitTypeParams])->one();
            if (!empty($sortModel)){
                $remark ="限{$sortModel['sort_name']}品类";
            }
            else{
                return "unknown";
            }
        }
        else if ($limitType==Coupon::LIMIT_TYPE_GOODS_SKU){
            $goodsSkus = GoodsSkuService::getSkuInfoCommon([$limitTypeParams],null);
            if (empty($goodsSkus)){
                return "unknown";
            }
            $goodsSku = $goodsSkus[0];
            $remark ="限{$goodsSku['goods_name']}-{$goodsSku['sku_name']}商品";
        }
        if ($type==Coupon::TYPE_CASH_BACK){
            return $remark."满".Common::showAmount($startup).'元减'.Common::showAmount($discount).'元';
        }
        else if ($type==Coupon::TYPE_DISCOUNT){
            return $remark."满".Common::showAmount($startup).'元打'.Common::showAmount($discount).'折';
        }
        else{
            return "unknown";
        }
    }


    /**
     * 恢复优惠券
     * @param $company_id
     * @param $customerId
     * @param $orderNo
     * @return array
     */
    public static function recoveryCoupon($company_id, $customerId, $orderNo){
        if (StringUtils::isBlank($orderNo)){
            return [true,""];
        }
        $coupon = (new Query())->from(Coupon::tableName())->where(['order_no'=>$orderNo,'company_id'=>$company_id,'customer_id'=>$customerId])->one();
        if (empty($coupon)){
            return [true,""];
        }
        if ($coupon['restore']!=Coupon::RESTORE_TRUE){
            return [true,""];
        }
        $rows = Coupon::updateAll(['status'=>Coupon::STATUS_ACTIVE,'order_no'=>'','use_time'=>''],['coupon_no'=>$coupon['coupon_no'],'customer_id'=>$customerId,'company_id'=>$company_id,'status'=>Coupon::STATUS_USED,'restore'=>Coupon::RESTORE_TRUE]);
        if ($rows<1){
            return [false,"恢复优惠券失败"];
        }
        return [true,""];
    }

    public static function batchSetDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setDisplayVO($v);
            $list[$k] = $v;
        }
        return $list;
    }


    public static function setDisplayVO($model){
        if (empty($model)){
            return [];
        }
        $model['status_text'] = ArrayUtils::getArrayValue($model['status'],Coupon::$statusArr);
        $model['restore_text'] = ArrayUtils::getArrayValue($model['restore'],Coupon::$restoreArr);
        $model['use_limit_text'] = CouponService::generateCouponDesc($model['type'],$model['startup'],$model['discount'],$model['use_limit_type'],$model['use_limit_type_params']);
        if ($model['status']==Coupon::STATUS_USED){
            $model['remark'] =  "订单号:{$model['order_no']},使用时间:{$model['use_time']}";
        }
        return $model;
    }
}