<?php


namespace business\services;


use common\models\Common;
use common\models\Customer;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\UserInfo;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\PhoneUtils;
use common\utils\StringUtils;
use yii\db\Query;

class OrderStatisticService
{
    /**
     * 分类销售占比
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getSortSummary($companyId,$deliveryId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $sortSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(num)  as num",
            "sort_1",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                [
                    "{$orderGoodsTable}.company_id"=>$companyId,
                    'order_status'=>Order::$activeStatusArr,
                    "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_DELIVERY,
                    "{$orderTable}.order_owner_id"=>$deliveryId,
                ],
                ['between',"{$orderGoodsTable}.created_at",$startTime,$endTime]
            ])
            ->groupBy('sort_1')
            ->all();
        GoodsSortService::completeSortName($sortSummary);


        if (!empty($sortSummary)){
            $sumNum = 0;
            foreach ($sortSummary as $k=>$v){
                $sumNum += $v['num'];
            }
            foreach ($sortSummary as $k=>$v){
                $v['percentage'] = Common::showPercentWithUnit((int)($v['num']*10000/$sumNum));
                $sortSummary[$k] = $v;
            }
            $pieNum = 5;
            if (count($sortSummary)<=$pieNum){
                return $sortSummary;
            }
            $reStatisticGoodsSummary = array_slice($sortSummary,0,$pieNum-1);
            $headNum = ArrayUtils::subValueAdd($sortSummary,'num');
            $reStatisticGoodsSummary[] = [
                'num'=>$sumNum-$headNum,
                'percentage' => Common::showPercentWithUnit((int)(($sumNum-$headNum)*10000/$sumNum)),
                'goods_name'=>'其他',
            ];
            return  $reStatisticGoodsSummary;
        }
        return $sortSummary;
    }

    /**
     * 商品销售统计
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @param null $bigSortId
     * @return array
     */
    public static function getGoodsSummary($companyId,$deliveryId,$startDate,$endDate,$bigSortId=null){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $conditions = [
            'and',
            [
                "{$orderGoodsTable}.company_id"=>$companyId,
                'order_status'=>Order::$activeStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_DELIVERY,
                "{$orderTable}.order_owner_id"=>$deliveryId,
            ],
            ['between', "{$orderGoodsTable}.created_at",$startTime,$endTime]
        ];
        if (StringUtils::isNotBlank($bigSortId)){
            $conditions[] = ["{$orderGoodsTable}.sort_1"=>$bigSortId];
        }
        $goodsSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(num)  as num",
            "concat(goods_name,sku_name) as goods_name",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where($conditions)
            ->groupBy('sku_id')
            ->orderBy('num desc')
            ->all();

        if (!empty($goodsSummary)){
            $sumNum = 0;
            foreach ($goodsSummary as $k=>$v){
                $sumNum += $v['num'];
            }
            foreach ($goodsSummary as $k=>$v){
                $v['percentage'] = Common::showPercentWithUnit((int)($v['num']*10000/$sumNum));
                $goodsSummary[$k] = $v;
            }
            $pieNum = 5;
            if (count($goodsSummary)<=$pieNum){
                return $goodsSummary;
            }
            $reStatisticGoodsSummary = array_slice($goodsSummary,0,$pieNum-1);
            $headNum = ArrayUtils::subValueAdd($goodsSummary,'num');
            $reStatisticGoodsSummary[] = [
                'num'=>$sumNum-$headNum,
                'percentage' => Common::showPercentWithUnit((int)(($sumNum-$headNum)*10000/$sumNum)),
                'goods_name'=>'其他',
            ];
            return  $reStatisticGoodsSummary;
        }
        return $goodsSummary;
    }


    /**
     * 订单每日统计数据
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getOrderSummaryEveryDay($companyId,$deliveryId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $customerServiceStatus = Order::CUSTOMER_SERVICE_STATUS_TRUE;
        $orderDay = (new Query())->from(Order::tableName())->select([
            "COALESCE(SUM(need_amount),0)  as need_amount",
            "COUNT(1)  as order_count",
            "COALESCE(SUM(discount_amount),0)  as discount_amount",
            "SUM(case when customer_service_status = {$customerServiceStatus}  then 1 else 0 end) as customer_service_count",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
        ])
            ->where([
                'and',
                ['company_id'=>$companyId],
                [
                    'order_status'=>Order::$activeStatusArr,
                    "order_owner"=>GoodsConstantEnum::OWNER_DELIVERY,
                    "order_owner_id"=>$deliveryId,
                ],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->all();
        $orderDay = ArrayUtils::index($orderDay,'time');
        $items = [];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $item = [];
            $item['time']=$time;
            $item['time_text']=$time;
            if (key_exists($time,$orderDay)){
                $item['need_amount']= Common::showAmount($orderDay[$time]['need_amount']);
                $item['order_count']=(int)$orderDay[$time]['order_count'];
                $item['discount_amount']= Common::showAmount($orderDay[$time]['discount_amount']);
                $item['customer_service_count']= (int)$orderDay[$time]['customer_service_count'];
            }
            else{
                $item['need_amount']= 0;
                $item['order_count']= 0;
                $item['discount_amount']= 0;
                $item['customer_service_count']= 0;
            }
            $items[] = $item;
        }
        return $items;
    }


    /**
     * 统计粉丝信息
     * @param $companyId
     * @param $deliveryId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getFansSummary($companyId,$deliveryId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $customerTable = Customer::tableName();
        $userInfoTable = UserInfo::tableName();
        $conditions = [
            'and',
            [
                "{$orderTable}.company_id"=>$companyId,
                'order_status'=>Order::$activeStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_DELIVERY,
                "{$orderTable}.order_owner_id"=>$deliveryId,
            ],
            ['between', "{$orderTable}.created_at",$startTime,$endTime]
        ];
        $fansSummary = (new Query())->from(Order::tableName())->select([
            "customer_id",
            "{$customerTable}.phone",
            "{$customerTable}.nickname",
            "MIN({$orderTable}.created_at) AS join_time",
            "head_img_url",
            "COUNT(DISTINCT(order_no))  as order_count",
            "SUM(goods_num)  as goods_num",
            "SUM(need_amount)  as need_amount",
        ])
            ->leftJoin(Customer::tableName(),"{$orderTable}.customer_id={$customerTable}.id")
            ->leftJoin(UserInfo::tableName(),"{$userInfoTable}.id={$customerTable}.user_id")
            ->where($conditions)
            ->groupBy('customer_id')
            ->orderBy('order_count desc')
            ->all();
        $fansSummary = GoodsDisplayDomainService::batchRenameImageUrl($fansSummary,'head_img_url');
        PhoneUtils::batchReplacePhoneMark($fansSummary,'phone');
        return $fansSummary;
    }

}