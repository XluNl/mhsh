<?php


namespace business\services;


use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\BizTypeEnum;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\DistributeBalance;
use common\models\DistributeBalanceItem;
use common\models\WechatPayLog;
use common\services\DistributeBalanceItemService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\MathUtils;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DistributeBalanceService extends \common\services\DistributeBalanceService
{

    /**
     * 汇总
     * @param $deliveryInfos
     * @param $popularizerInfos
     * @return array
     */
    public static function getDistributeInfo($deliveryInfos,$popularizerInfos){
        $deliveryOrderSum = 0;
        $deliveryDistributeSum = 0;
        $deliveryBalance = 0;
        if (!empty($deliveryInfos)){
            $deliveryInfos = ArrayHelper::index($deliveryInfos,'id');
            $deliveryIds = array_keys($deliveryInfos);
            $deliveryDistributeItems = self::getSumByBizType(BizTypeEnum::BIZ_TYPE_DELIVERY,$deliveryIds,DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!empty($deliveryDistributeItems)){
                foreach ($deliveryDistributeItems as $v){
                    if (in_array($v['biz_id'],$deliveryIds)){
                        $deliveryOrderSum += $v['order_amount'];
                        $deliveryDistributeSum += $v['amount'];
                        $deliveryInfos[$v['biz_id']]['distribute'] = $v;
                    }
                }
            }
            $deliveryBalance = self::getSumBalanceByBizType(BizTypeEnum::BIZ_TYPE_DELIVERY,$deliveryIds);
            $deliveryInfos = array_values($deliveryInfos);
        }

        $popularizerOrderSum = 0;
        $popularizerDistributeSum = 0;
        $popularizerBalance = 0;
        if (!empty($popularizerInfos)){
            $popularizerInfos = ArrayHelper::index($popularizerInfos,'id');
            $popularizerIds = array_keys($popularizerInfos);
            $popularizerDistributeItems = self::getSumByBizType(BizTypeEnum::BIZ_TYPE_POPULARIZER,$popularizerIds,DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!empty($popularizerDistributeItems)){
                foreach ($popularizerDistributeItems as $v){
                    if (in_array($v['biz_id'],$popularizerIds)){
                        $popularizerOrderSum += $v['order_amount'];
                        $popularizerDistributeSum += $v['amount'];
                        $popularizerInfos[$v['biz_id']]['distribute'] = $v;
                    }
                }
            }
            $popularizerBalance = self::getSumBalanceByBizType(BizTypeEnum::BIZ_TYPE_POPULARIZER,$popularizerIds);
            $popularizerInfos = array_values($popularizerInfos);
        }
        $res = [
            'balance' =>$deliveryBalance+$popularizerBalance,
            'deliveryOrderSum'=>$deliveryOrderSum,
            'deliveryDistributeSum'=>$deliveryDistributeSum,
            'popularizerOrderSum'=>$popularizerOrderSum,
            'popularizerDistributeSum'=>$popularizerDistributeSum,
            'deliveryInfos' =>$deliveryInfos,
            'popularizerInfos'=>$popularizerInfos,
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
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        if (BizTypeEnum::BIZ_TYPE_POPULARIZER == $bizType){
            BusinessCommon::checkPopularizerPermission($bizId,$userId);
        }
        else if (BizTypeEnum::BIZ_TYPE_DELIVERY == $bizType||BizTypeEnum::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY == $bizType){
            BusinessCommon::checkDeliveryPermission($bizId,$userId);
        }
    }

    /**
     * 根据bizType获取默认bizId
     * @param $bizType
     * @param $userId
     * @return mixed|null
     */
    public static function getDefaultIdByBizType($bizType,$userId){
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY,BizTypeEnum::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY]),StatusCode::createExpWithParams(StatusCode::ILLEGAL_BIZ_TYPE,'未知类型'));
        $bizId = null;
        if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            $bizId = PopularizerService::getSelectedPopularizerId($userId);
            ExceptionAssert::assertNotNull($bizId,StatusCode::createExpWithParams(StatusCode::POPULARIZER_NOT_EXIST,'不存在默认分享团长信息'));
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY||$bizType==BizTypeEnum::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY){
            $bizId = DeliveryService::getSelectedDeliveryId($userId);
            ExceptionAssert::assertNotNull($bizId,StatusCode::createExpWithParams(StatusCode::DELIVERY_NOT_EXIST,'不存在默认配送团长信息'));
        }
        return $bizId;
    }

    /**
     * 统计分润汇总曲线
     * @param $dateStr
     * @param $dateType
     * @param $bizType
     * @param $bizId
     * @param $beforeNum
     * @return array
     */
    public static function calcDistributeStatistics($dateStr,$dateType,$bizType, $bizId,$beforeNum){
        ExceptionAssert::assertNotNull(in_array($dateType,['day','month']),StatusCode::createExpWithParams(StatusCode::DISTRIBUTE_STATISTICS_ERROR,'未知时间类型'));
        ExceptionAssert::assertNotNull(in_array($bizType,[BizTypeEnum::BIZ_TYPE_POPULARIZER,BizTypeEnum::BIZ_TYPE_DELIVERY]),StatusCode::createExpWithParams(StatusCode::DISTRIBUTE_STATISTICS_ERROR,'未知类型'));
        if ($dateType=='day'){
            return self::calcDayDistributeStatistics($dateStr,$bizType, $bizId,$beforeNum);
        }
        else if ($dateType=='month'){
            return self::calcMonthDistributeStatistics($dateStr,$bizType, $bizId,$beforeNum);
        }
        return [];
    }

    /**
     * 按月统计最近N个月的分润汇总
     * @param $dateStr
     * @param $bizType
     * @param $bizId
     * @param null $beforeNum
     * @return array
     */
    private static function calcMonthDistributeStatistics($dateStr,$bizType, $bizId,$beforeNum=null){
        $beforeMonthAmount = $beforeNum===null?6:$beforeNum;
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
     * 按日统计最近N天的分润汇总
     * @param $dateStr
     * @param $bizType
     * @param $bizId
     * @param null $beforeNum
     * @return array
     */
    private static function calcDayDistributeStatistics($dateStr,$bizType, $bizId,$beforeNum=null){
        $beforeDayAmount = $beforeNum===null?30:$beforeNum;
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

    /**
     * @param $openid
     * @param $bizType
     * @param $bizId
     * @param $money
     * @return array
     */
    public static function chargeConfirm($openid,$bizType,$bizId,$money){
        $payments = PaymentService::generateJSSdkPayInfo($openid,$bizType,$bizId,$money);
        return $payments;
    }


    /**
     * 商品质保金充值回调
     * @param $data
     * @param $fail
     * @return bool
     */
    public static function chargeCallBack($data,&$fail){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            list($res,$bizType,$bizId) = DistributeBalanceService::decodePayChargeAttachMessage($data['attach']);
            ExceptionAssert::assertNotNull($res,StatusCode::createExpWithParams(StatusCode::DELIVERY_AUTH_PAY_CALLBACK_ERROR,"无法解析充值信息{$data['attach']}"));
            //判断是否已经处理过
            $exOrderPayCnt = WechatPayLog::find()->where(['transaction_id'=>$data['transaction_id']])->asArray()->count();
            if ($exOrderPayCnt>0){
                $transaction->rollBack();
                return true;
            }
            $orderQueryResult = Yii::$app->businessWechat->payment->order->queryByOutTradeNumber($data['out_trade_no']);
            ExceptionAssert::assertTrue($orderQueryResult['return_code'] === 'SUCCESS',StatusCode::createExpWithParams(StatusCode::CHARGE_CALL_BACK_ERROR,'通信失败，请稍后再通知我'));
            if ($orderQueryResult['trade_state'] !== 'SUCCESS'){
                $transaction->rollBack();
                return true;
            }
            $delivery = Delivery::findOne(['id'=>$bizId]);
            ExceptionAssert::assertNotEmpty($delivery,StatusCode::createExpWithParams(StatusCode::CHARGE_CALL_BACK_ERROR,"业务id不存在"));
            $payLog = new WechatPayLog();
            $payLog->company_id = !empty($delivery['company_id'])?$delivery['company_id']:WechatPayLog::$UN_KNOWN_COMPANY;
            $payLog->biz_type = WechatPayLog::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY;
            $payLog->biz_id = $bizId;
            $payLog->out_trade_no = $data["out_trade_no"];
            $payLog->transaction_id = $data["transaction_id"];
            $payLog->attach = $data["attach"];
            $payLog->total_fee = $data["total_fee"];
            $payLog->remain_fee = $data["total_fee"];
            $payLog->settlement_total_fee = ArrayUtils::getArrayValue('settlement_total_fee',$data,'');
            $payLog->bank_type = ArrayUtils::getArrayValue($data["bank_type"], Yii::$app->params['bank_type'],$data["bank_type"]);
            $payLog->openid = $data["openid"];
            $payLog->nonce_str = $data["nonce_str"];
            $payLog->time_end = $data["time_end"];
            $payLog->sign = $data["sign"];
            $payLog->trade_type = $data["trade_type"];
            ExceptionAssert::assertTrue($payLog->save(),StatusCode::createExpWithParams(StatusCode::CHARGE_CALL_BACK_ERROR,'回调数据保存失败'));
            list($result,$errorMsg) = DistributeBalanceService::createItem(BizTypeEnum::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY,
                $bizId,
                $delivery['company_id'],
                $payLog->id,
                null,
                $data["total_fee"]*10,
                null,
                $bizId,$delivery['nickname'],
                DistributeBalanceItem::TYPE_DELIVERY_COMMODITY_WARRANTY);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::CHARGE_CALL_BACK_ERROR,$errorMsg));
            $transaction->commit();
            return true;
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            $fail($e->getMessage());
        }
    }

}