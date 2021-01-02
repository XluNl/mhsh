<?php


namespace business\services;


use common\models\Delivery;
use common\models\Order;
use common\models\OrderGoods;
use common\services\UserService;
use common\utils\StringUtils;
use yii\db\Query;

class PushMessageService
{
    /**
     * 团长发送到货通知公众号模板消息(用户)
     * @param $deliveryId
     * @param $time
     * @return int
     */
    public static function prepareSendTemplate($deliveryId, $time)
    {
        $orderTable = Order::tableName();//订单表
        $orderGoodsTable = OrderGoods::tableName();//订单商品表
        $deliveryTable = Delivery::tableName();//配送表
        $salesList = (new Query())
            ->from($orderTable)
            ->leftJoin($orderGoodsTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->leftJoin($deliveryTable,"{$orderTable}.delivery_id={$deliveryTable}.id")
            ->select("{$orderTable}.order_no,
            {$orderTable}.accept_name,
            {$orderTable}.delivery_name,
            {$orderTable}.delivery_phone,
            {$orderTable}.prepay_id,
            {$orderTable}.customer_id,
            {$deliveryTable}.community,
            {$deliveryTable}.address")
            ->where([
                "{$orderTable}.delivery_id"=>$deliveryId,
                "{$orderGoodsTable}.expect_arrive_time"=>$time,
                "{$orderGoodsTable}.delivery_status"=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,
                "{$orderTable}.order_status"=>Order::ORDER_STATUS_SELF_DELIVERY
            ])
            ->groupBy("{$orderTable}.order_no")
            ->all();//团员待取货订单
        if($salesList){
            //获取用户公众号openid开始
            $customerIdList = array_column($salesList,'customer_id');//所有下单用户customer_id
            $customerIdsToOpenIdsMap = UserService::getMulCustomerOfficialOpenId($customerIdList) ;
            //获取用户公众号openid结束

            $num = 0;
            foreach($salesList as $v){
                //发送公众号模板消息开始
                if(StringUtils::isNotBlank($customerIdsToOpenIdsMap[$v['customer_id']])){
                    $data = [
                        'first'    => ['value' => '尊敬的用户:'.$v['employee_name'].'您好,您有订单商品已到团长收货处，请安排好时间前去领取','color' => "#743A3A"],
                        'keyword1' => ['value' => $v['order_no'],'color'=>'#0000FF'],//订单编号
                        'keyword2' => ['value' => $v['community'] . $v['address'],'color' => '#0000FF'],//提货地址
                        'keyword3'   => ['value' => $time,'color' => '#743A3A'],//提货时间
                    ];
                    $template      = [
                        'touser'      => $customerIdsToOpenIdsMap[$v['customer_id']],
                        'template_id' => \Yii::$app->params["officialAccountTemplateIds"]["notifyCustomerToGet"],
                        'url'         => '',
                        'topcolor'    => '#0000',
                        'data'        => $data
                    ];
                    $res = \Yii::$app->officialWechat->app->template_message->send($template);
                    if(!empty($res)&&$res['errcode']==0){
                        $num++;
                    }
                }
            }
        }
        $num = isset($num) ? $num : 0;
        return $num;
    }


    /**
     * 团员是否有配送订单
     * @param $deliveryId
     * @param $time
     * @return bool|int|string|null
     */
    public static function getWaitNoticeOrderCount($deliveryId, $time){
        $orderTable = Order::tableName();//订单表
        $orderGoodsTable = OrderGoods::tableName();//订单商品表
        return (new Query())->from($orderTable)
            ->leftJoin($orderGoodsTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->select("DISTINCT({$orderTable}.order_no)")
            ->where([
                "{$orderTable}.delivery_id"=>$deliveryId,
                "{$orderGoodsTable}.expect_arrive_time"=>$time,
                "{$orderGoodsTable}.delivery_status"=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,
                "{$orderTable}.order_status"=>Order::ORDER_STATUS_SELF_DELIVERY
            ])
            ->count();//团员待取货订单数量
    }
}