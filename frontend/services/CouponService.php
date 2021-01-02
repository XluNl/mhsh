<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:21
 */

namespace frontend\services;


use common\models\Common;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;

class CouponService extends \common\services\CouponService
{

    const DAY_LIMIT_USED = 1;

    public static function getCouponArrayByNo($couponNo,$customerId,$company_id,$validate=false){
        $nowTime = DateTimeUtils::parseStandardWLongDate();
        $couponModel = (new Query())->from(Coupon::tableName())->where(['coupon_no'=>$couponNo,'customer_id'=>$customerId,'company_id'=>$company_id])->andWhere([['status'=>Coupon::STATUS_ACTIVE],['<=','start_time',$nowTime],['>=','end_time',$nowTime]])->one();
        if ($validate){
            ExceptionAssert::assertNotNull($validate,StatusCode::createExp(StatusCode::COUPON_NOT_EXIST));
        }
        return $couponModel;
    }


    /**
     * 是否达到当日限制
     * @param $customer_id
     * @param $company_id
     * @return bool
     */
    public static function limitDayNum($customer_id,$company_id){
        $todayDate = date('Y-m-d');
        $startUsed = DateTimeUtils::parseStandardWLongDate(strtotime($todayDate));
        $endUsed = DateTimeUtils::parseStandardWLongDate(strtotime($todayDate) + 24 * 3600-1);
        $couponUsedModel = (new Query())->from(Coupon::tableName())->where([
            'and',
            ['customer_id'=>$customer_id,'status'=>Coupon::STATUS_USED,'company_id'=>$company_id],
            ['>=', 'use_time', $startUsed],
            ['<=', 'use_time', $endUsed]
        ])->all();
        if (empty($couponUsedModel)||count($couponUsedModel)< self::DAY_LIMIT_USED){
            return true;
        }
        return false;
    }

    /**
     * 获得各品类的优惠券
     * @param $company_id
     * @param $customer_id
     * @param $goods_list
     * @return array
     */
    public static function getAvailableCoupon($company_id,$customer_id, $goods_list) {
        if (empty($goods_list)){
            return [];
        }
        $coupons = [];
        $nowTime = DateTimeUtils::parseStandardWLongDate();
        $couponModels = (new Query())->from(Coupon::tableName())->where([
            'and',
            [
                'customer_id'=>$customer_id,
                'status'=>Coupon::STATUS_ACTIVE,
                'company_id'=>$company_id
            ],
            ['<=', 'start_time', $nowTime],
            ['>=', 'end_time', $nowTime],
        ])->orderBy('discount desc')->all();
        if (!empty($couponModels)){
            foreach ($couponModels as $couponK=>$couponV){
                if (self::couponUseForGoodsList($goods_list,$couponV)){
                    $couponModels[$couponK]['can_use'] = true;
                }
                else{
                    $couponModels[$couponK]['can_use'] = false;
                }
            }
        }
        if (empty($couponModels)){
            $noCoupon = [];
            /*$noCoupon['name'] = "暂无可使用的优惠券";
            $noCoupon['coupon_no'] = 0;
            $noCoupon['discount'] = 0;
            $noCoupon['remark'] = "暂无可使用的优惠券";
            $coupons[] = $noCoupon;*/
        }
        else{
            foreach ($couponModels as $key => $value){
                $couponVO = [];
                $couponVO['name'] = $value['name'];
                $couponVO['startup'] = $value['startup'];
                $couponVO['coupon_no'] = $value['coupon_no'];
                $couponVO['discount'] = $value['discount'];
                $couponVO['display_discount'] = Common::showAmount($value['discount']);
                $couponVO['can_use'] = $value['can_use'];
                $couponVO['remark'] = CouponService::generateCouponDesc($value['type'],$value['startup'],$value['discount'],$value['limit_type'],$value['limit_type_params']);
                $coupons[] = $couponVO;
            }
        }
        $coupons = ArrayUtils::index($coupons,'coupon_no');
        return $coupons;
    }

