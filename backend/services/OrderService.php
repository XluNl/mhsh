<?php


namespace backend\services;


use backend\models\BackendCommon;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use Yii;

class OrderService extends \common\services\OrderService
{
    /**
     * 获取订单详情
     * @param $orderNo
     * @param $companyId
     * @param bool $validate
     * @param $validateException
     * @param $model
     * @return array|Order|\yii\db\ActiveRecord|null
     */
    public static function getOrderDetail($orderNo,$companyId,$validate,$validateException,$model=true)
    {
        $conditions = ['order_no' => $orderNo,'company_id'=>$companyId];
        if ($model){
            $orderDetail = Order::find()->where($conditions)->with(['goods', 'logs'])->one();
        }
        else{
            $orderDetail = Order::find()->where($conditions)->with(['goods', 'logs'])->asArray()->one();
        }
        if ($validate){
            BExceptionAssert::assertNotNull($orderDetail,$validateException);
        }
        return $orderDetail;
    }

    /**
     * 获取订单
     * @param $orderNo
     * @param $companyId
     * @param bool $model
     * @param null $validateException
     * @return array|bool|Order|null
     */
    public static function requireOrder($orderNo,$companyId,$model=false,$validateException=null)
    {
        $model = parent::getOrderModel($orderNo,$model,null,$companyId);
        if ($validateException!=null){
            BExceptionAssert::assertNotNull($model,$validateException);
        }
        return $model;
    }

