<?php


namespace business\services;


use common\models\Coupon;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;

class CouponService extends \common\services\CouponService
{

    public static function getPageFilterList($batchId,$companyId,$deliveryId,$status,$pageNo=1,$pageSize=20){
        $condition = ['batch'=>$batchId,'owner_id' => $deliveryId,'owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,'company_id'=>$companyId];
        if (StringUtils::isNotBlank($status)){
            $condition['status'] = $status;
        }
        $coupons = Coupon::find()->offset(($pageNo - 1) * $pageSize)->limit($pageSize)->orderBy("created_at desc")->where($condition)->asArray()->all();
        $count = Coupon::find()->where($condition)->count();
        //处理状态展示文本
        $coupons = self::batchSetDisplayVO($coupons);
        return ['count'=>$count,'items'=>$coupons];
    }

}