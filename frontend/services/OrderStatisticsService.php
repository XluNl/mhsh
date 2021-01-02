<?php


namespace frontend\services;


use common\models\Order;
use common\models\OrderGoods;
use common\utils\DateTimeUtils;
use yii\db\Query;

class OrderStatisticsService
{
    /**
     * 查询已购买的数量
     * @param $skuId
     * @param $customerId
     * @param $company_id
     * @param $startTime
     * @return int
     */
    public static function getBoughtNumInScheduled($skuId,$customerId,$company_id,$startTime){
        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $select = "sum({$orderGoodsTable}.num) as num";
        $query = (new Query())->from($orderGoodsTable)->select($select)->leftJoin($orderTable,"{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->where(['and',
                [   'sku_id'=>$skuId,
                    "{$orderTable}.customer_id"=>$customerId,
                    "{$orderGoodsTable}.company_id"=>$company_id,
                    'order_status'=>[Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE,Order::ORDER_STATUS_COMPLETE],
                ],
                ['>',"{$orderGoodsTable}.created_at",$startTime]
            ])->one();
        if (empty($query["num"])){
            return 0;
        }
        return $query["num"];
    }

    /**
     * 获取当日备货中的订单金额
     * @param $customerId
     * @param $company_id
     * @return int
     */
    public static function getTodayRealAmount($customerId,$company_id){
        $nowTime = time();
        $startTime = strtotime(date("Y-m-d",$nowTime));
        $startTime = DateTimeUtils::parseStandardWLongDate($startTime);
        $existOrderAmount = (new Query())->from(Order::tableName())
            ->select('sum(real_amount) as amount')->where([
                'and',
                ["customer_id" => $customerId,'company_id'=>$company_id],
                ['order_status'=>[ Order::ORDER_STATUS_PREPARE]],
                ['>=', 'created_at',$startTime]
            ])->one();
        if (empty($existOrderAmount)){
            return 0;
        }
        return $existOrderAmount['amount'];
    }
}