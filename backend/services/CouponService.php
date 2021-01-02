<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\Coupon;
use common\models\Customer;
use common\models\Order;
use common\utils\DateTimeUtils;
use common\utils\ModelUtils;
use common\utils\StringUtils;
use yii\base\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class CouponService extends \common\services\CouponService
{
    /**
     * 解析订单优惠详情
     * @param $discountDetail
     * @return string
     */
    public static function decodeOrderDiscountDetail($discountDetail){
        if (empty($discountDetail)){
            return "无优惠信息";
        }
        try {
            $obj = Json::decode(($discountDetail));
            $result = "";
            foreach ($obj as $value){
                if ($value['type']==Order::DISCOUNT_TYPE_COUPON){
                    $result = $result."{$value['desc']}{$value['code']}\n";
                }
            }
            if (StringUtils::isBlank($result)){
                return '无优惠信息';
            }
            return $result;
        } catch (Exception $e) {
            return '无优惠信息';
        }
    }

    /**
     * 补全用户信息
     * @param $dataProvider
     * @return mixed
     */
    public static function completeUsedInfo($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        $customerIds = ModelUtils::getColFromModels($models,'customer_id');
        if (empty($customerIds)){
            return $dataProvider;
        }
        $customerModels = (new Query())->from(Customer::tableName())
            ->select(['id','nickname','phone'])
            ->where(['id'=>$customerIds])
            ->all();
        $customerModels = empty($customerModels)?[]:ArrayHelper::index($customerModels,'id');
        foreach ($models as $k=>$v){
            if (key_exists($v['customer_id'],$customerModels)){
                $customerModel = $customerModels[$v['customer_id']];
                $v->customer_name =$customerModel['nickname'];
                $v->customer_phone =$customerModel['phone'];
            }
            else{
                $v->customer_name = $v['customer_id'];
            }
            $models[$k]= $v;
        }
        $dataProvider->setModels($models);
        return $dataProvider;
    }


    /**
     * 作废优惠券
     * @param $id
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $validateException RedirectParams
     */
    public static function discard($id,$company_id,$operatorId,$operatorName,$validateException){
        $count = Coupon::updateAll([
            'status'=>Coupon::STATUS_DISCARD,
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(time()),
            'remark'=>"{$operatorName}({$operatorId})作废优惠券",
        ],['id'=>$id,'company_id'=>$company_id,'status'=>Coupon::STATUS_ACTIVE]);
        BExceptionAssert::assertTrue($count>0,$validateException->updateMessage("更新失败"));
    }



}