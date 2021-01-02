<?php


namespace alliance\services;


use alliance\models\AllianceCommon;
use alliance\utils\ExceptionAssert;
use alliance\utils\StatusCode;
use common\models\BizTypeEnum;
use common\models\CommonStatus;
use common\models\DistributeBalance;
use common\models\DistributeBalanceItem;
use common\models\Order;
use common\models\OrderPreDistribute;
use common\services\DistributeBalanceItemService;
use common\utils\DateTimeUtils;
use common\utils\MathUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DistributeBalanceService extends \common\services\DistributeBalanceService
{


    /**
     * 预分润汇总 所有
     * @param $allianceId
     * @return array|bool
     */
    public static function preDistributeAllSumF($allianceId){
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_ALLIANCE,$allianceId, null, null);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(日)
     * @param $allianceId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeDaySumF($allianceId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_ALLIANCE,$allianceId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(月)
     * @param $allianceId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeMonthSumF($allianceId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_ALLIANCE,$allianceId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }


    /**
     * 预分润详情(日)
     * @param $allianceId
     * @param $date
     * @return array
     */
    public static function preDistributeDayF($allianceId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $preDistributeOrders = self::getPreDistributeOrderF($allianceId, $startTime, $endTime);
        $orderCount = count($preDistributeOrders);
        $preDistributeAmount = 0;
        $preDistributeArr= [];
        self::assembleDetail($preDistributeOrders, $preDistributeArr, $preDistributeAmount);
        $res = [
            'date'=>DateTimeUtils::formatYearAndMonthAndDayChinese($date),
            'order_count' =>$orderCount,
            'pre_distribute_amount'=>$preDistributeAmount,
            'preDistributeOrders'=>$preDistributeArr
        ];
        return $res;
    }


    /**
     * 预分润详情(月)
     * @param $allianceId
     * @param $date
     * @return array
     */
    public static function preDistributeMonthF($allianceId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $preDistributeOrders = self::getPreDistributeOrderF($allianceId, $startTime, $endTime);
        $orderCount = count($preDistributeOrders);
        $preDistributeAmount = 0;
        $preDistributeArr= [];
        self::assembleDetail($preDistributeOrders, $preDistributeArr, $preDistributeAmount);
        $res = [
            'date'=>DateTimeUtils::formatYearAndMonthChinese($date),
            'order_count' =>$orderCount,
            'pre_distribute_amount'=>$preDistributeAmount,
            'orders'=>$preDistributeArr
        ];
        return $res;
    }

    /**
     * @param $customerId
     * @param $startTime
     * @param $endTime
     * @return array
     */
    public static function getPreDistributeOrderF($customerId, $startTime, $endTime)
    {
        $orderTable = Order::tableName();
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $conditions = [
            "and",
            [
                "{$orderPreDistributeTable}.biz_id"=>$customerId,
                "{$orderPreDistributeTable}.biz_type"=>OrderPreDistribute::BIZ_TYPE_ALLIANCE,
            ],
            ["{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE, Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY, Order::ORDER_STATUS_RECEIVE, Order::ORDER_STATUS_COMPLETE]],
        ];
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        $preDistributeOrders = (new Query())->from($orderPreDistributeTable)
            ->leftJoin($orderTable,"{$orderTable}.order_no={$orderPreDistributeTable}.order_no")
            ->where($conditions)->orderBy("{$orderPreDistributeTable}.order_time desc")->all();
        return $preDistributeOrders;
    }




    /**
     * 汇总
     * @param $allianceInfos
     * @return array
     */
    public static function getDistributeInfo($allianceInfos){
        $allianceOrderSum = 0;
        $allianceDistributeSum = 0;
        $allianceBalance = 0;
        if (!empty($allianceInfos)){
            $allianceInfos = ArrayHelper::index($allianceInfos,'id');
            $allianceIds = array_keys($allianceInfos);
            $allianceDistributeItems = self::getSumByBizType(BizTypeEnum::BIZ_TYPE_HA,$allianceIds,DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!empty($allianceDistributeItems)){
                foreach ($allianceDistributeItems as $v){
                    if (in_array($v['biz_id'],$allianceIds)){
                        $allianceOrderSum += $v['order_amount'];
                        $allianceDistributeSum += $v['amount'];
                        $allianceInfos[$v['biz_id']]['distribute'] = $v;
                    }
                }
            }
            $allianceBalance = self::getSumBalanceByBizType(BizTypeEnum::BIZ_TYPE_HA,$allianceIds);
            $allianceInfos = array_values($allianceInfos);
        }


        $res = [
            'balance' =>$allianceBalance,
            'allianceOrderSum'=>$allianceOrderSum,
            'allianceDistributeSum'=>$allianceDistributeSum,
            'allianceInfos' =>$allianceInfos,
        ];
        return $res;
    }

    public static function getSumBalanceByBizType($bizType, $bizIds){
        $result = DistributeBalance::find()
            ->select( ['SUM(amount) amount'])
            ->where(['biz_type'=>$bizType,'biz_id'=>$bizIds])
            ->one();
        return $result===false?0:$result['amount'];
    }


    public static function getSumByBizType($bizType, $bizIds,$type){
        $result = DistributeBalanceItem::find()->select(
            ['SUM(order_amount) order_amount','SUM(amount) amount','biz_type','biz_id']
        )
            ->where(['biz_id'=>$bizIds,'biz_type'=>$bizType,'status'=>CommonStatus::STATUS_ACTIVE,'type'=>$type])
            ->groupBy("biz_id")->all();
        return $result;
    }

    /**
     * 校验权限
     * @param $bizId
     * @param $bizType
     * @param $userId
     */
    public static function checkPermission($bizId,$bizType,$userId){
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_HA]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        if (BizTypeEnum::BIZ_TYPE_HA == $bizType){
            AllianceCommon::checkAlliancePermission($bizId,$userId);
        }
    }

    /**
     * 根据bizType获取默认bizId
     * @param $bizType
     * @param $userId
     * @return mixed|null
     */
    public static function getDefaultIdByBizType($bizType,$userId){
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_HA]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        $bizId = null;
        if ($bizType==BizTypeEnum::BIZ_TYPE_HA){
            $bizId = AllianceService::getSelectedId($userId);
            ExceptionAssert::assertNotNull($bizId,StatusCode::createExpWithParams(StatusCode::ALLIANCE_NOT_EXIST,'不存在默认异业联盟信息'));
        }
        return $bizId;
    }

    /**
     * 统计分润汇总曲线
     * @param $dateStr
     * @param $dateType
     * @param $bizType
     * @param $bizId
     * @return array|void
     */
    public static function calcDistributeStatistics($dateStr,$dateType,$bizType, $bizId){
        ExceptionAssert::assertNotNull(in_array($dateType,['day','month']),StatusCode::createExpWithParams(StatusCode::DISTRIBUTE_STATISTICS_ERROR,'未知时间类型'));
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_HA]),StatusCode::createExpWithParams(StatusCode::DISTRIBUTE_STATISTICS_ERROR,'未知类型'));
        if ($dateType=='day'){
            return self::calcDayDistributeStatistics($dateStr,$bizType, $bizId);
        }
        else if ($dateType=='month'){
            return self::calcMonthDistributeStatistics($dateStr,$bizType, $bizId);
        }
        return [];
    }

    /**
     * 按月统计最近6个月的分润汇总
     * @param $dateStr
     * @param $bizType
     * @param $bizId
     * @return array
     */
    private static function calcMonthDistributeStatistics($dateStr,$bizType, $bizId){
        $beforeMonthAmount = 6;
        $endTime = DateTimeUtils::endOfMonthLong($dateStr,true);
        $startTime = DateTimeUtils::startOfMonthLong(DateTimeUtils::plusMonth($dateStr,-$beforeMonthAmount));
        $statistics = self::getStatisticsByBizType($bizType,$bizId,DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE,'%Y-%m',$startTime,$endTime);
        $statistics = ArrayHelper::index($statistics,'time');
        $result = [];
        $items = [];
        for ($time = DateTimeUtils::formatYearAndMonth($startTime,false);strtotime($time)<$endTime;$time = DateTimeUtils::plusMonth($time,1)){
            $item = [
                'time'=>$time,
                'time_text'=>date('n月',strtotime($time))
            ];
            if (key_exists($time,$statistics)){
                $item['order_amount'] = $statistics[$time]['order_amount'];
                $item['order_count'] = $statistics[$time]['order_count'];
                $item['amount'] = $statistics[$time]['amount'];
            }
            else{
                $item['order_amount'] = 0;
                $item['order_count'] = 0;
                $item['amount'] = 0;
            }
            $items[$time] = $item;
        }
        if (key_exists($dateStr,$items)){
            $result['current'] = $items[$dateStr];
            $beforeDateStr = DateTimeUtils::formatYearAndMonth(DateTimeUtils::startOfMonthLong(DateTimeUtils::plusMonth($dateStr,-1)),false);
            if (key_exists($beforeDateStr,$items)){
                $before = $items[$beforeDateStr];
                $result['current']['grow'] = "较上月增长".MathUtils::calcGrow($result['current']['order_amount'],$before['order_amount']);
            }
            else{
                $result['current']['grow'] = "较上月增长0%";
            }
        }
        else{
            $result['current'] = [];
        }
        $result['items'] = array_values($items);
        return $result;
    }

    /**
     * 按日统计最近30天的分润汇总
     * @param $dateStr
     * @param $bizType
     * @param $bizId
     * @return array
     */
    private static function calcDayDistributeStatistics($dateStr,$bizType, $bizId){
        $beforeDayAmount = 30;
        $endTime = DateTimeUtils::endOfDayLong($dateStr,true);
        $startTime = strtotime($dateStr) - $beforeDayAmount*86400;
        $statistics = self::getStatisticsByBizType($bizType,$bizId,DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE,'%Y-%m-%d',$startTime,$endTime);
        $statistics = ArrayHelper::index($statistics,'time');
        $result = [];
        $items = [];
        for ($time = $startTime;$time<$endTime;$time+=86400){
            $timeStr = DateTimeUtils::formatYearAndMonthAndDay($time,false);
            $item = [
                'time'=>$timeStr,
                'time_text'=>date('m/d',strtotime($timeStr)),
            ];
            if (key_exists($timeStr,$statistics)){
                $item['order_amount'] = $statistics[$timeStr]['order_amount'];
                $item['order_count'] = $statistics[$timeStr]['order_count'];
                $item['amount'] = $statistics[$timeStr]['amount'];
            }
            else{
                $item['order_amount'] = 0;
                $item['order_count'] = 0;
                $item['amount'] = 0;
            }
            $items[$timeStr] = $item;
        }
        if (key_exists($dateStr,$items)){
            $result['current'] = $items[$dateStr];
            $beforeDateStr = DateTimeUtils::formatYearAndMonthAndDay(strtotime($dateStr) - 86400,false);
            if (key_exists($beforeDateStr,$items)){
                $before = $items[$beforeDateStr];
                $result['current']['grow'] = "较昨日增长".MathUtils::calcGrow($result['current']['order_amount'],$before['order_amount']);
            }
            else{
                $result['current']['grow'] = "较昨日增长0%";
            }
        }
        else{
            $result['current'] = [];
        }
        $result['items'] = array_values($items);
        return $result;
    }

    /**
     * 按月/日统计订单
     * @param $bizType
     * @param $bizIds
     * @param $type
     * @param $groupBy
     * @param $startTime \DateTime
     * @param $endTime \DateTime
     * @return array
     */
    private static function getStatisticsByBizType($bizType, $bizIds,$type,$groupBy,$startTime,$endTime){
        $statistic = (new Query())->from(DistributeBalanceItem::tableName())
            ->select(['SUM(order_amount) order_amount','COUNT(*) order_count','SUM(amount) amount',"date_format( created_at, '{$groupBy}' ) AS time"])
            ->where([
                'AND',
                ['biz_type'=>$bizType,'biz_id'=>$bizIds,'type'=>$type],
                ['>=','created_at',DateTimeUtils::parseStandardWLongDate($startTime)],
                ['<=','created_at',DateTimeUtils::parseStandardWLongDate($endTime)],
            ])
            ->groupBy('time')->orderBy('time asc')->all();
        return empty($statistic)?[]:$statistic;
    }

    /**
     * 分润详情（按月，带订单信息）
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array
     */
    public static function getDistributeDetail($bizType, $bizId,$date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $list = parent::getDistributeListByDateWithOrder($bizType,$bizId,$startTime,$endTime);
        $orderAmount = 0;
        $amount = 0;
        if (!empty($list)){
            foreach ($list as $k=>$v){
                $orderAmount += $v['order_amount'];
                $amount += $v['amount'];
            }
        }
        $res = [
            'date_text'=>DateTimeUtils::formatYearAndMonthChinese($date),
            'order_amount'=>$orderAmount,
            'amount'=>$amount,
            'item'=>$list,
        ];
        return $res;
    }

    /**
     * 获取订单详情
     * @param $bizType
     * @param $bizId
     * @param $id
     * @return array|bool|\common\models\Order|null
     */
    public static function getDistributeOrder($bizType, $bizId,$id){
        $item = DistributeBalanceItemService::getModel($id,$bizType,$bizId);
        ExceptionAssert::assertNotNull($item,StatusCode::createExp(StatusCode::DISTRIBUTE_ITEM_NOT_EXIST));
        $order = OrderService::getOrderModelWithGoods($item['order_no']);
        return $order;
    }




}