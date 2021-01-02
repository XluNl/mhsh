<?php


namespace frontend\services;


use backend\services\CouponService;

class CouponDisplayDomainService
{

    /**
     * 组装优惠券活动中的描述信息
     * @param $coupons
     * @return array
     */
    public static function assembleCouponInfo($coupons){
        $coupons = self::batchDefineDescVO($coupons);
        return $coupons;
    }


    /**
     * 批量定义展示文本
     * @param $dataList
     * @return array
     */
    public static function batchDefineDescVO($dataList){
        if (empty($dataList)){
            return [];
        }
        foreach ($dataList as $k=>$v){
            $v = self::defineDescVO($v);
            $dataList[$k] = $v;
        }
        return $dataList;
    }

    /**
     * @param $data
     * @return mixed
     */
    private static function defineDescVO($data){
        $data['desc_text'] = CouponService::generateCouponDesc($data['type'],$data['startup'],$data['discount'],$data['use_limit_type'],$data['use_limit_type_params']);
        return $data;
    }
}