    /**
     * 校验优惠券是否能使用于指定商品列表
     * @param $skuList
     * @param $coupon
     * @return bool
     */
    public static function couponUseForGoodsList($skuList, &$coupon)
    {
        $couponDistribution = [];
        if ($coupon['limit_type'] == Coupon::LIMIT_TYPE_ALL) {
            $goodsAmount = 0;
            foreach ($skuList as $sku) {
                $goodsAmount += $sku['num'] * $sku['price'];
                $couponDistribution[] = ['sku_id'=>$sku['sku_id'],'amount'=>$sku['num'] * $sku['price']];
            }
            if ($goodsAmount < $coupon['startup']) {
                return false;
            }
        } else if ($coupon['limit_type'] == Coupon::LIMIT_TYPE_OWNER) {
            $owner = $coupon['limit_type_params'];
            $goodsAmount = 0;
            foreach ($skuList as $sku) {
                if ($sku['goods_owner'] == $owner) {
                    if ($coupon['owner_type']==GoodsConstantEnum::OWNER_SELF||$coupon['owner_type']==$sku['goods_owner']&&$coupon['owner_id']==$sku['goods_owner_id']){
                        $goodsAmount += $sku['num'] * $sku['price'];
                        $couponDistribution[] = ['sku_id'=>$sku['sku_id'],'amount'=>$sku['num'] * $sku['price']];
                    }
                }
            }
            if ($goodsAmount < $coupon['startup']) {
                return false;
            }
        } else if ($coupon['limit_type'] == Coupon::LIMIT_TYPE_SORT) {
            $sort_id = $coupon['limit_type_params'];
            $goodsAmount = 0;
            foreach ($skuList as $sku) {
                if ($sku['sort_1'] == $sort_id) {
                    if ($coupon['owner_type']==GoodsConstantEnum::OWNER_SELF||$coupon['owner_type']==$sku['goods_owner']&&$coupon['owner_id']==$sku['goods_owner_id']){
                        $goodsAmount += $sku['num'] * $sku['price'];
                        $couponDistribution[] = ['sku_id'=>$sku['sku_id'],'amount'=>$sku['num'] * $sku['price']];
                    }

                }
            }
            if ($goodsAmount < $coupon['startup']) {
                return false;
            }
        } else if ($coupon['limit_type'] == Coupon::LIMIT_TYPE_GOODS_SKU) {
            $sku_id = $coupon['limit_type_params'];
            $goodsAmount = 0;
            foreach ($skuList as $sku) {
                if ($sku['sku_id'] == $sku_id) {
                    if ($coupon['owner_type']==GoodsConstantEnum::OWNER_SELF||$coupon['owner_type']==$sku['goods_owner']&&$coupon['owner_id']==$sku['goods_owner_id']){
                        $goodsAmount += $sku['num'] * $sku['price'];
                        $couponDistribution[] = ['sku_id'=>$sku['sku_id'],'amount'=>$sku['num'] * $sku['price']];
                    }
                }
            }
            if ($goodsAmount < $coupon['startup']) {
                return false;
            }
        }
        else {
            return false;
        }
        $coupon['couponDistribution'] = $couponDistribution;
        return true;
    }

    /**
     * 分配优惠金额
     * @param $coupon
     */
    public static function distributeDiscount(&$coupon){
        if (key_exists('couponDistribution',$coupon)&&!empty($coupon['couponDistribution'])){
            $amount = 0;
            $calcDiscount = 0;
            foreach ($coupon['couponDistribution'] as $k=>$v){
                $amount +=$v['amount'];
            }
            if ($coupon['type']==Coupon::TYPE_CASH_BACK){
                $calcDiscount = $coupon['discount'];
                $c = count($coupon['couponDistribution']);
                $allDiscount = $coupon['discount'];
                for ($i=0;$i<$c;$i++){
                    if ($i+1<$c){ 
                        $goodsDiscount = intval($coupon['discount'] * $coupon['couponDistribution'][$i]['amount'] /$amount);
                        $coupon['couponDistribution'][$i]['discount'] = $goodsDiscount;
                        $allDiscount -= $goodsDiscount;
                    }
                    else{
                        $coupon['couponDistribution'][$i]['discount'] = $allDiscount;
                        $allDiscount=0;
                    }
                }
            }
            else if ($coupon['type']==Coupon::TYPE_DISCOUNT){
                $c = count($coupon['couponDistribution']);
                for ($i=0;$i<$c;$i++){
                    $goodsDiscount = intval( Common::showAmount($coupon['discount']) * $coupon['couponDistribution'][$i]['amount']);
                    $coupon['couponDistribution'][$i]['discount'] = $goodsDiscount;
                    $calcDiscount +=$goodsDiscount;
                }
            }

            $coupon['calcDiscount'] = $calcDiscount;
        }
        else{
            $coupon['couponDistribution'] = [];
            $coupon['calcDiscount'] = 0;
        }
    }

