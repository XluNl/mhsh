<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/03/003
 * Time: 2:02
 */
namespace alliance\services;

use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\models\RoleEnum;
use common\utils\DateTimeUtils;
use frontend\services\OrderDisplayDomainService;
use Yii;
use yii\db\Query;

class OrderService extends \common\services\OrderService
{
    /**
     * 订单列表
     * @param $allianceId
     * @param $filter
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getPageFilterOrder($allianceId, $filter, $pageNo=1, $pageSize=20){
        $condition = ['order_owner'=>GoodsConstantEnum::OWNER_HA,'order_owner_id' => $allianceId];
        $query = Order::find()->with(['goods','delivery'])->offset(($pageNo - 1) * $pageSize)->limit($pageSize);
        $query->orderBy("created_at desc");
        switch ($filter) {
            case "all":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_UN_PAY,
                    Order::ORDER_STATUS_PREPARE,
                    Order::ORDER_STATUS_DELIVERY,
                    Order::ORDER_STATUS_SELF_DELIVERY,
                    Order::ORDER_STATUS_RECEIVE,
                    Order::ORDER_STATUS_COMPLETE,
                ];
                $query = $query->with('evaluate');
                break;
            case "unpay":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_UN_PAY,
                ];
                $condition['pay_status'] = Order::PAY_STATUS_UN_PAY;
                break;
            case "uncheck":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_PREPARE,
                ];
                break;
            case "delivery":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_DELIVERY,
                ];
                break;
            case "self_delivery":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_SELF_DELIVERY,
                ];
                break;
            case "deal":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_DELIVERY,
                    Order::ORDER_STATUS_SELF_DELIVERY,
                ];
                break;
            case "complete":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_RECEIVE,
                    Order::ORDER_STATUS_COMPLETE,
                ];
                break;
            case "customer-service":
                $condition['customer_service_status'] = Order::CUSTOMER_SERVICE_STATUS_TRUE;
                $query->orderBy("updated_at desc");
                break;
            default:
                return [];
                break;
        }
        $orders = $query->where($condition)
            ->asArray()
            ->all();
        //处理状态展示文本
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        return $orders;
    }


    public static function requiredOrderModel($order_no,$allianceId=null,$model=false){
        $order =  parent::getOrderModel($order_no,$model,null,null,GoodsConstantEnum::OWNER_HA,$allianceId);
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }

    public static function requiredOrderModelByCode($deliveryCode){
        $order =  (new Query())->from(Order::tableName())->where(['delivery_code'=>$deliveryCode,'order_owner'=>GoodsConstantEnum::OWNER_HA])->one();
        $order = $order===false?null:$order;
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }

    /**
     * 更新订单重量之前校验权限
     * @param $orderNo
     * @param $weights
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     * @throws \yii\db\Exception
     */
    public static function uploadWeight($orderNo, $weights, $operatorId, $operatorName){
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        ExceptionAssert::assertNotEmpty($weights,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'weights不能为空'));
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]),StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'只允许在配送或自提阶段上传重量'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'订单不属于你'));
        self::uploadOrders($order,$weights,$operatorId,$operatorName);
    }

    /**
     * 取消上传订单重量之前校验权限
     * @param $orderNo
     * @param $ids
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     * @throws \Exception
     */
    public static function unUploadWeight($orderNo, $ids, $operatorId, $operatorName){
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        ExceptionAssert::assertNotEmpty($ids,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'ids不能为空'));
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]),StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'只允许在配送或自提阶段上传重量'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'配送点不属于你'));
        self::unUploadOrders($order,$ids, $operatorId, $operatorName);
    }



    /**
     * 联盟发货
     * @param $orderNo
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     */
    public static function deliveryOut($orderNo,$operatorId,$operatorName){
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue($order['order_status']==Order::ORDER_STATUS_PREPARE,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,'订单已发货'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,'订单不属于你'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $uploadCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['order_no'=>$order['order_no'],'order_status'=>Order::ORDER_STATUS_PREPARE]);
            ExceptionAssert::assertTrue($uploadCount>0,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,'订单发货更新失败'));
            $uploadCount = OrderGoods::updateAll(['delivery_status'=>OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['order_no'=>$order['order_no'],'delivery_status'=>OrderGoods::DELIVERY_STATUS_PREPARE]);
            ExceptionAssert::assertTrue($uploadCount>0,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,'订单商品发货更新失败'));
            OrderLogService::addLogForAlliance($order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_ALLIANCE_DELIVERY_OUT,'');
            $transaction->commit();
        }
        catch (BusinessException $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::DELIVERY_OUT_ERROR,$e->getMessage()));
        }
    }

    /**
     * 确认送达
     * @param $orderNo
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     */
    public static function receive($orderNo,$operatorId,$operatorName){
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]),StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'只允许在配送或自提阶段订单送达'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'订单不属于你'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $unReadyOrderGoodsArr = (new Query())->from(OrderGoods::tableName())->where( [
                'order_no'=>$orderNo,
                'delivery_status'=>OrderGoods::$unReceiveDeliveryStatus,
                'status'=>CommonStatus::STATUS_ACTIVE
            ])->all();
            $receiveError = self::generateReceiveError($unReadyOrderGoodsArr);
            ExceptionAssert::assertBlank($receiveError,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,$receiveError));
            $uploadCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_RECEIVE,'accept_time'=>DateTimeUtils::parseStandardWLongDate(time()),'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['order_no'=>$order['order_no'],'order_status'=>[Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_DELIVERY]]);
            ExceptionAssert::assertTrue($uploadCount>0,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'订单收货更新失败'));
            OrderLogService::addLogForAlliance($order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_RECEIVE,'');
            $transaction->commit();
        }
        catch (BusinessException $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,$e->getMessage()));
        }
    }

    /**
     * 商品确认无货
     * @param $orderNo
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     */
    public static function noStock($orderNo,$operatorId,$operatorName){
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'orderNo不能为空'));
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue($order['order_status']==Order::ORDER_STATUS_PREPARE,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,'只允许待确认状态操作'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,'订单不属于你'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            // 订单如果是团活动订单 则释放团占位
            list($success,$error) = GroupOrderService::releaseGroupRoomPlace($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));

            //恢复优惠券
            list($success,$error) =  CouponService::recoveryCoupon($order['company_id'],$order['customer_id'],$order['order_no']);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));
            //取消库存
            list($success,$error) =  parent::refreshStock($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));

            //增加日志
            list($success,$error) =  OrderLogService::addOrderLogForAlliance($order,OrderLogs::ACTION_ORDER_ALLIANCE_NO_STOCK,$operatorId,$operatorName,"商家无货退款");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));

            //取消订单
            list($success,$error) =  parent::refreshOrderStatusToCancel($order,"商家无货退款");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));


            //退余额
            list($success,$error) = CustomerBalanceService::adjustBalance($order,$order['balance_pay_amount'],'商家无货退款', $order['customer_id'], $order['accept_nickname']);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));

            //最后三方支付
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($success,$error) = parent::refundThreePartPay($order,$paymentSdk);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$error));

            $transaction->commit();
        }
        catch (BusinessException $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ORDER_NO_STOCK,$e->getMessage()));
        }
    }



    /**
     * 更新订单实际重量
     * @param $order
     * @param $weights
     * @param $operatorId
     * @param $operatorName
     * @throws \yii\db\Exception
     */
    private static function uploadOrders($order, $weights, $operatorId, $operatorName){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = self::uploadWeightCommon($order, $weights,OrderLogs::ROLE_ALLIANCE, $operatorId, $operatorName);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,$error));
            $transaction->commit();
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,$e->getMessage()));
        }
    }



    public static function getOrderWithGoods($deliveryCode,$userId){
        ExceptionAssert::assertNotBlank($deliveryCode,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'deliveryCode不能为空'));
        $deliveryCode = strtoupper($deliveryCode);
        $order = self::requiredOrderModelByCode($deliveryCode);
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$userId,StatusCode::createExpWithParams(StatusCode::GET_ORDER_ERROR,'订单不属于你'));
        $orderGoods = self::getOrderGoodsModel($order['order_no']);
        $order['goods'] = $orderGoods;
        $order = OrderDisplayDomainService::defineOrderDisplayData($order);
        return $order;
    }

    /**
     * 取消提货
     * @param $order
     * @param $ids
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     * @throws \Exception
     */
    private static function unUploadOrders($order,$ids, $operatorId, $operatorName){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = self::unUploadWeightCommon($order, $ids,OrderLogs::ROLE_ALLIANCE, $operatorId, $operatorName);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::UN_UPLOAD_WEIGHTS,$error));
            $transaction->commit();
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::UN_UPLOAD_WEIGHTS,$e->getMessage()));
        }
    }

    /**
     * 批量上传重量并确认送达
     * @param $orderNos
     * @param $operatorId
     * @param $operatorName
     */
    public static function batchUploadWeightAndReceiveOrder($orderNos,$operatorId,$operatorName){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($orderNos as $orderNo){
                self::uploadWeightAndReceiveOrder($orderNo,$operatorId,$operatorName);
            }
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::BATCH_UPLOAD_AND_RECEIVE_ORDER,$e->getMessage()));
        }
    }



    /**
     * 上传重量并确认送达
     * @param $orderNo
     * @param $companyId
     * @param $operatorId
     * @param $operatorName
     */
    private static function uploadWeightAndReceiveOrder($orderNo,$operatorId,$operatorName){
        $order = self::requiredOrderModel($orderNo);
        ExceptionAssert::assertTrue($order['order_owner']==GoodsConstantEnum::OWNER_HA, BusinessException::create('不是联盟订单'));
        $allianceModel = AllianceService::requiredModel($order['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$operatorId, BusinessException::create('订单不属于你'));
        list($result,$errorMsg) = self::uploadWeightAndReceiveOrderCommon($order,RoleEnum::ROLE_HA,$operatorId,$operatorName);
        ExceptionAssert::assertTrue($result,BusinessException::create($errorMsg));
    }
}