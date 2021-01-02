<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/03/003
 * Time: 2:02
 */
namespace business\services;

use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\models\RoleEnum;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use common\services\CustomerService;
use frontend\services\OrderDisplayDomainService;
use Yii;
use yii\db\Query;

class OrderService extends \common\services\OrderService
{
    /**
     * 订单列表
     * @param $ownerType
     * @param $deliveryId
     * @param $filter
     * @param $keyword
     * @param int $pageNo
     * @param int $pageSize
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getPageFilterOrder($ownerType,$deliveryId,$filter,$keyword,$pageNo=1,$pageSize=20){
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();
        $condition = ['and',["{$orderTable}.delivery_id" => $deliveryId,'order_owner'=>$ownerType]];
        $query = Order::find()->with(['goods']);
        if (StringUtils::isNotBlank($keyword)){
            $condition[] = [
                'or',
                ['accept_mobile' => $keyword],
                ["{$orderTable}.order_no" => $keyword],
                ['accept_name' => $keyword],
                ['like',"{$orderGoodsTable}.goods_name",$keyword],
            ];
            $query->leftJoin($orderGoodsTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")
                ->select(["DISTINCT({$orderTable}.id)","{$orderTable}.*"]);
        }
        $query->offset(($pageNo - 1) * $pageSize)->limit($pageSize);
        $query->orderBy("{$orderTable}.created_at desc");
        switch ($filter) {
            case "all":
                $condition[] = ['order_status' => [
                    Order::ORDER_STATUS_UN_PAY,
                    Order::ORDER_STATUS_PREPARE,
                    Order::ORDER_STATUS_DELIVERY,
                    Order::ORDER_STATUS_SELF_DELIVERY,
                    Order::ORDER_STATUS_RECEIVE,
                    Order::ORDER_STATUS_COMPLETE,
                ]];
                $query = $query->with('evaluate');
                break;
            case "unpay":
                $condition[] = [
                    'order_status' => [ Order::ORDER_STATUS_UN_PAY],
                    'pay_status'=>Order::PAY_STATUS_UN_PAY,
                ];
                break;
            case "transport":
                $condition[] = [
                    'order_status' => [ Order::ORDER_STATUS_PREPARE],
                ];
                break;
            case "delivery":
                $condition[] = [
                    'order_status' => [ Order::ORDER_STATUS_DELIVERY],
                ];
                break;
            case "self_delivery":
                $condition[] = [
                    'order_status' => [ Order::ORDER_STATUS_SELF_DELIVERY],
                ];
                break;
            case "deal":
                $condition[] = [ 'order_status' => [
                    Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY],
                ];
                break;
            case "complete":
                $condition[] = [ 'order_status' => [
                    Order::ORDER_STATUS_RECEIVE,Order::ORDER_STATUS_COMPLETE],
                ];
                break;
            case "customer-service":
                $condition[] = [ 'customer_service_status' => Order::CUSTOMER_SERVICE_STATUS_TRUE];
                $query->orderBy("{$orderTable}.updated_at desc");
                break;
            default:
                return [];
                break;
        }
        $orders = $query->where($condition)
            ->with('preDistributes')
            ->asArray()
            ->all();
        //处理状态展示文本
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        $orders = OrderDisplayDomainService::batchSetPreDistributeText($orders);
        return $orders;
    }


    public static function requiredOrderModel($order_no,$deliveryId=null,$model=false){
        $order =  parent::getOrderModel($order_no,$model,$deliveryId,null,[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY]);
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }

    public static function requiredOrderModelByCode($deliveryCode){
        $order =  Order::find()->with('preDistributes')->where(['delivery_code'=>$deliveryCode,'order_owner'=>[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY]])->asArray()->one();
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }

    /**
     * 更新订单重量之前校验权限
     * 仅退款或退款退款的商品不允许提货
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
        $deliveryModel = DeliveryService::requiredModel($order['delivery_id']);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'订单不属于你'));
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
        $deliveryModel = DeliveryService::requiredModel($order['delivery_id']);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,'配送点不属于你'));
        self::unUploadOrders($order,$ids, $operatorId, $operatorName);
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
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]),StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'只允许在配送或自提阶段上传重量'));
        $deliveryModel = DeliveryService::requiredModel($order['delivery_id']);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$operatorId,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'订单不属于你'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $unReadyOrderGoodsArr = (new Query())->from(OrderGoods::tableName())->where( [
                'order_no'=>$orderNo,
                'delivery_status'=>OrderGoods::$unReceiveDeliveryStatus,
                'status'=>CommonStatus::STATUS_ACTIVE
            ])->all();
            $receiveError = self::generateReceiveError($unReadyOrderGoodsArr);
            ExceptionAssert::assertBlank($receiveError,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,$receiveError));
            $uploadCount = Order::updateAll(['order_status'=>Order::ORDER_STATUS_RECEIVE,'accept_time'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$order['order_no'],'order_status'=>[Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_DELIVERY]]);
            ExceptionAssert::assertTrue($uploadCount>0,StatusCode::createExpWithParams(StatusCode::RECEIVE_ERROR,'订单收货更新失败'));
            OrderLogService::addLogForDelivery($order['order_no'],$order['company_id'],$operatorId,$operatorName,OrderLogs::ACTION_ORDER_RECEIVE,'');
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
            list($result,$error) = self::uploadWeightCommon($order, $weights,OrderLogs::ROLE_DELIVERY, $operatorId, $operatorName);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::UPLOAD_WEIGHTS,$error));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
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
        $deliveryModel = DeliveryService::requiredModel($order['delivery_id']);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$userId,StatusCode::createExpWithParams(StatusCode::GET_ORDER_ERROR,'订单不属于你'));
        $orderGoods = self::getOrderGoodsModel($order['order_no']);
        $order['goods'] = $orderGoods;
        $order['customer'] = CustomerService::getModelWithUser($order['customer_id']);
        $order = OrderDisplayDomainService::defineOrderDisplayData($order);
        $order = OrderDisplayDomainService::setPreDistributeText($order);
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
            list($result,$error) = self::unUploadWeightCommon($order, $ids,OrderLogs::ROLE_DELIVERY, $operatorId, $operatorName);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::UN_UPLOAD_WEIGHTS,$error));
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
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::BATCH_UPLOAD_AND_RECEIVE_ORDER,$e->getMessage()));
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
        $deliveryModel = DeliveryService::requiredModel($order['delivery_id']);
        ExceptionAssert::assertTrue($deliveryModel['user_id']==$operatorId,BusinessException::create('订单不属于你'));
        list($result,$errorMsg) = self::uploadWeightAndReceiveOrderCommon($order,RoleEnum::ROLE_DELIVERY,$operatorId,$operatorName);
        ExceptionAssert::assertTrue($result,BusinessException::create($errorMsg));
    }

    /**
     * 获得所有合伙人当月已完成订单金额合计
     * @return array
     */
    public static function getPartnerMonthOrderSum(){

        $data = Yii::$app->cache->get('partner_month_order_sum');

        if (!$data) {
            $date = date("Y-m-d H:i:s");
            $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
            $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));

