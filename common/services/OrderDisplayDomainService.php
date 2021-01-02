<?php


namespace common\services;


use common\models\BizTypeEnum;
use common\models\Common;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderPreDistribute;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use Yii;

class OrderDisplayDomainService
{

    public static function assembleOrderGoodsDisplayData($orderGoodsList){
        $orderGoodsList =  GoodsDisplayDomainService::batchRenameImageUrl($orderGoodsList,'sku_img');
        $orderGoodsList =  GoodsDisplayDomainService::batchRenameImageUrl($orderGoodsList,'goods_img');
        $orderGoodsList =  self::batchDefineDeliveryStatusText($orderGoodsList);
        return $orderGoodsList;
    }


    public static function defineDeliveryStatusText($orderGoods){
        $deliveryStatus = $orderGoods['delivery_status'];
        $orderGoods['delivery_status_text'] = ArrayUtils::getArrayValue($deliveryStatus,OrderGoods::$deliveryStatusArr);
        return $orderGoods;
    }

    /**
     * 批量设置到达时间文本
     * @param $list
     * @return array
     */
    public static function batchDefineExpectArriveTimeText($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineExpectArriveTimeText($v);
        }
        return $list;
    }

    /**
     * 设置到达时间文本
     * @param $orderGoods
     * @return mixed
     */
    public static function defineExpectArriveTimeText($orderGoods){
        $expectArriveTime = $orderGoods['expect_arrive_time'];
        $orderGoods['expect_arrive_time_text'] = DateTimeUtils::formatYearAndMonthAndDaySlash($expectArriveTime);
        return $orderGoods;
    }

    /**
     * 设置上传重量状态
     * @param $list
     * @return array
     */
    public static function batchDefineUploadStatus($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineUploadStatus($v);
        }
        return $list;
    }

    private static function defineUploadStatus($orderGoods){
        if (in_array($orderGoods['delivery_status'],[OrderGoods::DELIVERY_STATUS_PREPARE,OrderGoods::DELIVERY_STATUS_DELIVERY,OrderGoods::DELIVERY_STATUS_SELF_DELIVERY])){
            $orderGoods['upload_status'] = CommonStatus::STATUS_DISABLED;
            $orderGoods['upload_status_text'] = '未上传';
        }
        else{
            $orderGoods['upload_status'] = CommonStatus::STATUS_ACTIVE;
            $orderGoods['upload_status_text'] = '已上传';
        }
        return $orderGoods;
    }


    public static function batchDefineDeliveryStatusText($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineDeliveryStatusText($v);
        }
        return $list;
    }

    /**
     * 批量组装订单展示文本
     * @param $orders
     * @return array
     */
    public static function batchDefineOrderDisplayData($orders){
        if (empty($orders)){
            return [];
        }
        RegionService::batchSetProvinceAndCityAndCountyForOrder($orders);
        foreach ($orders as $k=>$v){
            $v = self::defineOrderDisplayData($v,true);
            $orders[$k]= $v;
        }
        return $orders;
    }

    /**
     * 组装订单展示文本
     * @param $order
     * @param bool $batch
     * @return mixed|null
     */
    public static function defineOrderDisplayData($order,$batch=false){
        if (empty($order)){
            return null;
        }
        $order = self::defineOrderStatus($order);
        $order = self::defineUnPayCountDown($order);
        $order = self::defineOrderCanCustomerService($order);
        if (!$batch){
            RegionService::setProvinceAndCityAndCountyForOrder($order);
        }
        $order = self::defineOrderDeliveryType($order);
        if (key_exists('goods',$order)){
            $order = self::defineCanReceiveOrder($order,$order['goods']);

            $order['goods'] = self::batchDefineDeliveryStatusText($order['goods']);
            $order['goods'] = self::batchDefineExpectArriveTimeText($order['goods']);
            $order['goods'] = self::batchDefineUploadStatus($order['goods']);
            $order['goods'] = self::batchDefineOrderGoodsCanCustomerService($order['goods']);
            $order['goods'] = self::batchDefineUploadWeight($order,$order['goods']);
            $order['goods'] = GoodsDisplayDomainService::assembleImage($order['goods']);
        }
        if (key_exists('customer',$order)){
            $order['customer'] = self::defineCustomerOrder($order['customer']);
        }
        return $order;
    }

    public static function defineCustomerOrder($customer){
        if (key_exists('user',$customer)){
            $customer['user'] =  self::defineUserInfoDomain($customer['user']);
        }
        return $customer;
    }

    public static function defineUserInfoDomain($userinfo){
        if (key_exists('head_img_url',$userinfo)){
            $userinfo['head_img_url'] =  Common::generateAbsoluteUrl($userinfo['head_img_url']);
        }
        return $userinfo;
    }


    public static function defineOrderCanCustomerService($order){
        if (in_array($order['order_status'],Order::$canCustomerServiceStatusArr)){
            $order['can_customer_service'] = CommonStatus::STATUS_ACTIVE;
        }
        else{
            $order['can_customer_service'] = CommonStatus::STATUS_DISABLED;
        }
        return $order;
    }

    private static function batchDefineUploadWeight($order,$list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineUploadWeight($order,$v);
        }
        return $list;
    }

    public static function defineUploadWeight($order,$orderGoods){
        if (!in_array($order['order_status'],Order::$canUploadWeightStatusArr)){
            $orderGoods['can_upload_weight'] = CommonStatus::STATUS_DISABLED;
            $orderGoods['can_upload_weight_text'] = '不允许上传重量';
        }
        else if (in_array($orderGoods['delivery_status'],OrderGoods::$canUploadWeightStatus)){
            $orderGoods['can_upload_weight'] = CommonStatus::STATUS_ACTIVE;
            $orderGoods['can_upload_weight_text'] = '允许上传重量';
        }
        else{
            $orderGoods['can_upload_weight'] = CommonStatus::STATUS_DISABLED;
            $orderGoods['can_upload_weight_text'] = '不允许上传重量';
        }
        return $orderGoods;
    }


    public static function defineCanReceiveOrder($order,$orderGoods){
        if (!in_array($order['order_status'],Order::$canReceiveOrderStatusArr)){
            $order['can_receive_order'] = CommonStatus::STATUS_DISABLED;
            $order['can_receive_order_text'] = '不可确认收货';
        }
        $canReceiveOrder = true;
        foreach ($orderGoods as $orderGood){
            if (!in_array($orderGood['delivery_status'],OrderGoods::$canReceiveOrderStatus)){
                $canReceiveOrder = false;
                break;
            }
        }
        if ($canReceiveOrder){
            $order['can_receive_order'] = CommonStatus::STATUS_ACTIVE;
            $order['can_receive_order_text'] = '可以确认收货';
        }
        else{
            $order['can_receive_order'] = CommonStatus::STATUS_DISABLED;
            $order['can_receive_order_text'] = '不可确认收货';
        }
        return $order;
    }


    private static function batchDefineOrderGoodsCanCustomerService($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::defineOrderGoodsCanCustomerService($v);
        }
        return $list;
    }
    public static function defineOrderGoodsCanCustomerService($orderGoods){
        if ($orderGoods['customer_service_status']==CommonStatus::STATUS_DISABLED&&in_array($orderGoods['delivery_status'],OrderGoods::$canCustomerServiceStatusArr)){
            $orderGoods['can_customer_service'] = CommonStatus::STATUS_ACTIVE;
            $orderGoods['can_customer_service_text'] = '允许发起售后';
        }
        else{
            $orderGoods['can_customer_service'] = CommonStatus::STATUS_DISABLED;
            $orderGoods['can_customer_service_text'] = '您已发起售后';
        }
        return $orderGoods;
    }



    public static function defineOrderDeliveryType($order){
        $order['accept_delivery_type_text'] = ArrayUtils::getArrayValue($order['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr,'');
        return $order;
    }

    public static function defineOrderStatus($order){
        $orderStatusTextArr = Order::$order_status_list;
        if ($order['order_owner']==GoodsConstantEnum::OWNER_HA){
            $orderStatusTextArr = Order::$order_status_list_for_alliance;
        }
        $order['order_status_text'] = ArrayUtils::getArrayValue($order['order_status'],$orderStatusTextArr);
        $order['pay_status_text'] = ArrayUtils::getArrayValue($order['pay_status'],Order::$pay_status_list);
        return $order;
    }

    /**
     * 设置倒计时
     * @param $order
     * @return mixed
     */
    public static function defineUnPayCountDown($order){
        if ($order['order_status']==Order::ORDER_STATUS_UN_PAY){
            $orderUnPayTime = Yii::$app->params["order.un_pay.time"];
            $countDown = $orderUnPayTime - (time()-strtotime($order['created_at']));
            $order['un_pay_count_down'] = $countDown<0?0:$countDown;
        }
        else{
            $order['un_pay_count_down'] = 0;
        }
        return $order;
    }


    /**
     * 批量组装预分润展示文本
     * @param $orders
     * @return array
     */
    public static function batchSetPreDistributeText($orders){
        if (empty($orders)){
            return [];
        }
        foreach ($orders as $k=>$v){
            $v = self::setPreDistributeText($v);
            $orders[$k]= $v;
        }
        return $orders;
    }

    /**
     * 组装预分润展示文本
     * @param $order
     * @return mixed|null
     */
    public static function setPreDistributeText($order){
        if (empty($order)){
            return null;
        }
        $preDistributeItems = [];
        $expectPreDistributeItems = [];
        if (key_exists('preDistributes',$order)){
            $preDistributes = $order['preDistributes'];
            foreach ($preDistributes as $k=>$v){
                if (in_array($v['biz_type'],[OrderPreDistribute::BIZ_TYPE_CUSTOMER])||$v['amount']>0){

                    //代理商分润不展示
                    if (in_array($v['biz_type'],[OrderPreDistribute::BIZ_TYPE_AGENT,OrderPreDistribute::BIZ_TYPE_PAYMENT_HANDLING_FEE])){
                        continue;
                    }

                    $item = [
                        'biz_type'=>$v['biz_type'],
                    ];
                    $item['biz_type_text'] = ArrayUtils::getArrayValue("{$v['biz_type']}-{$v['level']}",OrderPreDistribute::$bizTypeShowArr);
                    $item['amount'] = $v['amount'];
                    $preDistributeItems[] = $item;
                    if ($v['biz_type']==OrderPreDistribute::BIZ_TYPE_DELIVERY){
                        $expectPreDistributeItems = $item;
                        $expectPreDistributeItems['biz_type_text'] = '预估收益';
                    }
                }
            }
        }
        $order['preDistributes'] = $preDistributeItems;
        $order['expectPreDistributes'] = $expectPreDistributeItems;
        return $order;
    }

    /**
     * 补全团点地址
     * @param $orders
     */
    public static function completeDeliveryInfoList(&$orders){
        if (empty($orders)){
            return;
        }
        foreach ($orders as $k=>$v){
            $v = self::completeDeliveryInfo($v);
            $orders[$k] = $v;
        }
    }

    /**
     * 补全团点地址
     * @param $order
     * @return mixed
     */
    public static function completeDeliveryInfo($order){
        if (key_exists('delivery',$order)){
            $order['delivery_address'] = $order['delivery']['community'].$order['delivery']['address'];
        }
        return $order;
    }
}