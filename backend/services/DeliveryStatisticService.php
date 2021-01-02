<?php


namespace backend\services;


use common\models\BizTypeEnum;
use common\models\Common;
use common\models\DistributeBalanceItem;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DeliveryStatisticService
{
    /**
     * 团长交易汇总
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getDeliveryTradeSummary($companyId, $deliveryId, $startDate, $endDate){
        $startTime = $startDate;
        $endTime = $endDate;
        $orderSummaryCondition = ['company_id'=>$companyId];
        if (StringUtils::isNotBlank($deliveryId)){
            $orderSummaryCondition['delivery_id'] = $deliveryId;
        }
        $orderSummary = (new Query())->from(Order::tableName())->select(
            [
                'COALESCE(SUM(need_amount),0)  as need_amount',
                'COUNT(DISTINCT(customer_id),0)  as customer_cnt',
            ]
        )->where([
            'and',
            $orderSummaryCondition,
            [
                'order_status'=>Order::$activeStatusArr,
                'order_owner'=>GoodsConstantEnum::OWNER_SELF
            ],
            ['between','created_at',$startTime,$endTime],
        ])->one();
        $distributeSummaryCondition=['company_id'=>$companyId];
        if ($deliveryId){
            $distributeSummaryCondition=['biz_id'=>$deliveryId];
        }
        $distributeSummary = (new Query())->from(DistributeBalanceItem::tableName())->select([
            "COALESCE(SUM(amount),0) as distribute_delivery_amount",
        ])
            ->where([
                'and',
                $distributeSummaryCondition,
                ['type'=>DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE,'biz_type'=>BizTypeEnum::BIZ_TYPE_DELIVERY],
                ['between','created_at',$startTime,$endTime]
            ])->one();

        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $grossSummaryCondition = ["{$orderGoodsTable}.company_id"=>$companyId];
        if (StringUtils::isNotBlank($deliveryId)){
            $grossSummaryCondition["{$orderGoodsTable}.delivery_id"] = $deliveryId;
        }
        $grossSummary = (new Query())->from(OrderGoods::tableName())->select([
            "COALESCE(SUM((sku_price-purchase_price)*num),0)  as gross_amount",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                $grossSummaryCondition,
                [
                    'order_status'=>Order::$activeStatusArr,
                    'order_owner'=>GoodsConstantEnum::OWNER_SELF
                ],
                ['between',"{$orderGoodsTable}.created_at",$startTime,$endTime]
            ])
            ->one();

        return [
            'need_amount'=>Common::showAmountWithYuan($orderSummary['need_amount']),
            'customer_cnt'=>$orderSummary['customer_cnt'],
            'distribute_delivery_amount'=>Common::showAmountWithYuan($distributeSummary['distribute_delivery_amount']),
            'gross_amount'=>Common::showAmountWithYuan($grossSummary['gross_amount']),
        ];

    }

    /**
     * 团长每日交易
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getDeliveryTradeDay($companyId, $deliveryId, $startDate, $endDate){
        $startTime = $startDate;
        $endTime = $endDate;
        $orderDayCondition = ['company_id'=>$companyId];
        if (StringUtils::isNotBlank($deliveryId)){
            $orderDayCondition['delivery_id'] = $deliveryId;
        }
        $orderDay = (new Query())->from(Order::tableName())->select(
            [
                'COALESCE(SUM(need_amount),0)  as need_amount',
                'COUNT(DISTINCT(customer_id),0)  as customer_cnt',
                "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
            ]
        )->where([
            'and',
            $orderDayCondition,
            ['order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF],
            ['between','created_at',$startTime,$endTime],
        ])->groupBy('time')
        ->all();


        $distributeDayCondition=['company_id'=>$companyId];
        if ($deliveryId){
            $distributeDayCondition=['biz_id'=>$deliveryId];
        }
        $distributeDay = (new Query())->from(DistributeBalanceItem::tableName())->select([
            "COALESCE(SUM(amount),0) as distribute_delivery_amount",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
        ])
            ->where([
                'and',
                $distributeDayCondition,
                ['type'=>DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE,'biz_type'=>BizTypeEnum::BIZ_TYPE_DELIVERY],
                ['between','created_at',$startTime,$endTime]
            ])->groupBy('time')
            ->all();


        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $grossSummaryCondition = ["{$orderGoodsTable}.company_id"=>$companyId];
        if (StringUtils::isNotBlank($deliveryId)){
            $grossSummaryCondition["{$orderGoodsTable}.delivery_id"] = $deliveryId;
        }
        $grossDay = (new Query())->from(OrderGoods::tableName())->select([
            "COALESCE(SUM((sku_price-purchase_price)*num),0)  as gross_amount",
            "DATE_FORMAT({$orderGoodsTable}.created_at,'%Y-%m-%d') as time"
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                $grossSummaryCondition,
                ['order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF],
                ['between',"{$orderGoodsTable}.created_at",$startTime,$endTime]
            ])
            ->groupBy('time')
            ->all();

        $items = [
            'time'=>[],
            'time_text'=>[],
            'need_amount'=>[],
            'customer_cnt'=>[],
            'distribute_delivery_amount'=>[],
            'gross_amount'=>[],
        ];
        $distributeDay = ArrayUtils::index($distributeDay,'time');
        $orderDay = ArrayUtils::index($orderDay,'time');
        $grossDay = ArrayUtils::index($grossDay,'time');
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$orderDay)){
                $items['need_amount'][]= Common::showAmount($orderDay[$time]['need_amount']);
                $items['customer_cnt'][]= $orderDay[$time]['customer_cnt'];
            }
            else{
                $items['need_amount'][]= 0;
                $items['customer_cnt'][]= 0;
            }
            if (key_exists($time,$distributeDay)){
                $items['distribute_delivery_amount'][]= Common::showAmount($distributeDay[$time]['distribute_delivery_amount']);
            }
            else{
                $items['distribute_delivery_amount'][]= 0;
            }
            if (key_exists($time,$grossDay)){
                $items['gross_amount'][]= Common::showAmount($grossDay[$time]['gross_amount']);
            }
            else{
                $items['gross_amount'][]= 0;
            }
        }
        return $items;
    }


    /**
     * 团长商品汇总
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getGoodsSummary($companyId, $deliveryId, $startDate, $endDate){
        $startTime = $startDate;
        $endTime = $endDate;
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $goodsSummaryCondition = ["{$orderGoodsTable}.company_id"=>$companyId];
        if (StringUtils::isNotBlank($deliveryId)){
            $goodsSummaryCondition["{$orderGoodsTable}.delivery_id"] = $deliveryId;
        }
        $goodsSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(num)  as num",
            "SUM(amount+discount)  as need_amount",
            "SUM((sku_price-purchase_price)*num)  as gross_amount",
            "concat(goods_name,sku_name) as goods_name",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                $goodsSummaryCondition,
                ['order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF],
                ['between', "{$orderGoodsTable}.created_at",$startTime,$endTime]
            ])
            ->groupBy('sku_id')
            ->orderBy('need_amount desc')
            ->all();
        if (!empty($goodsSummary)){
            $sumAmount = 0;
            foreach ($goodsSummary as $k=>$v){
                $sumAmount += $v['need_amount'];
            }
            foreach ($goodsSummary as $k=>$v){
                $v['percentage'] = Common::showPercentWithUnit((int)($v['need_amount']*10000/$sumAmount));
                $v['need_amount'] = Common::showAmount($v['need_amount']);
                $v['gross_amount'] = Common::showAmount($v['gross_amount']);
                $goodsSummary[$k] = $v;
            }
        }
        return $goodsSummary;
    }

}