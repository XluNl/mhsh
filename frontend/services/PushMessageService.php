<?php


namespace frontend\services;


use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\services\UserService;
use common\utils\StringUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use yii\db\Query;

class PushMessageService
{
    /**
     * 支付成功回调发送公众号消息给团长
     * @param $orderNo
     * @return int
     */
    public static function pushCommander($orderNo){
        $orderTable = Order::tableName();//订单表
        $orderGoodsTable = OrderGoods::tableName();//订单商品表
        $salesList = (new Query())
            ->from($orderTable)
            ->leftJoin($orderGoodsTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")
            ->select("
            {$orderTable}.order_owner,
            {$orderTable}.order_owner_id,
            {$orderTable}.accept_name,
            {$orderTable}.delivery_name,
            {$orderTable}.customer_id,
            {$orderTable}.delivery_id,
            {$orderTable}.accept_community,
            {$orderTable}.accept_address,
            {$orderTable}.pay_amount,
            {$orderTable}.pay_time,
            {$orderGoodsTable}.goods_name,
            {$orderGoodsTable}.num"
            )
            ->where([
                "{$orderTable}.order_no"=>$orderNo
            ])
            ->all();//团员订单
        if(!empty($salesList)){
            $title = "";//商品标题数量
            foreach($salesList as $value){
                $title .= $value['goods_name'] . '*' . $value['num'] . ',';
            }
            //获取发送信息开始
            $acceptName = isset($salesList[0]['accept_name']) ? $salesList[0]['accept_name'] : '';//下单用户名称
            $deliveryName = isset($salesList[0]['delivery_name']) ? $salesList[0]['delivery_name'] : '';//代送点联系人
            $title = isset($title) ? $title : '';//商品标题数量
            $deliveryId = isset($salesList[0]['delivery_id']) ? $salesList[0]['delivery_id'] : '';//代送点id
            $payAmount = isset($salesList[0]['pay_amount']) ? Common::showAmountWithYuan($salesList[0]['pay_amount']) : '';//支付金额
            $payTime = isset($salesList[0]['pay_time']) ? $salesList[0]['pay_time'] : '';//支付时间
            $acceptCommunity = isset($salesList[0]['accept_community']) ? $salesList[0]['accept_community'] : '';//收货人小区
            $acceptAddress = isset($salesList[0]['accept_address']) ? $salesList[0]['accept_address'] : '';//收货地址
            //获取发送信息结束
            if (in_array($salesList[0]['order_owner'],[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY])){
                self::sendOrderNotifyForDelivery($deliveryId, $deliveryName, $acceptName, $title, $orderNo, $payAmount, $payTime, $acceptCommunity, $acceptAddress);
            }
            else if (in_array($salesList[0]['order_owner'],[GoodsConstantEnum::OWNER_HA])){
                self::sendOrderNotifyForAlliance($salesList[0]['order_owner_id'], $deliveryName, $acceptName, $title, $orderNo, $payAmount, $payTime, $acceptCommunity, $acceptAddress);
            }
        }
        return true;
    }

    /**
     * @param $deliveryId
     * @param $deliveryName
     * @param $acceptName
     * @param $title
     * @param $orderNo
     * @param $payAmount
     * @param $payTime
     * @param $acceptCommunity
     * @param $acceptAddress
     */
    public static function sendOrderNotifyForDelivery($deliveryId, $deliveryName, $acceptName, $title, $orderNo, $payAmount, $payTime, $acceptCommunity, $acceptAddress)
    {
        if (StringUtils::isNotBlank($deliveryId)) {
            $gzOpenid = UserService::getDeliveryOfficialOpenId($deliveryId);
            if ($gzOpenid) {
                $data = [
                    'first' => ['value' => '尊敬的团长' . $deliveryName . ',您的团员' . $acceptName . '下单成功', 'color' => "#743A3A"],
                    'keyword1' => ['value' => $title, 'color' => '#0000FF'],//订单商品
                    'keyword2' => ['value' => $orderNo, 'color' => '#0000FF'],//订单编号
                    'keyword3' => ['value' => $payAmount, 'color' => '#FF0000'],//订单金额
                    'keyword4' => ['value' => $payTime, 'color' => '#743A3A'],//支付时间
                    'keyword5' => ['value' => $acceptCommunity . $acceptAddress, 'color' => '#000000'],//收货信息
                ];
                $template = [
                    'touser' => $gzOpenid,
                    'template_id' => \Yii::$app->params["officialAccountTemplateIds"]["buySuccessForNotifyDelivery"],
                    'url' => '',
                    'topcolor' => '#0000',
                    'data' => $data
                ];
                $res = \Yii::$app->officialWechat->app->template_message->send($template);
                ExceptionAssert::assertTrue(!empty($res) && $res['errcode'] == 0, BusinessException::create($res['errmsg']));
            }
        }
    }

    /**
     * @param $order_owner_id
     * @param $deliveryName
     * @param $acceptName
     * @param $title
     * @param $orderNo
     * @param $payAmount
     * @param $payTime
     * @param $acceptCommunity
     * @param $acceptAddress
     */
    public static function sendOrderNotifyForAlliance($order_owner_id, $deliveryName, $acceptName, $title, $orderNo, $payAmount, $payTime, $acceptCommunity, $acceptAddress)
    {
        $allianceId = $order_owner_id;
        if (StringUtils::isNotBlank($allianceId)) {
            $gzOpenid = UserService::getAllianceOfficialOpenId($allianceId);
            if ($gzOpenid) {
                $data = [
                    'first' => ['value' => '尊敬的联盟用户' . $deliveryName . ',您的用户' . $acceptName . '下单成功', 'color' => "#743A3A"],
                    'keyword1' => ['value' => $title, 'color' => '#0000FF'],//订单商品
                    'keyword2' => ['value' => $orderNo, 'color' => '#0000FF'],//订单编号
                    'keyword3' => ['value' => $payAmount, 'color' => '#FF0000'],//订单金额
                    'keyword4' => ['value' => $payTime, 'color' => '#743A3A'],//支付时间
                    'keyword5' => ['value' => $acceptCommunity . $acceptAddress, 'color' => '#000000'],//收货信息
                ];
                $template = [
                    'touser' => $gzOpenid,
                    'template_id' => \Yii::$app->params["officialAccountTemplateIds"]["buySuccessForNotifyDelivery"],
                    'url' => '',
                    'topcolor' => '#0000',
                    'data' => $data
                ];
                $res = \Yii::$app->officialWechat->app->template_message->send($template);
                ExceptionAssert::assertTrue(!empty($res) && $res['errcode'] == 0, BusinessException::create($res['errmsg']));
            }
        }
    }

}