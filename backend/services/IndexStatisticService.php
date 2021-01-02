<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\models\constants\IndexStatisticConstants;
use common\models\Alliance;
use common\models\BizTypeEnum;
use common\models\Common;
use common\models\CommonStatus;
use common\models\CustomerCompany;
use common\models\Delivery;
use common\models\DistributeBalanceItem;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsScheduleCollection;
use common\models\Order;
use common\models\OrderCustomerService;
use common\models\OrderGoods;
use common\models\OrderPayRefund;
use common\models\Popularizer;
use common\models\User;
use common\models\UserInfo;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class IndexStatisticService
{
    /**
     * 团长数量（分享&配送团长&联盟商户）
     * @param $companyId
     * @return array
     */
    public static function deliverySummary($companyId){
        $popularizerCount = (new Query())->from(Popularizer::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE])->count();
        $deliveryCount =  (new Query())->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE])->count();
        $allianceCount =  (new Query())->from(Alliance::tableName())->where(['company_id'=>$companyId])->count();
        return [
            'allCount'=>$popularizerCount+$deliveryCount+$allianceCount,
            'popularizerCount'=>$popularizerCount,
            'deliveryCount'=>$deliveryCount,
            'allianceCount'=>$allianceCount,
        ];
    }

    /**
     * 合伙人数量（合伙人&团长总数&未认证团长总数）
     * @param $companyId
     * @return array
     */
    public static function partnerSummary($companyId){
        $partnerCount =  (new Query())->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH])->count();
        $deliveryCount =  (new Query())->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE])->count();
        return [
            'partnerCount'=>$partnerCount,
            'deliveryCount'=>$deliveryCount,
            'unAuthCount'=>$deliveryCount-$partnerCount,
        ];
    }

    /**
     * 合伙人粉丝数量（总数&当日）
     * @param $companyId
     * @return array
     */
    public static function partnerFansSummary($companyId){
        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $userCount = (new Query())->from(User::tableName())->where(['delivery_id' => $partner])->count();

        $nowUserCount = (new Query())->from(User::tableName())->where(['delivery_id' => $partner])->andWhere(['>=', 'created_at', strtotime(date('Y-m-d'))])->count();

        return [
            'userCount'=>$userCount,
            'nowUserCount'=>$nowUserCount,
        ];
    }

    /**
     * 合伙人商品数量（数量&总数&当日）
     * @param $companyId
     * @return array
     */
    public static function partnerGoodsSummary($companyId){

        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $partnerGoodsCount = (new Query())->from(Goods::tableName())->where(['company_id'=>$companyId, 'goods_status'=>GoodsConstantEnum::STATUS_UP, 'goods_owner'=>GoodsConstantEnum::OWNER_DELIVERY])->andWhere(['goods_owner_id'=>$partner])->count();

        $GoodsCount = (new Query())->from(Goods::tableName())->where(['company_id'=>$companyId, 'goods_status'=>GoodsConstantEnum::STATUS_UP])->count();

        $partnerNewCount = (new Query())->from(Goods::tableName())->where(['company_id'=>$companyId, 'goods_status'=>GoodsConstantEnum::STATUS_ACTIVE, 'goods_owner'=>GoodsConstantEnum::OWNER_DELIVERY])->andWhere(['goods_owner_id'=>$partner])->count();

        return [
            'partnerGoodsCount'=>$partnerGoodsCount,
            'GoodsCount'=>$GoodsCount,
            'partnerNewCount'=>$partnerNewCount,
        ];
    }

    /**
     * 合伙人订单金额数量
     * @param $companyId
     * @return float|int
     */
    public static function partnerOrderSummary($companyId){

        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $realPriceAmount = (new Query())->from(Order::tableName())->select(["COALESCE(SUM(need_amount),0)  as need_amount"])
            ->where([
                'company_id'=>$companyId,
                'order_owner'=>GoodsConstantEnum::OWNER_DELIVERY,
                'order_status'=>Order::ORDER_STATUS_COMPLETE
            ])
            ->andWhere(['delivery_id'=>$partner])
            ->one();
        return Common::showAmount($realPriceAmount['need_amount']);
    }

    /**
     * 商品销量汇总
     * @param $companyId
     * @return mixed
     */
    public static function orderGoodsSummary($companyId){
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $orderGoodsSum = (new Query())->from(OrderGoods::tableName())
            ->select(["COALESCE(SUM(num),0)  as cnt"])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                "{$orderGoodsTable}.company_id"=>$companyId,
                "{$orderTable}.order_status"=>Order::$activeStatusArr
            ])->one();
        return $orderGoodsSum['cnt'];
    }

    /**
     * 总用户数和已下单用户数量
     * @param $companyId
     * @return array
     */
    public static function customerSummary($companyId){
        // 用户区分代理商
        $query = CustomerCompany::find();
        $query->alias('c');
        $query->select('i.nickname,i.lat,i.lng');
        $query->join('LEFT JOIN', User::tableName() . ' as u', 'c.user_id = u.id');
        $query->join('LEFT JOIN', UserInfo::tableName() . ' as i', 'i.id = u.user_info_id');
        $query->andWhere(['not', ['u.user_info_id' => null]]);
        if (BackendCommon::isNotSuperCompany($companyId)){
            $query->andWhere(['c.company_id' => $companyId]);
        }
        $customerCount = $query->count();

        $customerOrderCount = (new Query())->from(Order::tableName())->select(["COUNT(DISTINCT(customer_id))  as cnt"])->where(['company_id'=>$companyId])->one();
        return [
            'customerCount'=>$customerCount,
            'customerOrderCount'=>$customerOrderCount['cnt']
        ];
    }

    /**
     * 累计销售额
     * @param $companyId
     * @return mixed
     */
    public static function orderNeedAmountSummary($companyId){
        $realPriceAmount = (new Query())->from(Order::tableName())->select(["COALESCE(SUM(need_amount),0)  as need_amount"])
            ->where([
                'company_id'=>$companyId,
                'order_status'=>Order::$activeStatusArr
            ])
            ->one();
        return Common::showAmount($realPriceAmount['need_amount']);
    }

    /**
     * 合伙人走势
     * @param $companyId
     * @param $startDate
     * @param $endDate
     */
    public static function getPartnerTransactionSummaryEveryDay($companyId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));

        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $customerOrderDay = (new Query())->from(Order::tableName())->select([
            "SUM(need_amount)  as deal_amount",
            "SUM(discount_amount)  as discount_amount",
            "DATE_FORMAT(pay_time,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId, 'pay_status'=>Order::PAY_STATUS_PAYED_ALL],
                ['between','pay_time',$startTime,$endTime]
            ])
            ->andWhere(['not',['delivery_id' => NULL]])
            ->andWhere(['delivery_id' => $partner])
            ->andWhere(['order_owner' => GoodsConstantEnum::OWNER_DELIVERY])
            ->groupBy('time')
            ->all();

        $customerOrderDay = empty($customerOrderDay)?[]:ArrayHelper::index($customerOrderDay,'time');

        $refundOrderDay = (new Query())->from(Order::tableName().' o')->select([
            "SUM(r.refund_fee)  as refund_amount",
            "DATE_FORMAT(r.created_at,'%Y-%m-%d') as time"])
            ->join('LEFT JOIN', OrderPayRefund::tableName() . ' as r', 'o.order_no = r.out_trade_no')
            ->where(['between','r.created_at',$startTime,$endTime])
            ->andWhere(['not',['o.delivery_id' => NULL, 'r.refund_fee' => NULL]])
            ->andWhere(['o.delivery_id' => $partner])
            ->andWhere(['o.order_owner' => GoodsConstantEnum::OWNER_DELIVERY])
            ->groupBy('time')
            ->all();

        $refundOrderDay = empty($refundOrderDay)?[]:ArrayHelper::index($refundOrderDay,'time');

        $items = [
            'time'=>[],
            'deal_amount'=>[],
            'discount_amount'=>[],
            'refund_amount'=>[],
        ];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$customerOrderDay)){
                $items['deal_amount'][]=Common::showAmount($customerOrderDay[$time]['deal_amount']);
                $items['discount_amount'][]=Common::showAmount($customerOrderDay[$time]['discount_amount']);
            }else{
                $items['deal_amount'][]= 0;
                $items['discount_amount'][]= 0;
            }
            if (key_exists($time,$refundOrderDay)){
                $items['refund_amount'][]=Common::showAmount($refundOrderDay[$time]['refund_amount']);
            }else{
                $items['refund_amount'][]= 0;
            }
        }
        return $items;
    }

    /**
     * 合伙人订单统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array[]
     */
    public static function getPartnerOrderTransactionSummaryEveryDay($companyId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));

        $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

        $countOrderDay = (new Query())->from(Order::tableName())->select([
            "count(*)  as count_order",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','created_at',$startTime,$endTime]])
            ->andWhere(['not',['delivery_id' => NULL]])
            ->andWhere(['delivery_id' => $partner])
            ->andWhere(['order_owner' => GoodsConstantEnum::OWNER_DELIVERY])
            ->groupBy('time')
            ->all();
        $countOrderDay = empty($countOrderDay)?[]:ArrayHelper::index($countOrderDay,'time');

        $countPayOrderDay = (new Query())->from(Order::tableName())->select([
            "count(*)  as count_order",
            "DATE_FORMAT(pay_time,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','pay_time',$startTime,$endTime]])
            ->andWhere(['not',['delivery_id' => NULL]])
            ->andWhere(['delivery_id' => $partner])
            ->andWhere(['order_owner' => GoodsConstantEnum::OWNER_DELIVERY])
            ->groupBy('time')
            ->all();
        $countPayOrderDay = empty($countPayOrderDay)?[]:ArrayHelper::index($countPayOrderDay,'time');

        $countCompletionOrderDay = (new Query())->from(Order::tableName())->select([
            "count(*)  as count_order",
            "DATE_FORMAT(completion_time,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','completion_time',$startTime,$endTime]])
            ->andWhere(['not',['delivery_id' => NULL]])
            ->andWhere(['delivery_id' => $partner])
            ->andWhere(['order_owner' => GoodsConstantEnum::OWNER_DELIVERY])
            ->groupBy('time')
            ->all();
        $countCompletionOrderDay = empty($countCompletionOrderDay)?[]:ArrayHelper::index($countCompletionOrderDay,'time');

        $countCustomerServiceDay = (new Query())->from(OrderCustomerService::tableName())->select([
            "count(*)  as count_customer_service",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','created_at',$startTime,$endTime]])
            ->andWhere(['not',['delivery_id' => NULL]])
            ->andWhere(['delivery_id' => $partner])
            ->andWhere(['audit_level' => OrderCustomerService::AUDIT_LEVEL_DELIVERY_OR_ALLIANCE])
            ->groupBy('time')
            ->all();
        $countCustomerServiceDay = empty($countCustomerServiceDay)?[]:ArrayHelper::index($countCustomerServiceDay,'time');

        $items = [
            'time'=>[],
            'count_order'=>[],
            'count_pay_order'=>[],
            'count_completion_order'=>[],
            'count_customer_service'=>[],
        ];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$countOrderDay)){
                $items['count_order'][]=(int)$countOrderDay[$time]['count_order'];
            }else{
                $items['count_order'][]= 0;
            }
            if (key_exists($time,$countPayOrderDay)){
                $items['count_pay_order'][]=(int)$countPayOrderDay[$time]['count_pay_order'];
            }else{
                $items['count_pay_order'][]= 0;
            }
            if (key_exists($time,$countCompletionOrderDay)){
                $items['count_completion_order'][]=(int)$countCompletionOrderDay[$time]['count_completion_order'];
            }else{
                $items['count_completion_order'][]= 0;
            }
            if (key_exists($time,$countCustomerServiceDay)){
                $items['count_customer_service'][]=(int)$countCustomerServiceDay[$time]['count_customer_service'];
            }else{
                $items['count_customer_service'][]= 0;
            }
        }
        return $items;
    }

    /**
     * 团长数据 按日统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getDeliverySummaryEveryDay($companyId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $customerOrderDay = (new Query())->from(Order::tableName())->select([
            "COUNT(DISTINCT(delivery_id))  as delivery_cnt",
            "COUNT(DISTINCT(share_rate_id_1))  as popularizer_cnt",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"])
            ->where([
                'and',
                ['company_id'=>$companyId,'order_owner'=>GoodsConstantEnum::OWNER_SELF],
                ['order_status'=>Order::$activeStatusArr],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->all();
        $customerOrderDay = empty($customerOrderDay)?[]:ArrayHelper::index($customerOrderDay,'time');
        $bizTypeDelivery = BizTypeEnum::BIZ_TYPE_DELIVERY;
        $bizTypePopularizer = BizTypeEnum::BIZ_TYPE_POPULARIZER;
        $distributeDay = (new Query())->from(DistributeBalanceItem::tableName())->select([
            "SUM(case when biz_type = {$bizTypeDelivery}  then amount else 0 end) as distribute_delivery_amount",
            "SUM(case when biz_type = {$bizTypePopularizer}  then amount else 0 end) as distribute_popularizer_amount",
            "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
        ])
            ->where([
                'and',
                ['company_id'=>$companyId,'type'=>DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE],
                ['between','created_at',$startTime,$endTime]
            ])->groupBy('time')
            ->all();
        $distributeDay = empty($distributeDay)?[]:ArrayHelper::index($distributeDay,'time');
        $items = [
            'time'=>[],
            'time_text'=>[],
            'delivery_cnt'=>[],
            'popularizer_cnt'=>[],
            'distribute_delivery_amount'=>[],
            'distribute_popularizer_amount'=>[],
        ];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$customerOrderDay)){
                $items['delivery_cnt'][]=(int)$customerOrderDay[$time]['delivery_cnt'];
                $items['popularizer_cnt'][]=(int)$customerOrderDay[$time]['popularizer_cnt'];
            }
            else{
                $items['delivery_cnt'][]= 0;
                $items['popularizer_cnt'][]= 0;
            }
            if (key_exists($time,$distributeDay)){
                $items['distribute_delivery_amount'][]=Common::showAmount($distributeDay[$time]['distribute_delivery_amount']);
                $items['distribute_popularizer_amount'][]=Common::showAmount($distributeDay[$time]['distribute_popularizer_amount']);
            }
            else{
                $items['distribute_delivery_amount'][]= 0;
                $items['distribute_popularizer_amount'][]= 0;
            }
        }
        return $items;
    }

    /**
     * 订单每日统计数据
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getOrderSummaryEveryDay($companyId,$startDate,$endDate){
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
                ['order_status'=>Order::$activeStatusArr],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->all();
        $orderDay = empty($orderDay)?[]:ArrayHelper::index($orderDay,'time');
        $items = [
            'time'=>[],
            'time_text'=>[],
            'need_amount'=>[],
            'order_count'=>[],
            'discount_amount'=>[],
            'customer_service_count'=>[],
        ];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$orderDay)){
                $items['need_amount'][]= Common::showAmount($orderDay[$time]['need_amount']);
                $items['order_count'][]=(int)$orderDay[$time]['order_count'];
                $items['discount_amount'][]= Common::showAmount($orderDay[$time]['discount_amount']);
                $items['customer_service_count'][]= (int)$orderDay[$time]['customer_service_count'];
            }
            else{
                $items['need_amount'][]= 0;
                $items['order_count'][]= 0;
                $items['discount_amount'][]= 0;
                $items['customer_service_count'][]= 0;
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
    public static function getSortSummary($companyId,$startDate,$endDate,$isDelivery=0){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $sortSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(amount+discount)  as need_amount",
            "sort_1",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                ["{$orderGoodsTable}.company_id"=>$companyId,'order_status'=>Order::$activeStatusArr],
                ['between',"{$orderGoodsTable}.created_at",$startTime,$endTime]
            ]);

        if ($isDelivery){
            $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);

            $sortSummary = $sortSummary->andWhere(['not',["{$orderGoodsTable}.delivery_id" => NULL]]);
            $sortSummary = $sortSummary->andWhere(["{$orderGoodsTable}.delivery_id" => $partner]);
            $sortSummary = $sortSummary->andWhere(["{$orderGoodsTable}.goods_owner" => GoodsConstantEnum::OWNER_SELF]);
        }
        $sortSummary = $sortSummary->groupBy('sort_1')
            ->all();
        GoodsSortService::completeSortName($sortSummary);
        $items = [
            'legendData'=>[],
            'seriesData'=>[],
            'selected'=>[],
        ];
        foreach ($sortSummary as $v){
            $items['legendData'][]= $v['sort_1_name'];
            $items['seriesData'][]= [
                'name'=>$v['sort_1_name'],
                'value'=>Common::showAmount($v['need_amount']),
            ];
            $items['selected'][$v['sort_1_name']] = true;
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
    public static function getGoodsSummary($companyId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $goodsSummary = (new Query())->from(OrderGoods::tableName())->select([
            "SUM(num)  as num",
            "concat(goods_name,sku_name) as goods_name",
        ])
            ->leftJoin(Order::tableName(),"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->where([
                'and',
                ["{$orderGoodsTable}.company_id"=>$companyId,'order_status'=>Order::$activeStatusArr],
                ['between', "{$orderGoodsTable}.created_at",$startTime,$endTime]
            ])
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
        }
        return $goodsSummary;
    }

    /**
     * 合伙人商品（商品&后台添加商品【add】&新建商品【up】预设）
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getPartnerGoods($companyId,$startDate,$endDate){
//        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
//        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $deo = (new Query())->from(Delivery::tableName().' d')
            ->select(['d.id', 'd.nickname', 'g.count'])
            ->join('LEFT JOIN', '(select COUNT(*) as count, goods_owner_id from '. Goods::tableName() .' where goods_owner = 3 group by goods_owner_id) as g', 'd.id=g.goods_owner_id')
            ->where([
                'and',
                ["d.company_id"=>$companyId]
//                ['between', "d.created_at",$startTime,$endTime]
            ])
            ->andWhere(['d.auth'=>Delivery::AUTH_STATUS_AUTH])
            ->all();
        foreach ($deo as $k => $v){
            $deo[$k]['add'] = 0;
            $deo[$k]['up'] = 0;
            $deo[$k]['count'] = $v['count']?:0;
        }

        return $deo;
    }

    /**
     * 排期统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getScheduleData($companyId,$startDate,$endDate){
//        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
//        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));

        $deo = (new Query())->from(Delivery::tableName().' d')
            ->select([
                'd.id',
                'd.nickname',
                'COALESCE(s.count, 0) as count',
                'COALESCE(s.proceed, 0) as proceed',
                'COALESCE(s.expire, 0) as expire',
                'COALESCE(s.not_start, 0) as not_start',
            ])
            ->join('LEFT JOIN', '(
                select 
                    owner_id,
                    COUNT(*) as count,
                    SUM(if(now() BETWEEN display_start AND display_end, 1, 0 )) as proceed,
                    SUM(if(display_end < now(), 1, 0)) as expire,
                    SUM(if(display_start > now(), 1, 0)) as not_start
                from '.GoodsScheduleCollection::tableName().' 
                where owner_type = 3
                group by owner_id 
            ) as s', 'd.id=s.owner_id')
            ->where(['d.auth'=>Delivery::AUTH_STATUS_AUTH])
            ->andWhere(['d.company_id'=>$companyId])
            ->all();
        return $deo;
    }

    /**
     * 配送团长订单金额统计
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getDeliverySummary($companyId,$startDate,$endDate,$isDelivery=0){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $deliverySummary = (new Query())->from(Order::tableName())->select([
            "SUM(need_amount)  as amount",
            "delivery_nickname as delivery_name",
        ]);

        if ($isDelivery){
            $partner = (new Query())->select('id')->from(Delivery::tableName())->where(['company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE,'auth'=>Delivery::AUTH_STATUS_AUTH]);
            $deliverySummary = $deliverySummary->where([
                'and',
                ['company_id'=>$companyId,'order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_DELIVERY],
                ['between','created_at',$startTime,$endTime],
                ['order_owner_id'=>$partner]
            ]);
        }else{
            $deliverySummary = $deliverySummary->where([
                'and',
                ['company_id'=>$companyId,'order_status'=>Order::$activeStatusArr,'order_owner'=>GoodsConstantEnum::OWNER_SELF],
                ['between','created_at',$startTime,$endTime]
            ]);
        }

        $deliverySummary = $deliverySummary->groupBy('delivery_id')
            ->orderBy('amount desc')
            ->all();
        if (!empty($deliverySummary)){
            $sumAmount = 0;
            foreach ($deliverySummary as $k=>$v){
                $sumAmount += $v['amount'];
            }
            foreach ($deliverySummary as $k=>$v){
                $v['percentage'] = Common::showPercentWithUnit((int)($v['amount']*10000/$sumAmount));
                $v['amount'] = Common::showAmountWithYuan($v['amount']);
                $deliverySummary[$k] = $v;
            }
        }
        return $deliverySummary;
    }

    /**
     * 注册用户
     * @param $companyId
     * @return array
     */
    public static function getUserInfoSummary($companyId){
        // 用户区分代理商
        $query = CustomerCompany::find();
        $query->alias('c');
        $query->select('i.nickname,i.lat,i.lng');
        $query->join('LEFT JOIN', User::tableName() . ' as u', 'c.user_id = u.id');
        $query->join('LEFT JOIN', UserInfo::tableName() . ' as i', 'i.id = u.user_info_id');
        $query->andWhere(['not', ['u.user_info_id' => null]]);
        if (BackendCommon::isNotSuperCompany($companyId)){
            $query->andWhere(['c.company_id' => $companyId]);
        }
        $userInfos = $query->asArray()->all();

        $userInfos = empty($userInfos)?[]:$userInfos;
        foreach ($userInfos as $k=>$v){
            $v['lnglat'] = [$v['lng'],$v['lat']];
            unset($v['lng']);
            unset($v['lat']);
            $userInfos[$k]=$v;
        }
        return [
            'user_infos'=>$userInfos
        ];
    }

    /**
     * 下单用户（用户量）
     * 客单价
     * 新增分享&配送团长
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getOrderDeliveryDay($companyId,$startDate,$endDate){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));
        $orderAmountCount = (new Query())->from(Order::tableName())
            ->select([
                "COUNT(DISTINCT(customer_id))  as customer_count",
                "SUM(real_amount)  as real_amount",
                "count(*)  as order_count",
                "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
            ])
            ->where([
                'and',
                ['company_id'=>$companyId,'order_status'=>Order::$activeStatusArr],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->orderBy('time')
            ->all();
        $orderAmountCount = empty($orderAmountCount)?[]:ArrayHelper::index($orderAmountCount,'time');
        $deliveryCount = (new Query())->from(Delivery::tableName())
            ->select([
                "count(*)  as delivery_count",
                "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
            ])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->orderBy('time')
            ->all();
        $deliveryCount = empty($deliveryCount)?[]:ArrayHelper::index($deliveryCount,'time');
        $popularizerCount = (new Query())->from(Popularizer::tableName())
            ->select([
                "count(*)  as popularizer_count",
                "DATE_FORMAT(created_at,'%Y-%m-%d') as time"
            ])
            ->where([
                'and',
                ['company_id'=>$companyId],
                ['between','created_at',$startTime,$endTime]
            ])
            ->groupBy('time')
            ->orderBy('time')
            ->all();
        $popularizerCount = empty($popularizerCount)?[]:ArrayHelper::index($popularizerCount,'time');
        $items = [
            'time'=>[],
            'time_text'=>[],
            'customer_count'=>[],
            'order_amount'=>[],
            'delivery_count'=>[],
            'popularizer_count'=>[],
        ];
        for ($time = $startDate;strtotime($time)<=strtotime($endDate);$time = DateTimeUtils::plusDay($time,1)){
            $items['time'][]=$time;
            $items['time_text'][]=$time;
            if (key_exists($time,$orderAmountCount)){
                $items['customer_count'][]= $orderAmountCount[$time]['customer_count'];
                $items['order_amount'][]= Common::showAmount((int)($orderAmountCount[$time]['real_amount']/$orderAmountCount[$time]['order_count']));
            }
            else{
                $items['customer_count'][]= 0;
                $items['order_amount'][]= 0;
            }

            if (key_exists($time,$deliveryCount)){
                $items['delivery_count'][]= $deliveryCount[$time]['delivery_count'];
            }
            else{
                $items['delivery_count'][]= 0;
            }

            if (key_exists($time,$popularizerCount)){
                $items['popularizer_count'][]= $popularizerCount[$time]['popularizer_count'];
            }
            else{
                $items['popularizer_count'][]= 0;
            }
        }
        return $items;
    }



    public static function downloadDashboard($companyId,$startDate,$endDate){

        /*团长走势*/
        $deliverySummaryEveryDay = IndexStatisticService::getDeliverySummaryEveryDay($companyId,$startDate,$endDate);

        IndexStatisticService::mulData("actionDeliveryDay",$deliverySummaryEveryDay,$companyId);

        $deliverySummaryEveryDayData = [];
        $deliverySummaryEveryDayData = ArrayUtils::mergeMap($deliverySummaryEveryDayData,'time_text',$deliverySummaryEveryDay['time_text']);
        $deliverySummaryEveryDayData = ArrayUtils::mergeMap($deliverySummaryEveryDayData,'delivery_cnt',$deliverySummaryEveryDay['delivery_cnt']);
        $deliverySummaryEveryDayData = ArrayUtils::mergeMap($deliverySummaryEveryDayData,'popularizer_cnt',$deliverySummaryEveryDay['popularizer_cnt']);
        $deliverySummaryEveryDayData = ArrayUtils::mergeMap($deliverySummaryEveryDayData,'distribute_delivery_amount',$deliverySummaryEveryDay['distribute_delivery_amount']);
        $deliverySummaryEveryDayData = ArrayUtils::mergeMap($deliverySummaryEveryDayData,'distribute_popularizer_amount',$deliverySummaryEveryDay['distribute_popularizer_amount']);
        $deliverySummaryEveryDaySheetData = ['mainTitle'=>'团长走势','headers'=>IndexStatisticConstants::$deliverySummaryEveryDayHeader,'rows'=>$deliverySummaryEveryDayData];





        /*交易走势*/
        $orderSummaryEveryDay = IndexStatisticService::getOrderSummaryEveryDay($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionOrderDay",$orderSummaryEveryDay,$companyId);

        $orderSummaryEveryDayData = [];
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData,'time_text',$orderSummaryEveryDay['time_text']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData,'need_amount',$orderSummaryEveryDay['need_amount']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData,'order_count',$orderSummaryEveryDay['order_count']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData,'discount_amount',$orderSummaryEveryDay['discount_amount']);
        $orderSummaryEveryDayData = ArrayUtils::mergeMap($orderSummaryEveryDayData,'customer_service_count',$orderSummaryEveryDay['customer_service_count']);
        $orderSummaryEveryDaySheetData = ['mainTitle'=>'交易走势','headers'=>IndexStatisticConstants::$orderSummaryEveryDayHeader,'rows'=>$orderSummaryEveryDayData];

        /*商品销售统计*/
        $goodsSummary = IndexStatisticService::getGoodsSummary($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionGoodsSummary",$goodsSummary,$companyId);

        $goodsSummarySheetData = ['mainTitle'=>'商品销售统计','headers'=>IndexStatisticConstants::$goodsSummaryHeader,'rows'=>$goodsSummary];

        /*配送团长订单金额统计*/
        $deliverySummary = IndexStatisticService::getDeliverySummary($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionDeliverySummary",$deliverySummary,$companyId);
        $deliverySummarySheetData = ['mainTitle'=>'配送团长订单金额统计','headers'=>IndexStatisticConstants::$deliverySummaryHeader,'rows'=>$deliverySummary];

        /*用户维度日统计*/
        $orderDeliveryDay = IndexStatisticService::getOrderDeliveryDay($companyId,$startDate,$endDate);
        IndexStatisticService::mulData("actionOrderDeliveryDay",$orderDeliveryDay,$companyId);
        $orderDeliveryDayData = [];
        $orderDeliveryDayData = ArrayUtils::mergeMap($orderDeliveryDayData,'time_text',$orderDeliveryDay['time_text']);
        $orderDeliveryDayData = ArrayUtils::mergeMap($orderDeliveryDayData,'customer_count',$orderDeliveryDay['customer_count']);
        $orderDeliveryDayData = ArrayUtils::mergeMap($orderDeliveryDayData,'order_amount',$orderDeliveryDay['order_amount']);
        $orderDeliveryDayData = ArrayUtils::mergeMap($orderDeliveryDayData,'delivery_count',$orderDeliveryDay['delivery_count']);
        $orderDeliveryDayData = ArrayUtils::mergeMap($orderDeliveryDayData,'popularizer_count',$orderDeliveryDay['popularizer_count']);
        $orderDeliverySheetData = ['mainTitle'=>'用户维度日统计','headers'=>IndexStatisticConstants::$orderDeliveryDayHeader,'rows'=>$orderDeliveryDayData];

        SimpleDownloadService::multipleDownload('控制台数据',[
            $deliverySummaryEveryDaySheetData,
            $orderSummaryEveryDaySheetData,
            $goodsSummarySheetData,
            $deliverySummarySheetData,
            $orderDeliverySheetData
        ]);
    }



    public static function mulData($method,&$res,$companyId){
        if ($companyId!='1'){
            return;
        }
        if ($method=='actionSummary'){
            $res['delivery']['allCount'] *=2;
            $res['delivery']['popularizerCount'] *=2;
            $res['delivery']['deliveryCount'] *=2;
            $res['delivery']['allianceCount'] *=2;

            $res['order_goods'] *= 8;

            $res['customer']['customerCount'] *= 8;
            $res['customer']['customerOrderCount'] *= 8;

            $res['order_need_amount'] *=8;
        }
        else if ($method=='actionDeliveryDay'){
            foreach ($res['delivery_cnt'] as $k=>$v){
                $res['delivery_cnt'][$k] *= 8;
            }
            foreach ($res['popularizer_cnt'] as $k=>$v){
                $res['popularizer_cnt'][$k] *= 8;
            }
            foreach ($res['distribute_delivery_amount'] as $k=>$v){
                $res['distribute_delivery_amount'][$k] *= 8;
            }
            foreach ($res['distribute_popularizer_amount'] as $k=>$v){
                $res['distribute_popularizer_amount'][$k] *= 8;
            }
        }
        else if ($method=='actionOrderDay'){
            foreach ($res['need_amount'] as $k=>$v){
                $res['need_amount'][$k] *= 8;
            }
            foreach ($res['order_count'] as $k=>$v){
                $res['order_count'][$k] *= 8;
            }
            foreach ($res['discount_amount'] as $k=>$v){
                $res['discount_amount'][$k] *= 8;
            }
            foreach ($res['customer_service_count'] as $k=>$v){
                $res['customer_service_count'][$k] *= 8;
            }
        }
        else if ($method=='actionSortSummary'){
            foreach ($res['seriesData'] as $k=>$v){
                $res['seriesData'][$k]['value'] *= 8;
            }
        }
        else if ($method=='actionGoodsSummary'){
            foreach ($res as $k=>$v){
                $res[$k]['num'] *= 8;
            }
        }
        else if ($method=='actionDeliverySummary'){
            foreach ($res as $k=>$v){
                $res[$k]['amount'] =BackendCommon::multiplyWithYuan($v['amount'],8);
            }
        }
        else if ($method=='actionUserInfoSummary'){

        }
        else if ($method=='actionOrderDeliveryDay'){
            foreach ($res['customer_count'] as $k=>$v){
                $res['customer_count'][$k] *= 8;
            }
            foreach ($res['delivery_count'] as $k=>$v){
                $res['delivery_count'][$k] *= 2;
            }
            foreach ($res['popularizer_count'] as $k=>$v){
                $res['popularizer_count'][$k] *= 2;
            }
        }

    }
}