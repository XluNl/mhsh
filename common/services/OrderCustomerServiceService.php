<?php


namespace common\services;


use common\models\Common;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceGoods;
use common\models\OrderCustomerServiceLog;
use common\utils\ArrayUtils;
use common\utils\PriceUtils;
use yii\db\Query;

class OrderCustomerServiceService
{
    public static function getModel($id,$model=false){
        $conditions = ['id'=>$id];
        if ($model){
            return OrderCustomerService::findOne($conditions);
        }
        else{
            $cusotmerService = (new Query())->from(OrderCustomerService::tableName())->where($conditions)->one();
            $cusotmerService = $cusotmerService===false?null:$cusotmerService;
            return $cusotmerService;
        }
    }

    /**
     * 计算预计赔付金额
     * @param $amount
     * @param $num_ac
     * @param $num
     * @return int
     */
    public static function calcClaimAmount($amount,$num_ac,$num){
        $amountAc = PriceUtils::accurateToTen(($num_ac*$amount)/$num);
        return  $amount - $amountAc;
    }

    /**
     * 售后订单补全商品信息
     * @param $customerServiceModels
     * @return mixed
     */
    public static function fillGoodsInfo($customerServiceModels)
    {
        if (!empty($customerServiceModels)) {
            $customerServiceIds = ArrayUtils::getColumnWithoutNull('id', $customerServiceModels);
            $customerServiceGoodsModels = OrderCustomerServiceGoods::find()
                ->with(['orderGoods'])
                ->where(['customer_service_id' => $customerServiceIds])->asArray()->all();
            foreach ($customerServiceModels as $k1 => $v1) {
                foreach ($customerServiceGoodsModels as $v2) {
                    if ($v1['id'] == $v2['customer_service_id']) {
                        if (!key_exists('goods', $v1)) {
                            $v1['goods'] = [];
                        }
                        $customerServiceModels[$k1]['goods'][] = $v2;
                    }
                }
            }
        }
        return $customerServiceModels;
    }

    /**
     * 有待处理的售后单
     * @param $orderNo
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getDealing($orderNo){
        return OrderCustomerService::find()->where([
            'order_no'=>$orderNo,
            'status'=>OrderCustomerService::STATUS_UN_DEAL
        ])->all();
    }
}