<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\models\constants\AllianceStatisticConstants;
use common\models\Alliance;
use common\models\Common;
use common\models\CommonStatus;
use common\models\Customer;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class AllianceStatisticService
{
    /**
     * 联盟下单用户数&总用户数
     * 有单子的联盟数&总联盟数
     * @param $companyId
     * @return array
     */
    public static function headerSummary($companyId)
    {
        $customerCount = (new Query())->from(Customer::tableName())->where(['status' => CommonStatus::STATUS_ACTIVE])->count();
        $deliveryCount = (new Query())->from(Delivery::tableName())->where(['company_id' => $companyId, 'status' => CommonStatus::STATUS_ACTIVE])->count();
        $allianceCount = (new Query())->from(Alliance::tableName())->where(['company_id' => $companyId])->count();
        $orderStatistic = Order::find()->where(['order_status' => Order::$activeStatusArr, 'company_id' => $companyId, 'order_owner' => GoodsConstantEnum::OWNER_HA])
            ->select([
                "COUNT(DISTINCT(customer_id)) as customerOrderCount",
                "COUNT(DISTINCT(order_owner_id)) as allianceOrderCount",
                "COUNT(DISTINCT(order_owner_id)) as allianceOrderCount",
                "COALESCE(SUM(goods_num),0) as orderGoodsSum",
                "COALESCE(SUM(real_amount),0) as orderAmount",
            ])->asArray()->one();

        $orderGoodsTable = OrderGoods::tableName();
        $orderTable = Order::tableName();
        $orderGoodsStatistic = OrderGoods::find()
            ->leftJoin($orderTable, "{$orderGoodsTable}.order_no={$orderTable}.order_no")
            ->where(['order_status' => Order::$activeStatusArr, "{$orderTable}.company_id" => $companyId, 'order_owner' => GoodsConstantEnum::OWNER_HA])
            ->select([
                "COUNT(DISTINCT(sku_id)) as orderGoodsCount",
            ])->asArray()->one();
        return [
            'customerCount' => $customerCount,
            'deliveryCount' => $deliveryCount,
            'customerOrderCount' => $orderStatistic['customerOrderCount'],

            'allianceCount' => $allianceCount,
            'allianceOrderCount' => $orderStatistic['allianceOrderCount'],

            'orderGoodsSum' => $orderStatistic['orderGoodsSum'],
            'orderGoodsCount' => $orderGoodsStatistic['orderGoodsCount'],

            'orderAmount' => Common::showAmount($orderStatistic['orderAmount']),
        ];
    }

    /**
     * 订单每日统计数据
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getOrderSummaryEveryDay($companyId, $startDate, $endDate)
    {
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderDay = (new Query())->from(Order::tableName())->select([
            "COALESCE(SUM(need_amount),0)  as need_amount",
            "COUNT(DISTINCT(delivery_id))  as delivery_count",
            "COALESCE(SUM(discount_amount),0)  as discount_amount",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
        ])
            ->where([
                'and',
                ['company_id' => $companyId],
                ['order_owner' => GoodsConstantEnum::OWNER_HA],
                ['order_status' => Order::$activeStatusArr],
                ['between', 'created_at', $startTime, $endTime]
            ])
            ->groupBy('time')
            ->all();
        $orderDay = ArrayUtils::index($orderDay, 'time');

        $orderRefundDay = (new Query())->from(Order::tableName())->select([
            "COALESCE(SUM(need_amount),0)  as refund_amount",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
        ])
            ->where([
                'and',
                ['company_id' => $companyId],
                ['order_owner' => GoodsConstantEnum::OWNER_HA],
                ['order_status' => Order::ORDER_STATUS_CANCELED],
                ['pay_status' => Order::PAY_STATUS_PAYED_ALL],
                ['between', 'created_at', $startTime, $endTime]
            ])
            ->groupBy('time')
            ->all();
        $orderRefundDay = ArrayUtils::index($orderRefundDay, 'time');

        $items = [
            'time' => [],
            'time_text' => [],
            'delivery_count' => [],
            'order_count' => [],
            'discount_amount' => [],
            'refund_amount' => [],
        ];
        for ($time = $startDate; strtotime($time) <= strtotime($endDate); $time = DateTimeUtils::plusDay($time, 1)) {
            $items['time'][] = $time;
            $items['time_text'][] = $time;
            if (key_exists($time, $orderDay)) {
                $items['need_amount'][] = Common::showAmount($orderDay[$time]['need_amount']);
                $items['delivery_count'][] = (int)$orderDay[$time]['delivery_count'];
                $items['discount_amount'][] = Common::showAmount($orderDay[$time]['discount_amount']);
            } else {
                $items['need_amount'][] = 0;
                $items['delivery_count'][] = 0;
                $items['discount_amount'][] = 0;
            }
            if (key_exists($time, $orderRefundDay)) {
                $items['refund_amount'][] = Common::showAmount($orderRefundDay[$time]['refund_amount']);
            } else {
                $items['refund_amount'][] = 0;
            }
        }
        return $items;
    }

    /**
     * 分类销售占比
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getSortSummary($companyId, $startDate, $endDate)
    {
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $sortSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(amount+discount)  as need_amount",
            "sort_1",
        ])
            ->leftJoin(Order::tableName(), "{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                [
                    "{$orderGoodsTable}.company_id" => $companyId,
                    'order_status' => Order::$activeStatusArr,
                    "{$orderTable}.order_owner" => GoodsConstantEnum::OWNER_HA,
                ],
                ['between', "{$orderGoodsTable}.created_at", $startTime, $endTime]
            ]);

        $sortSummary = $sortSummary->groupBy('sort_1')
            ->all();
        GoodsSortService::completeSortName($sortSummary);
        $items = [
            'legendData' => [],
            'seriesData' => [],
            'selected' => [],
        ];
        foreach ($sortSummary as $v) {
            $items['legendData'][] = $v['sort_1_name'];
            $items['seriesData'][] = [
                'name' => $v['sort_1_name'],
                'value' => Common::showAmount($v['need_amount']),
            ];
            $items['selected'][$v['sort_1_name']] = true;
        }
        return $items;
    }

    public static function getSort2Summary($companyId, $startDate, $endDate)
    {
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $sortSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(amount+discount)  as need_amount",
            "sort_2",
        ])
            ->leftJoin(Order::tableName(), "{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                [
                    "{$orderGoodsTable}.company_id" => $companyId,
                    'order_status' => Order::$activeStatusArr,
                    "{$orderTable}.order_owner" => GoodsConstantEnum::OWNER_HA,
                ],
                ['between', "{$orderGoodsTable}.created_at", $startTime, $endTime]
            ]);

        $sortSummary = $sortSummary->groupBy('sort_2')
            ->all();
        GoodsSortService::completeSortName($sortSummary);
        $items = [
            'legendData' => [],
            'seriesData' => [],
            'selected' => [],
        ];
        foreach ($sortSummary as $v) {
            $items['legendData'][] = $v['sort_2_name'];
            $items['seriesData'][] = [
                'name' => $v['sort_2_name'],
                'value' => Common::showAmount($v['need_amount']),
            ];
            $items['selected'][$v['sort_2_name']] = true;
        }
        return $items;
    }


    /**
     * 商品销量统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getGoodsSummary($companyId, $startDate, $endDate)
    {
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $goodsSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(num)  as num",
            "concat(goods_name,sku_name) as goods_name",
        ])
            ->leftJoin(Order::tableName(), "{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                ["{$orderGoodsTable}.company_id" => $companyId, 'order_status' => Order::$activeStatusArr, "{$orderTable}.order_owner" => GoodsConstantEnum::OWNER_HA],
                ['between', "{$orderGoodsTable}.created_at", $startTime, $endTime]
            ])
            ->groupBy('sku_id')
            ->orderBy('num desc')
            ->all();
        if (!empty($goodsSummary)) {
            $sumNum = 0;
            foreach ($goodsSummary as $k => $v) {
                $sumNum += $v['num'];
            }
            foreach ($goodsSummary as $k => $v) {
                $v['percentage'] = Common::showPercentWithUnit((int)($v['num'] * 10000 / $sumNum));
                $goodsSummary[$k] = $v;
            }
        }
        return $goodsSummary;
    }

    /**
     * 联盟点订单金额统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getAllianceSummary($companyId, $startDate, $endDate)
    {
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $deliverySummary = (new Query())->from(Order::tableName())->select([
            "SUM(need_amount)  as amount",
            "delivery_nickname as alliance_name",
        ]);

        $deliverySummary = $deliverySummary->where([
            'and',
            ['company_id' => $companyId, 'order_status' => Order::$activeStatusArr, 'order_owner' => GoodsConstantEnum::OWNER_HA],
            ['between', 'created_at', $startTime, $endTime]
        ]);

        $deliverySummary = $deliverySummary->groupBy('order_owner_id')
            ->orderBy('amount desc')
            ->all();
        if (!empty($deliverySummary)) {
            $sumAmount = 0;
            foreach ($deliverySummary as $k => $v) {
                $sumAmount += $v['amount'];
            }
            foreach ($deliverySummary as $k => $v) {
                $v['percentage'] = Common::showPercentWithUnit((int)($v['amount'] * 10000 / $sumAmount));
                $v['amount'] = Common::showAmountWithYuan($v['amount']);
                $deliverySummary[$k] = $v;
            }
        }
        return $deliverySummary;
    }


    public static function downloadDashboard($companyId, $startDate, $endDate)
    {
        /*交易走势*/
        $orderSummaryEveryDay = self::getOrderSummaryEveryDay($companyId, $startDate, $endDate);
        //self::mulData("actionOrderDay",$orderSummaryEveryDay,$companyId);

        $orderSummaryEveryDayData = [];
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData, 'time_text', $orderSummaryEveryDay['time_text']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData, 'need_amount', $orderSummaryEveryDay['need_amount']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData, 'delivery_count', $orderSummaryEveryDay['delivery_count']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData, 'discount_amount', $orderSummaryEveryDay['discount_amount']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData, 'refund_amount', $orderSummaryEveryDay['refund_amount']);
        $orderSummaryEveryDaySheetData = ['mainTitle' => '交易走势', 'headers' => AllianceStatisticConstants::$orderSummaryEveryDayHeader, 'rows' => $orderSummaryEveryDayData];

        /*商品销售统计*/
        $goodsSummary = self::getGoodsSummary($companyId, $startDate, $endDate);
        //self::mulData("actionGoodsSummary",$goodsSummary,$companyId);

        $goodsSummarySheetData = ['mainTitle' => '商品销售统计', 'headers' => AllianceStatisticConstants::$goodsSummaryHeader, 'rows' => $goodsSummary];

        /*联盟点金额统计*/
        $allianceSummary = self::getAllianceSummary($companyId, $startDate, $endDate);
        //self::mulData("actionDeliverySummary",$deliverySummary,$companyId);
        $deliverySummarySheetData = ['mainTitle' => '联盟点订单金额统计', 'headers' => AllianceStatisticConstants::$deliverySummaryHeader, 'rows' => $allianceSummary];

        /*销售额类别占比*/
        $sortSummary = self::getSortSummary($companyId, $startDate, $endDate);
        $sortSummaryData = [];
        if (!empty($sortSummary['seriesData'])) {
            $sortNameArr = ArrayHelper::getColumn($sortSummary['seriesData'], 'name', false);
            $sortSummaryData = ArrayUtils::mergeMap($sortSummaryData, 'sort_name', $sortNameArr);
            $sortAmountArr = ArrayHelper::getColumn($sortSummary['seriesData'], 'value', false);
            $sortSummaryData = ArrayUtils::mergeMap($sortSummaryData, 'sort_amount', $sortAmountArr);
            $proportionArr = ArrayUtils::calculateArrayProportion($sortAmountArr, 10000);
            $proportionArr = ArrayUtils::divideArray($proportionArr, 10000);
            $sortSummaryData = ArrayUtils::mergeMap($sortSummaryData, 'percentage', $proportionArr);
        }
        $sortSummarySheetData = ['mainTitle' => '销售额类别占比', 'headers' => AllianceStatisticConstants::$sortSummaryHeader, 'rows' => $sortSummaryData];


        SimpleDownloadService::multipleDownload('联盟控制台数据', [
            $orderSummaryEveryDaySheetData,
            $goodsSummarySheetData,
            $deliverySummarySheetData,
            $sortSummarySheetData,
        ]);
    }


    public static function mulData($method, &$res, $companyId)
    {
        if ($companyId != '1') {
            return;
        }
        if ($method == 'actionSummary') {
            $res['delivery']['allCount'] *= 2;
            $res['delivery']['popularizerCount'] *= 2;
            $res['delivery']['deliveryCount'] *= 2;
            $res['delivery']['allianceCount'] *= 2;

            $res['order_goods'] *= 8;

            $res['customer']['customerCount'] *= 8;
            $res['customer']['customerOrderCount'] *= 8;

            $res['order_need_amount'] *= 8;
        } else if ($method == 'actionDeliveryDay') {
            foreach ($res['delivery_cnt'] as $k => $v) {
                $res['delivery_cnt'][$k] *= 8;
            }
            foreach ($res['popularizer_cnt'] as $k => $v) {
                $res['popularizer_cnt'][$k] *= 8;
            }
            foreach ($res['distribute_delivery_amount'] as $k => $v) {
                $res['distribute_delivery_amount'][$k] *= 8;
            }
            foreach ($res['distribute_popularizer_amount'] as $k => $v) {
                $res['distribute_popularizer_amount'][$k] *= 8;
            }
        } else if ($method == 'actionOrderDay') {
            foreach ($res['need_amount'] as $k => $v) {
                $res['need_amount'][$k] *= 8;
            }
            foreach ($res['delivery_count'] as $k => $v) {
                $res['delivery_count'][$k] *= 8;
            }
            foreach ($res['discount_amount'] as $k => $v) {
                $res['discount_amount'][$k] *= 8;
            }
            foreach ($res['refund_amount'] as $k => $v) {
                $res['refund_amount'][$k] *= 8;
            }
        } else if ($method == 'actionSortSummary') {
            foreach ($res['seriesData'] as $k => $v) {
                $res['seriesData'][$k]['value'] *= 8;
            }
        } else if ($method == 'actionGoodsSummary') {
            foreach ($res as $k => $v) {
                $res[$k]['num'] *= 8;
            }
        } else if ($method == 'actionDeliverySummary') {
            foreach ($res as $k => $v) {
                $res[$k]['amount'] = BackendCommon::multiplyWithYuan($v['amount'], 8);
            }
        } else if ($method == 'actionUserInfoSummary') {

        } else if ($method == 'actionOrderDeliveryDay') {
            foreach ($res['customer_count'] as $k => $v) {
                $res['customer_count'][$k] *= 8;
            }
            foreach ($res['delivery_count'] as $k => $v) {
                $res['delivery_count'][$k] *= 2;
            }
            foreach ($res['popularizer_count'] as $k => $v) {
                $res['popularizer_count'][$k] *= 2;
            }
        }

    }
}