            $time = 'and `completion_time` between \'' . $startTime . '\' and \'' . $endTime . '\'';

            $partnerMonthOrderSum = (new Query())->select('d.id, d.nickname, d.phone, real_amount_sum')
                ->from(Delivery::tableName() . ' as d')
                ->join('LEFT JOIN', '(select delivery_id, sum(`real_amount`) as real_amount_sum from ' . Order::tableName() . ' as o where `order_status` = ' . Order::ORDER_STATUS_COMPLETE . ' and `order_owner` = ' . GoodsConstantEnum::OWNER_DELIVERY . ' and `delivery_id` is not null ' . $time . ' group by `delivery_id`) as o', 'o.delivery_id = d.id')
                ->where([
                    'd.status' => CommonStatus::STATUS_ACTIVE,
                    'd.auth' => Delivery::AUTH_STATUS_AUTH
                ])
                ->orderBy('real_amount_sum asc,id desc')
                ->all();

            $data = [];
            $i = 1;
            foreach ($partnerMonthOrderSum as $pmos) {
                $pmos['rankings'] = $i++;
                $pmos['real_amount_sum'] = $pmos['real_amount_sum'] ?: 0;
                array_push($data, $pmos);
            }
            Yii::$app->cache->set('partner_month_order_sum', $data, 180);
        }

        return $data;
    }


}