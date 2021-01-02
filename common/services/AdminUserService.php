<?php


namespace common\services;

use common\models\AdminUser;
use common\utils\StringUtils;
use yii\db\Query;
use common\models\Order;
use common\models\Coupon;
use common\models\CouponBatch;

class AdminUserService
{

    /**
     * @param $id
     * @param null $companyId
     * @param bool $model
     * @return array|bool|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $companyId=null, $model = false){
        $conditions = ['id' => $id];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if ($model){
            return AdminUser::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(AdminUser::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    public static function batchMask($list){
        if (StringUtils::isEmpty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::mask($v);
            $list[$k] = $v;
        }
        return $list;
    }

    public static function mask($model){
        if (StringUtils::isEmpty($model)){
            return null;
        }
        $newModel = [];
        $newModel['id'] = $model['id'];
        $newModel['username'] = $model['username'];
        $newModel['email'] = $model['email'];
        $newModel['status'] = $model['status'];
        $newModel['created_at'] = $model['created_at'];
        $newModel['updated_at'] = $model['updated_at'];
        $newModel['company_id'] = $model['company_id'];
        $newModel['nickname'] = $model['nickname'];
        $newModel['is_super_admin'] = $model['is_super_admin'];
        return $newModel;
    }
    /**
     * 判断用户对代理商 是否为新用户
     * 1，是否在代理商下过订单
     * 2，是否在代理商领过新人优惠券
     * @param $customerId
     * @param $companyId
     * @return bool
     */
    public static function userGetCouponByCompany($customerId,$companyId){
        $orders = Order::find()->where(['company_id'=>$companyId,'customer_id'=>$customerId])->count();
        $coupons= Coupon::find()->where(['company_id'=>$companyId,'draw_operator_id'=>$customerId,'coupon_type'=>CouponBatch::COUPON_NEW])->count();
        if($orders || $coupons){
            return false;
        }
        return true;
    }

}