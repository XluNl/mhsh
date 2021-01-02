<?php


namespace frontend\services;


use common\models\Order;
use common\models\OrderPreDistribute;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class OrderPreDistributeService
{
    /**
     * 保存分润信息
     * @param $order Order
     * @param $bizType
     * @param $bizId
     * @param $level
     * @param $amount
     */
    public static function create($order,$bizType,$bizId,$level,$amount){
        $pre = new OrderPreDistribute();
        $pre->biz_type = $bizType;
        $pre->biz_id = $bizId;
        $pre->order_no = $order['order_no'];
        $pre->order_amount = $order['real_amount'];
        $pre->order_time = $order['created_at'];
        $pre->level = $level;
        $pre->amount=$amount;
        $pre->amount_ac=0;
        ExceptionAssert::assertTrue($pre->save(), StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '订单分润保存失败'));
    }
}