    /**
     * 核销优惠券
     * @param $company_id
     * @param $customerId
     * @param $couponNo
     * @param $orderNo
     */
    public static function verifyCoupon($company_id, $customerId, $couponNo, $orderNo){
        $rows = Coupon::updateAll(['status'=>Coupon::STATUS_USED,'order_no'=>$orderNo,'use_time'=> date("Y-m-d H:i:s",time())],['coupon_no'=>$couponNo,'customer_id'=>$customerId,'company_id'=>$company_id,'status'=>Coupon::STATUS_ACTIVE]);
        ExceptionAssert::assertTrue($rows>0,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,"使用优惠券失败"));
    }



    /**
     * 校验优惠券是否有效
     * @param $company_id
     * @param $coupon_no
     * @param $customer_id
     * @param $goods_list
     * @return array
     */
    public static function checkCoupon($company_id,$coupon_no,$customer_id, $goods_list){
        $nowTime = time();
        $couponModel = (new Query())->from(Coupon::tableName())->where(['customer_id'=>$customer_id,'coupon_no'=>$coupon_no,'company_id'=>$company_id])->one();
        if (empty($couponModel)||$couponModel['status']==Coupon::STATUS_DISCARD){
            return [false,"编号为{$coupon_no}的优惠券不存在！"];
        }
        else if ($couponModel['status']==Coupon::STATUS_USED){
            return [false,"编号为{$coupon_no}的优惠券已使用！"];
        }
        else if (strtotime($couponModel['start_time'])>$nowTime||strtotime($couponModel['end_time'])<$nowTime){
            $msg = "编号为".$coupon_no.'的使用时间为 '.DateTimeUtils::parseStandardWStrDate($couponModel['start_time']).'至'.DateTimeUtils::parseStandardWStrDate($couponModel['end_time']);
            return [false,$msg];
        }
        else if (!self::couponUseForGoodsList($goods_list,$couponModel)){
            $msg = "编号为{$coupon_no}的使用起用金额为".Common::showAmount($couponModel['startup']).'元！';
            return [false,$msg];
        }
        return [true,""];
    }

    /**
     * 获取用户优惠券
     * @param $company_id
     * @param $customer_id
     * @return array
     */
    public static function getCustomerCouponList($company_id,$customer_id) {
        $coupons = [];
        $nowTime = DateTimeUtils::parseStandardWLongDate();
        $couponModels = (new Query())->from(Coupon::tableName())->where([
            'and',
            [
                'customer_id'=>$customer_id,
                'status'=>Coupon::STATUS_ACTIVE,
                'company_id'=>$company_id
            ],
            // ['<=', 'start_time', $nowTime],
            ['>=', 'end_time', $nowTime],
        ])->orderBy('end_time asc')->all();
        if (!empty($couponModels)){
            foreach ($couponModels as $key => $value){
                $couponVO = [];
                $couponVO['name'] = $value['name'];
                $couponVO['start_time'] = $value['start_time'];
                $couponVO['end_time'] = $value['end_time'];
                $couponVO['startup'] = $value['startup'];
                $couponVO['discount'] = $value['discount'];
                $couponVO['coupon_type'] = $value['coupon_type'];
                $couponVO['is_remind'] = $value['is_remind'];
                $couponVO['remark'] = CouponService::generateCouponDesc($value['type'],$value['startup'],$value['discount'],$value['limit_type'],$value['limit_type_params']);
                $coupons[] = $couponVO;
            }
        }
        return $coupons;
    }


}