    public static function getViewFlow($order_status,$deliveryType){
        $displayStatusViewArr = [];
        if (in_array($order_status,[Order::ORDER_STATUS_CANCELING,Order::ORDER_STATUS_CANCELED])){
            $displayStatusViewArr[Order::ORDER_STATUS_UN_PAY] = ['order_status'=>Order::ORDER_STATUS_UN_PAY,'text'=>Order::$order_status_list[Order::ORDER_STATUS_UN_PAY]];
            $displayStatusViewArr[Order::ORDER_STATUS_CANCELING] = ['order_status'=>Order::ORDER_STATUS_CANCELING,'text'=>Order::$order_status_list[Order::ORDER_STATUS_CANCELING]];
            $displayStatusViewArr[Order::ORDER_STATUS_CANCELED] = ['order_status'=>Order::ORDER_STATUS_CANCELED,'text'=>Order::$order_status_list[Order::ORDER_STATUS_CANCELED]];
        }
        else if (in_array($order_status,[Order::ORDER_STATUS_UN_PAY,Order::ORDER_STATUS_CHECKING,Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE,Order::ORDER_STATUS_COMPLETE])){
            $displayStatusViewArr[Order::ORDER_STATUS_UN_PAY] = ['order_status'=>Order::ORDER_STATUS_UN_PAY,'text'=>Order::$order_status_list[Order::ORDER_STATUS_UN_PAY]];
            $displayStatusViewArr[Order::ORDER_STATUS_CHECKING] = ['order_status'=>Order::ORDER_STATUS_CHECKING,'text'=>Order::$order_status_list[Order::ORDER_STATUS_CHECKING]];
            $displayStatusViewArr[Order::ORDER_STATUS_PREPARE] =  ['order_status'=>Order::ORDER_STATUS_PREPARE,'text'=>Order::$order_status_list[Order::ORDER_STATUS_PREPARE]];
            if (in_array($deliveryType,[GoodsConstantEnum::DELIVERY_TYPE_SELF])){
                $displayStatusViewArr[Order::ORDER_STATUS_SELF_DELIVERY] =  ['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'text'=>Order::$order_status_list[Order::ORDER_STATUS_SELF_DELIVERY]];
            }
            else if (in_array($deliveryType,[GoodsConstantEnum::DELIVERY_TYPE_HOME,GoodsConstantEnum::DELIVERY_TYPE_EXPRESS])){
                $displayStatusViewArr[Order::ORDER_STATUS_DELIVERY] =  ['order_status'=>Order::ORDER_STATUS_DELIVERY,'text'=>Order::$order_status_list[Order::ORDER_STATUS_DELIVERY]];
            }
            $displayStatusViewArr[Order::ORDER_STATUS_RECEIVE] =  ['order_status'=>Order::ORDER_STATUS_RECEIVE,'text'=>Order::$order_status_list[Order::ORDER_STATUS_RECEIVE]];
            $displayStatusViewArr[Order::ORDER_STATUS_COMPLETE] =  ['order_status'=>Order::ORDER_STATUS_COMPLETE,'text'=>Order::$order_status_list[Order::ORDER_STATUS_COMPLETE]];
        }
        foreach ($displayStatusViewArr as $k=>$v){
            $displayStatusViewArr[$k]['show'] = self::isViewShow($k,$order_status);
            $displayStatusViewArr[$k]['activeIndex'] = $k==$order_status;
        }
        return $displayStatusViewArr;
    }


    private static function isViewShow($viewStatus,$nowStatus){
        $viewIndex = array_search($viewStatus,Order::$logicOrderStatusDisplayOrder);
        $nowIndex = array_search($nowStatus,Order::$logicOrderStatusDisplayOrder);
        $viewIndex = $viewIndex===false?-1:$viewIndex;
        $nowIndex = $nowIndex===false?-1:$nowIndex;
        if ($viewIndex<=$nowIndex){
            return true;
        }
        return false;
    }

    public static function getOrderDetailItem($model){
        $orderItems = [];
        $itemsOne = [];
        $itemsOne[] = ['title'=>'下单时间  /  支付时间','text'=>$model['created_at'].'  /  '.$model['pay_time']];
        $itemsOne[] = ['title'=>'订单金额  /  实际订单金额','text'=>Common::showAmountWithYuan($model['need_amount']).'  /  '.Common::showAmountWithYuan($model['need_amount_ac'])];
        $itemsOne[] = ['title'=>'应付订单金额  /  实际应付订单金额','text'=>Common::showAmountWithYuan($model['real_amount']).'  /  '.Common::showAmountWithYuan($model['real_amount_ac'])];
        $itemsOne[] = ['title'=>'优惠金额','text'=>Common::showAmountWithYuan($model['discount_amount'])];
        $itemsOne[] = ['title'=>'优惠信息','text'=>CouponService::decodeOrderDiscountDetail($model['discount_details'])];
        $itemsOne[] = ['title'=>'支付方式  /  支付状态','text'=>$model['pay_name'].'/'.ArrayUtils::getArrayValue($model['pay_status'],Order::$pay_status_list)];
        $itemsOne[] = ['title'=>'余额支付  /  三方支付','text'=>Common::showAmountWithYuan($model['balance_pay_amount']).'  /  '.Common::showAmountWithYuan($model['three_pay_amount'])];
        $itemsOne[] = ['title'=>'管理员备注','text'=>$model['admin_note']];
        $orderItems['orderInfo'] = $itemsOne;

        $itemsTwo = [];
        $itemsTwo[] = ['title'=>'用户名称','text'=>$model['accept_nickname']];
        $itemsTwo[] = ['title'=>'收货人姓名','text'=>$model['accept_name']];
        $itemsTwo[] = ['title'=>'收货人电话','text'=>$model['accept_mobile']];
        $itemsTwo[] = ['title'=>'收货人地址','text'=>$model['accept_community'].$model['accept_address']];
        $itemsTwo[] = ['title'=>'配送方式','text'=>ArrayUtils::getArrayValue($model['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr)];
        $orderItems['userInfo'] = $itemsTwo;

        $itemsThree = [];
        $itemsThree[] = ['title'=>'配送点名称','text'=>$model['delivery_nickname']];
        $itemsThree[] = ['title'=>'配送点联系人','text'=>$model['delivery_name']];
        $itemsThree[] = ['title'=>'配送点电话','text'=>$model['delivery_phone']];
        $itemsThree[] = ['title'=>'提货码','text'=>$model['delivery_code']];
        $orderItems['deliveryInfo'] = $itemsThree;

        $itemsFour = [];
        $orderItems['shareInfo'] = $itemsFour;
        return $orderItems;
    }


    /**
     * @param $order Order
     * @return string
     */
    public static function generateNote($order){
        $str = "";
        if ($order['order_status']==Order::ORDER_STATUS_COMPLETE){
            if ($order['real_amount']>$order['real_amount_ac']){
                $str = "已退款".Common::showAmountWithYuan($order['real_amount']-$order['real_amount_ac']);
            }
        }
        else if ($order['order_status']==Order::ORDER_STATUS_RECEIVE){
            if ($order['real_amount']>$order['real_amount_ac']){
                $str = "预计退款".Common::showAmountWithYuan($order['real_amount']-$order['real_amount_ac']);
            }
        }
        return $str;
    }


    /**
     * 管理员留言
     * @param $order Order
     * @param $adminNote
     */
    public static function addAdminNote($order,$adminNote){
        $order->admin_note = $adminNote;
        BExceptionAssert::assertTrue($order->save(),BStatusCode::createExpWithParams(BStatusCode::ORDER_UPDATE_ERROR,'订单保存失败'));
        $log = new OrderLogs();
        $log->company_id = $order['company_id'];
        $log->order_no = $order['order_no'];
        $log->role = OrderLogs::ROLE_ADMIN;
        $log->name = BackendCommon::getUserName();
        $log->user_id = BackendCommon::getUserId();
        $log->action = OrderLogs::ACTION_ADMIN_ADD_NOTE;
        $log->remark = $order->admin_note;
        BExceptionAssert::assertTrue($log->save(),BStatusCode::createExpWithParams(BStatusCode::ORDER_UPDATE_ERROR,'订单日志保存失败'));
    }

    /**
     * 订单确认
     * @param $order
     * @param $validateException RedirectParams
     * @param $operationId
     * @param $operationName
     * @throws \Exception
     */
    public static function complete($order,$validateException,$operationId,$operationName){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($result,$errorMsg) = self::completeOrder($order,$paymentSdk,OrderLogs::ROLE_ADMIN,$operationId,$operationName);
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 出库
     * @param $order Order
     * @param $operationId
     * @param $operationName
     */
    public static function deliveryOut($order,$operationId,$operationName){
        BExceptionAssert::assertTrue($order['order_status']==Order::ORDER_STATUS_PREPARE,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'订单不处于备货中'));
        $updateCount = 0;
        if ($order['accept_delivery_type']==GoodsConstantEnum::DELIVERY_TYPE_SELF){
            $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            BExceptionAssert::assertTrue($updateCount>0,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'订单状态更新失败'));
            $updateCount = OrderGoods::updateAll(['delivery_status'=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'delivery_status'=>OrderGoods::DELIVERY_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            BExceptionAssert::assertTrue($updateCount>0,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'子商品配送状态更新失败'));
        }
        else if (in_array($order['accept_delivery_type'],[GoodsConstantEnum::DELIVERY_TYPE_HOME,GoodsConstantEnum::DELIVERY_TYPE_EXPRESS,GoodsConstantEnum::DELIVERY_TYPE_ALLIANCE_SELF])){
            $updateCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            BExceptionAssert::assertTrue($updateCount>0,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'订单状态更新失败'));
            $updateCount = OrderGoods::updateAll(['delivery_status'=>OrderGoods::DELIVERY_STATUS_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'delivery_status'=>OrderGoods::DELIVERY_STATUS_PREPARE,'company_id'=>$order['company_id']]);
            BExceptionAssert::assertTrue($updateCount>0,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'子商品配送状态更新失败'));
        }
        else{
            BExceptionAssert::assertTrue(false,BStatusCode::createExpWithParams(BStatusCode::ORDER_DELIVERY_OUT_ERROR,'未知的配送方式'));
        }

        OrderLogService::addLogForSystem($order['order_no'],$order['company_id'],$operationId,$operationName,OrderLogs::ACTION_ORDER_DELIVERY_OUT,'');
    }


    /**
     * 上传实际重量
     * @param $companyId
     * @param $orderNo
     * @param $orderGoodsId
     * @param $numAc
     * @param $operatorId
     * @param $operatorName
     */
    public static function uploadWeight($companyId,$orderNo,$orderGoodsId,$numAc, $operatorId, $operatorName){
        $order = OrderService::getOrderModel($orderNo,false,null,$companyId);
        BExceptionAssert::assertTrue($order,BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,'订单不存在'));
        BExceptionAssert::assertTrue(in_array($order['order_status'],Order::$canUploadWeightStatusArr),BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,'只允许在配送或自提或送达阶段上传重量'));
        $weights = [['id'=>$orderGoodsId,'num'=>$numAc]];
        list($result,$error) = self::uploadWeightCommon($order, $weights,OrderLogs::ROLE_SYSTEM, $operatorId, $operatorName);
        BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,$error));
    }

    /**
     * 取消上传实际重量
     * @param $companyId
     * @param $orderNo
     * @param $orderGoodsId
     * @param $operatorId
     * @param $operatorName
     */
    public static function unUploadWeight($companyId,$orderNo,$orderGoodsId, $operatorId, $operatorName){
        $order = OrderService::getOrderModel($orderNo,false,null,$companyId);
        BExceptionAssert::assertTrue($order,BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,'订单不存在'));
        BExceptionAssert::assertTrue(in_array($order['order_status'],Order::$canUploadWeightStatusArr),BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,'只允许在配送或自提或送达阶段上传重量'));
        list($result,$error) = self::unUploadWeightCommon($order, [$orderGoodsId],OrderLogs::ROLE_SYSTEM, $operatorId, $operatorName);
        BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::UPLOAD_WEIGHT_ERROR,$error));
    }


    /**
     * 取消订单
     * @param $order
     * @param $validateException RedirectParams
     * @param $operationId
     * @param $operationName
     */
    public static function cancelOrder($order,$validateException,$operationId,$operationName){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            // 订单如果是团活动订单 则释放团占位
            list($success,$error) = GroupOrderService::releaseGroupRoomPlace($order);
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));

            //恢复优惠券
            list($success,$error) =  CouponService::recoveryCoupon($order['company_id'],$order['customer_id'],$order['order_no']);
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));
            //取消库存
            list($success,$error) =  parent::refreshStock($order);
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));

            //增加日志
            list($success,$error) =  OrderLogService::addOrderLogForAdmin($order,OrderLogs::ACTION_CANCEL_ORDER,$operationId,$operationName,"管理员取消订单");
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));

            //取消订单
            list($success,$error) =  parent::refreshOrderStatusToCancel($order,"管理员取消订单");
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));


            //最后退余额+三方支付
            list($success,$error) = CustomerBalanceService::adjustBalance($order,$order['balance_pay_amount'],'整单退款', $order['customer_id'], $order['accept_nickname']);
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));

            //最后三方支付
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($success,$error) = parent::refundThreePartPay($order,$paymentSdk);
            BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_CANCEL_ERROR,$error));

            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            BExceptionAssert::assertTrue(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    /**
     * 查找相关订单
     * @param $orderOwner
     * @param $orderOwnerId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getOrders($orderOwner,$orderOwnerId){
        $conditions = ['order_owner'=>$orderOwner,'order_owner_id'=>$orderOwnerId];
        return Order::find()->where($conditions)->all();
    }

    /**
     * 查找和分享者相关的订单
     * @param $popularizerId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPopularizerRelativeOrder($popularizerId){
        $conditions = [
            'or',
            ['share_rate_id_1'=>$popularizerId],
            ['share_rate_id_2'=>$popularizerId]
        ];
        return Order::find()->where($conditions)->all();
    }

}