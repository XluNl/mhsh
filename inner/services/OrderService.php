<?php


namespace inner\services;


use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use frontend\services\OrderDisplayDomainService;
use inner\utils\ExceptionAssert;
use inner\utils\exceptions\BusinessException;
use inner\utils\StatusCode;
use Yii;

class OrderService extends \common\services\OrderService
{

    public static function getList($orderNos){
        ExceptionAssert::assertNotEmpty($orderNos, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNos不能为空'));
        $orders = self::getAllOrderModel($orderNos);
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        $orders = OrderDisplayDomainService::batchSetPreDistributeText($orders);
        return $orders;
    }

    public static function getOrderWithGoods($orderNo){
        ExceptionAssert::assertNotBlank($orderNo, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        $order = self::getOrderModel($orderNo);
        $orderGoods = self::getOrderGoodsModel($order['order_no']);
        $order['goods'] = $orderGoods;
        $order = OrderDisplayDomainService::defineOrderDisplayData($order);
        $order = OrderDisplayDomainService::setPreDistributeText($order);
        return $order;
    }

    /**
     * 配送员送达
     * @param $orderNo
     * @param $orderGoodsIds
     * @param $operationId
     * @param $operationName
     * @throws BusinessException
     */
    public static function deliveryReceiveI($orderNo,$orderGoodsIds,$operationId,$operationName){
        ExceptionAssert::assertNotEmpty($orderGoodsIds,StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,"子单id不能为空"));
        $order = self::getOrderModel($orderNo);
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]),StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,"配送中的订单才允许团点送达"));
        $orderGoodsModels = self::getOrderGoodsModel($orderNo,$orderGoodsIds);
        $realOrderGoodsIds = ArrayUtils::getColumnWithoutNull("id",$orderGoodsModels);
        $diffIds = array_diff($orderGoodsIds,$realOrderGoodsIds);
        ExceptionAssert::assertEmpty($diffIds,StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,"子单id(".implode(",", $diffIds).")不能属于此订单"));
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $remark = "配送员送达商品：";
            $flag = false;
            foreach ($orderGoodsModels as $orderGoodsModel){
                if ($orderGoodsModel['delivery_status']!=OrderGoods::DELIVERY_STATUS_SELF_DELIVERY){
                    $flag = true;
                    ExceptionAssert::assertTrue($orderGoodsModel['delivery_status']==OrderGoods::DELIVERY_STATUS_DELIVERY,StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,"子单id".$orderGoodsModel['id']."状态不处于配送中"));
                    $updateCount = OrderGoods::updateAll(['delivery_status'=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$orderGoodsModel['id'],'delivery_status'=>OrderGoods::DELIVERY_STATUS_DELIVERY]);
                    ExceptionAssert::assertTrue($updateCount>0,StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,"子单id".$orderGoodsModel['id']."状态不处于配送中"));
                    $remark = "{$remark}{$orderGoodsModel['schedule_name']}-{$orderGoodsModel['goods_name']}-{$orderGoodsModel['sku_name']}(数量{$orderGoodsModel['num']});";
                }
            }
            $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$orderNo,'order_status'=>Order::ORDER_STATUS_DELIVERY]);
            if ($flag||$updateCount>0){
                OrderLogService::addLogForCourier($orderNo,$order['company_id'],$operationId,$operationName,OrderLogs::ACTION_COURIER_DELIVERY_RECEIVE,$remark);
            }
            $transaction->commit();
        }
        catch (BusinessException $e){
            Yii::error("deliveryReceiveI BusinessException:".$e->getMessage());
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error("deliveryReceiveI Exception:".$e->getMessage());
            throw StatusCode::createExpWithParams(StatusCode::DELIVERY_RECEIVE_ERROR,$e->getMessage());
        }
    }
}