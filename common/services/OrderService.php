<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/03/003
 * Time: 2:02
 */
namespace common\services;

use business\services\OrderLogService;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\PriceUtils;
use common\utils\StringUtils;
use frontend\services\GroupRoomService;
use frontend\services\OrderDisplayDomainService;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderService
{

    /**
     * 获取订单
     * @param $order_no
     * @param bool $model
     * @param null $deliveryId
     * @param null $company_id
     * @param null $orderOwner
     * @param null $allianceId
     * @return array|bool|Order|null
     */
    public static function getOrderModel($order_no, $model=false, $deliveryId=null, $company_id=null, $orderOwner=null, $allianceId=null){
        $conditions = ['order_no'=>$order_no];
        if (!StringUtils::isBlank($deliveryId)){
            $conditions['delivery_id'] = $deliveryId;
        }
        if (!StringUtils::isBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if (!StringUtils::isBlank($orderOwner)){
            $conditions['order_owner'] = $orderOwner;
        }
        if (!StringUtils::isBlank($allianceId)){
            $conditions['order_owner_id'] = $allianceId;
        }
        if ($model){
            return Order::findOne($conditions);
        }
        else{
            $order = (new Query())->from(Order::tableName())->where($conditions)->one();
            return $order===false?null:$order;
        }
    }

    /**
     * 批量查询
     * @param $orderNos
     * @param bool $model
     * @param null $deliveryId
     * @param null $company_id
     * @param null $orderOwner
     * @param null $allianceId
     * @return array|bool|Order|null
     */
    public static function getAllOrderModel($orderNos, $model=false, $deliveryId=null, $company_id=null, $orderOwner=null, $allianceId=null){
        $conditions = ['order_no'=>$orderNos];
        if (!StringUtils::isBlank($deliveryId)){
            $conditions['delivery_id'] = $deliveryId;
        }
        if (!StringUtils::isBlank($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if (!StringUtils::isBlank($orderOwner)){
            $conditions['order_owner'] = $orderOwner;
        }
        if (!StringUtils::isBlank($allianceId)){
            $conditions['order_owner_id'] = $allianceId;
        }
        if ($model){
            $order = Order::find()->where($conditions)->all();
            return $order;
        }
        else{
            $order = (new Query())->from(Order::tableName())->where($conditions)->all();
            return $order===false?null:$order;
        }
    }

    /**
     * 查订单（带商品）
     * @param $order_no
     * @return array|Order|\yii\db\ActiveRecord|null
     */
    public static function getOrderModelWithGoods($order_no){
        $conditions = ['order_no'=>$order_no];
        return Order::find()->where($conditions)->with(['goods'])->asArray()->one();
    }

    /**
     * 根据customerId查询订单
     * @param $customerId
     * @param null $deliveryId
     * @return array
     */
    public static function getOrdersModelByCustomerId($customerId,$deliveryId=null){
        $conditions = ['customer_id'=>$customerId];
        if (!StringUtils::isBlank($deliveryId)){
            $conditions['delivery_id'] = $deliveryId;
        }
        $orders = (new Query())->from(Order::tableName())->where($conditions)->all();
        return $orders;
    }

    /**
     * @param $deliveryId
     * @return array
     */
    public static function getOrdersModelByDeliveryId($deliveryId){
        $conditions = ['delivery_id'=>$deliveryId];
        $orders = (new Query())->from(Order::tableName())->where($conditions)->all();
        return $orders;
    }

    /**
     * 获取订单商品列表
     * @param $orderNo
     * @param $ids array
     * @return array
     */
    public static function getOrderGoodsModel($orderNo, array $ids=[]){
        $conditions = ['order_no'=>$orderNo];
        if (StringUtils::isNotEmpty($ids)){
            $conditions['id'] = $ids;
        }
        $orderGoods = (new Query())->from(OrderGoods::tableName())->where($conditions)->all();
        return $orderGoods;
    }

    /**
     * 计算实际的最终金额
     * @param $order
     * @return float
     */
    public static function calcRealAmountAc($order){
        return round(($order['need_amount_ac']+$order['freight_amount']-$order['discount_amount'])/10.0)*10.0;
    }

    /**
     * 上传实际重量时重新计算最终价格
     * @param $real_amount_ac
     * @param $freight_amount
     * @return float
     */
    public static function refreshOrderCalcRealAmountAc($real_amount_ac,$freight_amount){
        return round(($real_amount_ac+$freight_amount)/10.0)*10.0;
    }


    /**
     * 订单退款时，计算多退少补金额（退余额+退三方支付）
     * @param $order
     * @param $isAllSuccessDelivery
     * @return array
     */
    public static function calcBalanceAndThreePartyAmountForCompleteOrder($order,$isAllSuccessDelivery){
        $balanceAmount = 0;
        $threePartyAmount = 0;
        //如果尚未进行任何提货
        if ($order['real_amount_ac']<=0){
            return [$order['balance_pay_amount'],$order['three_pay_amount']];
        }
        //如果全部都提货了或实体金额大于付款金额，则只退余额或记账在余额中
        else if ($isAllSuccessDelivery||$order['real_amount']-$order['real_amount_ac']<0){
            $balanceAmount = $order['real_amount']-$order['real_amount_ac'];
        }
        //如果差额大于三方支付金额
        else if ($order['real_amount']-$order['real_amount_ac']>$order['three_pay_amount']){
            $threePartyAmount = $order['three_pay_amount'];
            $balanceAmount = $order['real_amount']-$order['real_amount_ac'] - $order['three_pay_amount'];
        }
        //否则只退到余额
        else{
            $threePartyAmount = $order['real_amount']-$order['real_amount_ac'];
        }
        return [$balanceAmount,$threePartyAmount];
    }


    /**
     * 更新订单金额
     * @param $order
     */
    public static function refreshOrderWeightAndAmount($order){
        $orderGoodsModels = self::getOrderGoodsModel($order['order_no']);
        if (!empty($orderGoodsModels)){
            $goods_num_ac = 0;
            $need_amount_ac = 0;
            $real_amount_ac = 0;
            foreach ($orderGoodsModels as $orderGoodsModel){
                $goods_num_ac += $orderGoodsModel['num_ac'];
                $need_amount_ac += $orderGoodsModel['sku_price']*$orderGoodsModel['num_ac'];
                $real_amount_ac += $orderGoodsModel['amount_ac'];
            }
            $order['goods_num_ac'] = $goods_num_ac;
            $order['need_amount_ac'] = $need_amount_ac;
            $order['real_amount_ac'] = self::refreshOrderCalcRealAmountAc($real_amount_ac,$order['freight_amount']);
            Order::updateAll(['goods_num_ac'=>$goods_num_ac,'need_amount_ac'=>$need_amount_ac,'real_amount_ac'=>$order['real_amount_ac']],['order_no'=>$order['order_no']]);
        }
    }

    /**
     * 恢复库存
     * @param $order
     * @return array
     */
    public static function refreshStock($order){
        $allowStatus = [
            Order::ORDER_STATUS_UN_PAY,
            Order::ORDER_STATUS_PREPARE,
            Order::ORDER_STATUS_DELIVERY,
            Order::ORDER_STATUS_SELF_DELIVERY,
        ];
        if (!in_array($order['order_status'],$allowStatus)){
            return[false,'状态不允许退款'];
        }
        $orderGoods = (new Query())->from(OrderGoods::tableName())->where([
            'order_no'=>$order['order_no'],
            'status'=>CommonStatus::STATUS_ACTIVE,
            'company_id'=>$order['company_id'],
        ])->all();

        //如果当前时间超过所有排期的下架时间，则不允许下架
        $needScheduleTimeCheckStatus = [
            Order::ORDER_STATUS_PREPARE,
            Order::ORDER_STATUS_DELIVERY,
            Order::ORDER_STATUS_SELF_DELIVERY,
        ];

        if (in_array($order['order_status'],$needScheduleTimeCheckStatus)){
            $scheduleIds = ArrayUtils::getColumnWithoutNull('schedule_id',$orderGoods);
            if (!empty($scheduleIds)){
                $scheduleModels = GoodsScheduleService::getModels($scheduleIds,null,$order['company_id']);
                if (!empty($scheduleModels)){
                    $lastScheduleTime = $scheduleModels[0]['offline_time'];
                    for ($i=1;$i<count($scheduleModels);$i++){
                        if (DateTimeUtils::biggerStr($scheduleModels[$i]['offline_time'],$lastScheduleTime)){
                            $lastScheduleTime = $scheduleModels[$i]['offline_time'];
                        }
                    }
                    if (DateTimeUtils::biggerStr(DateTimeUtils::parseStandardWLongDate(time()),$lastScheduleTime)){
                        return [false,'所有商品停止售卖，暂不支持取消订单'];
                    }
                }
            }
        }

        foreach ($orderGoods as $orderGood){
            list($result,$error) = GoodsSkuService::addStock($orderGood['schedule_id'],$orderGood['sku_id'],$orderGood['num']);
            if ($result==false){
                return [$result,$error];
            }
        }
        return [true,''];
    }

    /**
     * 将订单状态置为取消
     * @param $order
     * @param $cancelRemark
     * @return array
     */
    public static function refreshOrderStatusToCancel($order,$cancelRemark){
        if ($order['order_status']==Order::ORDER_STATUS_CANCELED){
            return [false,'订单已取消，请勿重试'];
        }
        $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_CANCELED,'cancel_remark'=>$cancelRemark,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>$order['order_status']]);
        if ($updateCount<1){
            return [false,'订单状态已变更，请重试'];
        }
        return [true,''];
    }

    /**
     * 最后退三方支付
     * @param Order $order
     * @param $paymentSdk
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function refundThreePartPay($order,$paymentSdk){
        $allowStatus = [
            Order::ORDER_STATUS_PREPARE,
            Order::ORDER_STATUS_CHECKING,
        ];
        if (in_array($order['order_status'],$allowStatus)){
            //退三方支付
            list($success,$error) = OrderPayRefundService::distributeRefundThreePartyPay($order,$order['three_pay_amount'],$paymentSdk,'多退少补');
            return [$success,$error];
        }
        return [true,''];

    }


    /**
     * 上传商品实际重量
     * @param $order
     * @param $weights
     * @param $role
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    public static function uploadWeightCommon($order,$weights,$role,$operatorId, $operatorName){
        $ids = ArrayHelper::getColumn($weights,'id');
        $orderGoodsModels = self::getOrderGoodsModel($order['order_no'],$ids);
        if (!empty($orderGoodsModels)){
            $orderGoodsModels = ArrayHelper::index($orderGoodsModels,'id');
            $remark = '';
            foreach ($weights as $weight){
                if (key_exists($weight['id'],$orderGoodsModels)){
                    $orderGoodsModel = $orderGoodsModels[$weight['id']];
                    if (!in_array($orderGoodsModel['delivery_status'],OrderGoods::$canUploadWeightStatus)){
                        return [false,'备货中或售后中的订单不允许确认收货']; //对外文本修改了（原文本：备货中或售后中的商品不允许上传重量）
                    }
                    $remark = "{$remark}{$orderGoodsModel['schedule_name']}-{$orderGoodsModel['goods_name']}-{$orderGoodsModel['sku_name']}实际重量设置为{$weight['num']};";
                    $num_ac = $weight['num'];
                    $num = $orderGoodsModels[$weight['id']]['num'];
                    $discount = $orderGoodsModels[$weight['id']]['discount'];
                    $amount = $orderGoodsModels[$weight['id']]['amount'];
                    $price = $orderGoodsModels[$weight['id']]['sku_price'];
                    if ($num>=$num_ac){
                        $amount_ac = PriceUtils::accurateToTen(($num_ac*$amount)/$num);
                    }
                    else{
                        $amount_ac = PriceUtils::accurateToTen( ($num_ac*$price)-$discount);
                    }
                    OrderGoods::updateAll(['num_ac'=>$num_ac,'amount_ac'=>$amount_ac,'delivery_status'=>OrderGoods::DELIVERY_STATUS_SUCCESS],['order_no'=>$order['order_no'],'id'=>$weight['id']]);
                }
            }
            if (empty($remark)){
                return [false,'所有商品都不属于此订单'];
            }
            self::refreshOrderWeightAndAmount($order);
            OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_UPLOAD_WEIGHT,$remark);
        }
        return [true,''];
    }

    /**
     * 取消上传重量
     * @param $order
     * @param $ids
     * @param $role
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    public static function unUploadWeightCommon($order,$ids,$role,$operatorId, $operatorName){
        $orderGoodsModels = self::getOrderGoodsModel($order['order_no'],$ids);
        if (!empty($orderGoodsModels)){
            $orderGoodsModels = ArrayHelper::index($orderGoodsModels,'id');
            $remark = '';
            foreach ($ids as $id){
                if (key_exists($id,$orderGoodsModels)){
                    $orderGoodsModel = $orderGoodsModels[$id];
                    if (in_array($orderGoodsModel['delivery_status'],[OrderGoods::DELIVERY_STATUS_REFUND_MONEY_ONLY,OrderGoods::DELIVERY_STATUS_REFUND_MONEY_AND_GOODS,OrderGoods::DELIVERY_STATUS_CLAIM])){
                        return [false,'仅退款或退款退款的商品或赔付不允许取消提货'];
                    }
                    $remark = "{$remark}{$orderGoodsModel['schedule_name']}-{$orderGoodsModel['goods_name']}-{$orderGoodsModel['sku_name']}取消送达;";
                    OrderGoods::updateAll(['num_ac'=>0,'amount_ac'=>0,'delivery_status'=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY],['order_no'=>$order['order_no'],'id'=>$id]);
                }
            }
            if (empty($remark)){
                return [false,'所有商品都不属于此订单'];
            }
            self::refreshOrderWeightAndAmount($order);
            OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_UN_UPLOAD_WEIGHT,$remark);
        }
        return [true,''];
    }

    /**
     * 完成订单
     * @param $order
     * @param $paymentSdk
     * @param $role
     * @param $operationId
     * @param $operationName
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public static function completeOrder($order,$paymentSdk,$role,$operationId,$operationName){
        //分润
        list($result,$errorMsg) = DistributeBalanceService::distributeBalance($order,$isAllSuccessDelivery,$operationId,$operationName);
        if (!$result){
            return [false,$errorMsg];
        }

        //订单退款时，计算多退少补金额（退余额+退三方支付）
        list($balanceAmount,$threePartyAmount) = self::calcBalanceAndThreePartyAmountForCompleteOrder($order,$isAllSuccessDelivery);

        //退余额
        list($result,$errorMsg) =  CustomerBalanceService::adjustBalance($order,$balanceAmount,'订单完成多退少补',$operationId,$operationName);
        if (!$result){
            return [false,$errorMsg];
        }

        //退三方支付
        list($result,$errorMsg) =  OrderPayRefundService::distributeRefundThreePartyPay($order,$threePartyAmount,$paymentSdk,'多退少补');
        if (!$result){
            return [false,$errorMsg];
        }

        //更新订单状态
        $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_COMPLETE,'accept_time'=>DateTimeUtils::parseStandardWLongDate(),'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'company_id'=>$order['company_id']]);
        if ($updateCount<1){
            return [false,'更新订单状态失败'];
        }
        OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operationId,$operationName,OrderLogs::ACTION_ORDER_COMPLETE,'');
        return [true,''];
    }



    public static function receiveOrder($order){

    }


    /**
     * 一级订单统计
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param null $customerId
     * @return array
     */
    public static function activeOneLevelOrderStatistic($companyId, $startTime, $endTime, $customerId=null){
        $conditions = ['and',
            ['order_status'=>Order::$activeStatusArr,'company_id'=>$companyId],
            ['>=','created_at',$startTime],
            ['<=','created_at',$endTime],
        ];
        if (StringUtils::isNotBlank($customerId)){
            $conditions[] = ['one_level_rate_id'=>$customerId];
        }
        else{
            $conditions[] = ['not',['one_level_rate_id'=>null]];
        }

        $orderCustomerStatistic = (new Query())->from(Order::tableName())
            ->select([
                "COUNT(1) as invitation_child_order_count",
                "SUM(real_amount) as invitation_child_order_amount",
                "customer_id as invitation_child_id",
                "one_level_rate_id as customer_id",
            ])
            ->where($conditions)->groupBy("one_level_rate_id,customer_id")->all();
        $orderStatistic=[];
        foreach ($orderCustomerStatistic as $v){
            if (!key_exists($v['customer_id'],$orderStatistic)){
                $orderStatistic[$v['customer_id']] = [
                    'customer_id'=>$v['customer_id'],
                    'invitation_order_count'=>0,
                    'children'=>[],
                ];
            }
            $orderStatistic[$v['customer_id']]['invitation_order_count'] ++;
            $orderStatistic[$v['customer_id']]['children'][$v['invitation_child_id']] = [
                'child_customer_id'=> $v['invitation_child_id'],
                'child_order_count'=> $v['invitation_child_order_count'],
                'child_order_amount'=> $v['invitation_child_order_amount'],
            ];
        }
        return $orderStatistic;
    }


    /**
     * 二级订单统计
     * @param $companyId
     * @param $startTime
     * @param $endTime
     * @param null $customerId
     * @return array
     */
    public static function activeTwoLevelOrderStatistic($companyId, $startTime, $endTime, $customerId=null){
        $conditions = ['and',
            ['order_status'=>Order::$activeStatusArr,'company_id'=>$companyId],
            ['>=','created_at',$startTime],
            ['<=','created_at',$endTime],
        ];
        if (StringUtils::isNotBlank($customerId)){
            $conditions[] = ['two_level_rate_id'=>$customerId];
        }
        else{
            $conditions[] =  ['not',['two_level_rate_id'=>null]];
        }

        $orderCustomerStatistic = (new Query())->from(Order::tableName())
            ->select([
                "COUNT(1) as invitation_child_order_count",
                "SUM(real_amount) as invitation_child_order_amount",
                "customer_id as invitation_child_id",
                "two_level_rate_id as customer_id",
            ])
            ->where($conditions)->groupBy("two_level_rate_id,customer_id")->all();
        $orderStatistic=[];
        foreach ($orderCustomerStatistic as $v){
            if (!key_exists($v['customer_id'],$orderStatistic)){
                $orderStatistic[$v['customer_id']] = [
                    'customer_id'=>$v['customer_id'],
                    'invitation_order_count'=>0,
                    'children'=>[],
                ];
            }
            $orderStatistic[$v['customer_id']]['invitation_order_count'] ++;
            $orderStatistic[$v['customer_id']]['children'][$v['invitation_child_id']] = [
                'child_customer_id'=> $v['invitation_child_id'],
                'child_order_count'=> $v['invitation_child_order_count'],
                'child_order_amount'=> $v['invitation_child_order_amount'],
            ];
        }
        return $orderStatistic;
    }

    /**
     * 修改预计送达时间
     * @param $scheduleId
     * @param $expectArriveTime
     * @param $companyId
     */
    public static function modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId){
        $updateCount = OrderGoods::updateAll(['expect_arrive_time'=>$expectArriveTime,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['schedule_id'=>$scheduleId,'company_id'=>$companyId]);
    }

    /**
     * 上传默认重量并确认送达
     * @param $order
     * @param $role
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    protected static function uploadWeightAndReceiveOrderCommon($order,$role,$operatorId,$operatorName){
        if (!in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY])){
            return [false,'只允许在配送或自提阶段上传重量'];
        }
        $orderGoodsModels = self::getOrderGoodsModel($order['order_no']);
        if (!empty($orderGoodsModels)){
            $orderGoodsModels = ArrayHelper::index($orderGoodsModels,'id');
            $remark = '';
            foreach ($orderGoodsModels as $orderGoodsModel){
                if (in_array($orderGoodsModel['delivery_status'],OrderGoods::$needAutoUploadWeightStatus)){
                    $num_ac = $orderGoodsModel['num'];
                    $remark = "{$remark}{$orderGoodsModel['schedule_name']}-{$orderGoodsModel['goods_name']}-{$orderGoodsModel['sku_name']}实际重量设置为{$num_ac};";
                    $amount_ac = $orderGoodsModel['amount'];
                    OrderGoods::updateAll(['num_ac'=>$num_ac,'amount_ac'=>$amount_ac,'delivery_status'=>OrderGoods::DELIVERY_STATUS_SUCCESS],['order_no'=>$order['order_no'],'id'=>$orderGoodsModel['id']]);
                }
            }
            if (StringUtils::isNotBlank($remark)){
                self::refreshOrderWeightAndAmount($order);
                OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_UPLOAD_WEIGHT,$remark);
            }
        }
        $unReadyOrderGoodsArr = (new Query())->from(OrderGoods::tableName())->where( [
            'order_no'=>$order['order_no'],
            'delivery_status'=>OrderGoods::$unReceiveDeliveryStatus,
            'status'=>CommonStatus::STATUS_ACTIVE
        ])->all();
        $receiveError = self::generateReceiveError($unReadyOrderGoodsArr);
        if (StringUtils::isNotBlank($receiveError)){
            return [false,$receiveError];
        }
        $uploadCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_RECEIVE,'accept_time'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>[Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_DELIVERY]]);
        if ($uploadCount<1){
            return [false,'订单收货更新失败'];
        }
        OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_RECEIVE,'');
        return [true,''];
    }


    /**
     * 判断不允许提货的商品详情
     * @param $unReadyOrderGoodsArr
     * @return string
     */
    protected static function generateReceiveError($unReadyOrderGoodsArr){
        if (empty($unReadyOrderGoodsArr)){
            return "";
        }
        $str ="";
        foreach ($unReadyOrderGoodsArr as $v){
            $str = "{$str}{$v['goods_name']}{$v['sku_name']}处于".ArrayUtils::getArrayValue($v['delivery_status'],OrderGoods::$deliveryStatusArr).PHP_EOL;
        }
        return $str;
    }


    public static function deliveryOutOrderStatusForOrderGoods($orderLogRole,$orderLogAction,$orderNo,$companyId,$operationId,$operationName){
        $order = self::getOrderModel($orderNo,false,null,$companyId);
        if ($order['order_status']==Order::ORDER_STATUS_PREPARE){
            $updateCount = 0;
            if ($order['accept_delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_SELF){
                $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            }
            else if (in_array($order['accept_delivery_type'],[GoodsConstantEnum::DELIVERY_TYPE_HOME,GoodsConstantEnum::DELIVERY_TYPE_EXPRESS,GoodsConstantEnum::DELIVERY_TYPE_ALLIANCE_SELF])){
                $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            }
            else{
                return [false,'未知的配送方式'];
            }
            if ($updateCount>0){
                list($res,$error) = OrderLogService::addLog($orderLogRole,$order['order_no'],$order['company_id'],$operationId,$operationName,$orderLogAction,'');
                if (!$res){
                    return [false,$error];
                }
            }
        }
        return [true,''];
    }


    public static function deliveryOutOrderStatusForOrderGoodsForStorage($orderLogRole,$orderLogAction,$orderNo,$companyId,$operationId,$operationName){
        $order = self::getOrderModel($orderNo,false,null,$companyId);
        if ($order['order_status']==Order::ORDER_STATUS_PREPARE){
            $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            if ($updateCount>0){
                list($res,$error) = OrderLogService::addLog($orderLogRole,$order['order_no'],$order['company_id'],$operationId,$operationName,$orderLogAction,'');
                if (!$res){
                    return [false,$error];
                }
            }
        }
        return [true,''];
    }


    /**
     * 订单详情
     * @param $orderNo
     * @param $customerId
     * @param $companyId
     * @param null $ownerType
     * @param null $ownerId
     * @return mixed|null
     */
    public static function getOrderDetailCommon($orderNo,$customerId,$companyId,$ownerType=null,$ownerId=null){
        $conditions = ['order_no' => $orderNo,'customer_id'=>$customerId];
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        if (StringUtils::isNotBlank($ownerType)){
            $conditions['order_owner'] = $ownerType;
        }
        if (StringUtils::isNotBlank($ownerId)){
            $conditions['order_owner_id'] = $ownerId;
        }
        $orderDetail = Order::find()->where($conditions)->with(['goods', 'logs','delivery.userinfo'])->asArray()->one();
        if (empty($orderDetail)){
            return null;
        }
        $orderDetail['goods'] = self::getOrderGoodsModel($orderNo);
        if ($orderDetail['order_owner']==GoodsConstantEnum::OWNER_HA){
            $orderDetail['alliance'] = AllianceService::getModel($orderDetail['order_owner_id']);
        }
        if ($orderDetail['order_type']==GoodsConstantEnum::TYPE_GROUP_ACTIVE){
            $orderDetail = self::completeGroupRoomInfoCommon($orderDetail);
            $orderDetail = GroupRoomService::setRoomOwnerTagForOrder($orderDetail);
            //$orderDetail['group'] = GroupRoomService::getRoomAndActiveByOrderNo($orderNo,$companyId);
        }

        $orderDetail = OrderDisplayDomainService::defineOrderDisplayData($orderDetail);
        $orderDetail = OrderDisplayDomainService::setPreDistributeText($orderDetail);
        OrderDisplayDomainService::completeDeliveryInfo($orderDetail);
        // $orderDetail = GoodsDisplayDomainService::renameSubAttrImage($orderDetail,'head_img_url','delivery');
        return $orderDetail;
    }

    /**
     * 返回单团
     * @param $order
     * @return mixed|null
     */
    public static function completeGroupRoomInfoCommon($order){
        $roomOrder = GroupRoomOrderService::getActiveModel($order['order_no']);
        $order['room'] = GroupRoomService::getRoomDetail($roomOrder['room_no'], $roomOrder['company_id']);
        return $order;
    }

    /**
     * 返回多团
     * @param $orders
     * @return mixed
     */
    public static function completeGroupRoomsInfoCommon($orders){
        $orderNos = [];
        foreach ($orders as $v){
            if ($v['order_type']==GoodsConstantEnum::TYPE_GROUP_ACTIVE){
                $orderNos[] = $v['order_no'];
            }
        }
        if (empty($orderNos)){
            return $orders;
        }
        $roomOrders = GroupRoomOrderService::getActiveModels($orderNos);
        $roomNos = ArrayUtils::getColumnWithoutNull("room_no",$roomOrders);
        $roomList = GroupRoomService::getRoomsDetail($roomNos);
        $roomList = ArrayUtils::index($roomList,'room_no');
        $orderMapRoom = [];

        foreach ($roomOrders as $roomOrder){
            $orderMapRoom[$roomOrder['order_no']] = ArrayUtils::getArrayValue($roomOrder['room_no'],$roomList,[]);
        }

        foreach ($orders as &$v){
            if ($v['order_type']==GoodsConstantEnum::TYPE_GROUP_ACTIVE){
                $v['room'] = ArrayUtils::getArrayValue($v['order_no'],$orderMapRoom,[]);
            }
        }
        return $orders;
    }

    public static function cancelOrderCommon($order,$paymentSdk,$role,$operationId,$operationName,$cancelRemark="管理员取消订单"){
        // 订单如果是团活动订单 则释放团占位
        list($success,$error) = GroupOrderService::releaseGroupRoomPlace($order);
        if (!$success){
            return [false,$error];
        }

        //恢复优惠券
        list($success,$error) =  CouponService::recoveryCoupon($order['company_id'],$order['customer_id'],$order['order_no']);
        if (!$success){
            return [false,$error];
        }
        //取消库存
        list($success,$error) =  OrderService::refreshStock($order);
        if (!$success){
            return [false,$error];
        }

        //增加日志
        list($success,$error) =  OrderLogService::addLog($role,$order['order_no'],$order['company_id'],$operationId,$operationName,OrderLogs::ACTION_CANCEL_ORDER,$cancelRemark);
        if (!$success){
            return [false,$error];
        }

        //取消订单
        list($success,$error) =  OrderService::refreshOrderStatusToCancel($order,$cancelRemark);
        if (!$success){
            return [false,$error];
        }


        //最后退余额+三方支付
        list($success,$error) = CustomerBalanceService::adjustBalance($order,$order['balance_pay_amount'],'整单退款', $order['customer_id'], $order['accept_nickname']);
        if (!$success){
            return [false,$error];
        }
        list($success,$error) = OrderService::refundThreePartPay($order,$paymentSdk);
        if (!$success){
            return [false,$error];
        }

        return [true,''];
    }


    /**
     * 计算订单实付价格
     * @param $needAmount
     * @param $freightAmount
     * @param $discountAmount
     * @return float
     */
    public static function calcRealAmount($needAmount,$freightAmount,$discountAmount){
        return round(($needAmount+$freightAmount-$discountAmount)/10.0)*10.0;
    }
}