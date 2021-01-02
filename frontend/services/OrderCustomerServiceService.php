<?php

namespace frontend\services;

use common\models\Common;
use common\models\CommonStatus;
use common\models\Order;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceGoods;
use common\models\OrderCustomerServiceLog;
use common\models\OrderGoods;
use common\models\SystemOptions;
use common\utils\DateTimeUtils;
use common\utils\PriceUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderCustomerServiceService extends \common\services\OrderCustomerServiceService
{


    /**
     * 售后单列表
     * @param $ownerTypes
     * @param $customerId
     * @param $status
     * @param $keyword
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getListPageFilter($ownerTypes,$customerId,$status,$keyword,$pageNo=1,$pageSize=20)
    {
        $orderTable = Order::tableName();
        $orderCustomerServiceTable = OrderCustomerService::tableName();
        $condition = ['and',["{$orderTable}.customer_id" => $customerId]];
        if (StringUtils::isNotEmpty($ownerTypes)){
            $condition[] = ["{$orderTable}.order_owner" => $ownerTypes];
        }
        if (StringUtils::isNotBlank($keyword)){
            $condition[] = [
                'or',
                ["{$orderTable}.accept_mobile" => $keyword],
                ["{$orderTable}.order_no" => $keyword],
                ["{$orderTable}.accept_name" => $keyword]
            ];
        }
        if (StringUtils::isNotBlank($status)){
            if ($status==OrderCustomerService::STATUS_UN_DEAL){
                $condition[] = ["{$orderCustomerServiceTable}.status" => OrderCustomerService::STATUS_UN_DEAL];
            }
            else{
                $condition[] = ["{$orderCustomerServiceTable}.status" => [OrderCustomerService::STATUS_ACCEPT,OrderCustomerService::STATUS_CANCEL,OrderCustomerService::STATUS_DENY]];
            }
        }
        $customerServiceModels = OrderCustomerService::find()
            ->with(['logs','order'])
            ->leftJoin($orderTable,"{$orderTable}.order_no={$orderCustomerServiceTable}.order_no")
            ->where($condition)->orderBy("{$orderCustomerServiceTable}.created_at desc")
            ->offset(($pageNo - 1) * $pageSize)->limit($pageSize)->asArray()->all();
        $customerServiceModels = self::fillGoodsInfo($customerServiceModels);
        $customerServiceModels = OrderCustomerServiceDisplayVOService::batchSetDisplayVOB($customerServiceModels);
        return $customerServiceModels;
    }

    /**
     * 申请售后
     * @param $orderNo
     * @param $orderGoodsIds
     * @param $type
     * @param $remark
     * @param $images
     * @param $claimAmountArr
     * @throws BusinessException
     * @throws \yii\db\Exception
     */
    public static function apply($orderNo, $orderGoodsIds, $type,$remark,$images,$claimAmountArr=[])
    {
        ExceptionAssert::assertTrue(key_exists($type, OrderCustomerService::$typeArr), StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR, 'type'));
        $customerModel = FrontendCommon::requiredActiveCustomer();

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orderModel = self::checkPermission($customerModel, $orderNo, $orderGoodsIds);
            $customerService = new OrderCustomerService();
            $customerService->type = $type;
            $customerService->status = OrderCustomerService::STATUS_UN_DEAL;
            $customerService->order_no = $orderNo;
            $customerService->customer_id = $customerModel['id'];
            $customerService->delivery_id = $orderModel['delivery_id'];
            $customerService->company_id = $orderModel['company_id'];
            $customerService->remark = $remark;
            $customerService->images = $images;
            ExceptionAssert::assertTrue($customerService->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "售后记录创建失败"));

            foreach ($orderGoodsIds as $k=> $orderGoodsId) {
                $customerServiceGoods = new OrderCustomerServiceGoods();
                $customerServiceGoods->customer_service_id = $customerService->id;
                $customerServiceGoods->order_goods_id = $orderGoodsId;
                $orderGoodsModel = OrderGoodsService::getModels($orderGoodsId,$orderModel['order_no'],$orderModel['company_id'],false);
                if ($type==OrderCustomerService::TYPE_REFUND_CLAIM){
                    list($acNum,$num,$acAmount,$amount) = self::calcCustomerServiceNumAndAmount($orderGoodsModel,Common::setAmount($claimAmountArr[$k]));
                    $customerServiceGoods->order_goods_num = $acNum;
                    $customerServiceGoods->order_goods_order_num = $num;
                    $customerServiceGoods->order_goods_ac_amount = $acAmount;
                    $customerServiceGoods->order_goods_order_amount = $amount;
                }
                else if ($type==OrderCustomerService::TYPE_REFUND_CHANGE){
                    $customerServiceGoods->order_goods_num =$orderGoodsModel['num'] ;
                    $customerServiceGoods->order_goods_order_num= $orderGoodsModel['num'];
                    $customerServiceGoods->order_goods_ac_amount= $orderGoodsModel['amount'];
                    $customerServiceGoods->order_goods_order_amount= $orderGoodsModel['amount'];
                }
                else{
                    $customerServiceGoods->order_goods_num = 0 ;
                    $customerServiceGoods->order_goods_order_num= $orderGoodsModel['num'];
                    $customerServiceGoods->order_goods_ac_amount= 0;
                    $customerServiceGoods->order_goods_order_amount= $orderGoodsModel['amount'];
                }
                ExceptionAssert::assertTrue($customerServiceGoods->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "售后商品记录创建失败"));
                $updateCount = OrderGoods::updateAll(['customer_service_status'=>CommonStatus::STATUS_ACTIVE,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$orderGoodsId,'order_no'=>$orderNo]);
                ExceptionAssert::assertTrue($updateCount>0, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "售后商品记录创建失败"));
            }

            $customerServiceLog = new OrderCustomerServiceLog();
            $customerServiceLog->customer_service_id = $customerService->id;
            $customerServiceLog->operator_id = $customerModel['id'];
            $customerServiceLog->operator_name = $customerModel['nickname'];
            $customerServiceLog->action = OrderCustomerServiceLog::ACTION_APPLY;
            ExceptionAssert::assertTrue($customerServiceLog->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "售后日志记录创建失败"));
            $updateCount = Order::updateAll(['customer_service_status'=>Order::CUSTOMER_SERVICE_STATUS_TRUE,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['order_no'=>$orderNo]);
            ExceptionAssert::assertTrue($updateCount>0, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "订单售后状态标记失败"));
            $transaction->commit();
        } catch (BusinessException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, $e->getMessage()));
        }
    }

    /**
     * 客户售后列表
     * @param $orderNo
     * @param $customerId
     * @return array
     */
    public static function getListByOrder($orderNo, $customerId)
    {
        $orderModel = OrderService::requiredOrderModel($orderNo,$customerId);
        $customerServiceModels = OrderCustomerService::find()->where(['order_no'=>$orderNo,'customer_id' => $customerId])->with('logs')->orderBy('id desc')
            ->asArray()->all();
        if (empty($customerServiceModels)) {
            return [];
        }
        $customerServiceIds = ArrayHelper::getColumn($customerServiceModels, "id");
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())
            ->leftJoin(OrderGoods::tableName(), OrderCustomerServiceGoods::tableName() . '.order_goods_id=' . OrderGoods::tableName() . '.id')
            ->where(['customer_service_id' => $customerServiceIds])->all();
        foreach ($customerServiceModels as $k1 => $v1) {
            foreach ($customerServiceGoodsModels as $v2) {
                if ($v1['id'] == $v2['customer_service_id']) {
                    if (!key_exists('goods', $v1)) {
                        $v1['goods'] = [];
                    }
                    $customerServiceModels[$k1]['goods'][] = $v2;
                    $customerServiceModels[$k1]['order'] = $orderModel;
                }
            }
        }

        $customerServiceModels = OrderCustomerServiceDisplayVOService::batchSetDisplayVO($customerServiceModels);
        OrderCustomerServiceDisplayVOService::batchSetLogsVO($customerServiceModels);
        $orderModel['customer_service'] = $customerServiceGoodsModels;
        return $customerServiceModels;
    }

    /**
     * 取消售后
     * @param $cModel
     * @param $customerServiceId
     * @throws BusinessException
     * @throws \Exception
     */
    public static function cancel($cModel, $customerServiceId)
    {
        $transaction = Yii::$app->db->beginTransaction();
        $customerServiceModel = OrderCustomerService::find()->where(['id' => $customerServiceId, 'customer_id' => $cModel['id']])->one();
        ExceptionAssert::assertNotNull($customerServiceModel, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_CANCEL_ERROR, "售后记录不存在"));
        ExceptionAssert::assertTrue($customerServiceModel['status']==OrderCustomerService::STATUS_UN_DEAL, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_CANCEL_ERROR, "已不支持取消"));
        try {
            $customerServiceModel->status = OrderCustomerService::STATUS_CANCEL;
            ExceptionAssert::assertTrue($customerServiceModel->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_CANCEL_ERROR, "售后记录更新失败"));
            $customerServiceLog = new OrderCustomerServiceLog();
            $customerServiceLog->customer_service_id = $customerServiceModel['id'];
            $customerServiceLog->operator_id = $cModel['id'];
            $customerServiceLog->operator_name = $cModel['nickname'];
            $customerServiceLog->action = OrderCustomerServiceLog::ACTION_CANCEL;
            ExceptionAssert::assertTrue($customerServiceLog->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_CANCEL_ERROR, "售后日志记录创建失败"));

            $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())->where(['customer_service_id'=>$customerServiceModel['id']])->all();
            ExceptionAssert::assertNotNull($customerServiceGoodsModels, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_CANCEL_ERROR, "售后记录错误，未包含售后商品"));
            foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
                $updateCount = OrderGoods::updateAll(['customer_service_status'=>CommonStatus::STATUS_DISABLED,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['id'],'order_no'=>$customerServiceModel['order_no']]);
                ExceptionAssert::assertTrue($updateCount>0, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "售后商品记录创建失败"));
            }

        } catch (BusinessException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, $e->getMessage()));
        }
    }

    private static function checkPermission($cModel, $orderNo, $orderGoodsIds)
    {
        ExceptionAssert::assertNotNull($cModel, StatusCode::createExp(StatusCode::CUSTOMER_NOT_EXIST));
        $orderModel = (new Query())->from(Order::tableName())->where(['order_no' => $orderNo, 'customer_id' => $cModel['id']])->one();
        ExceptionAssert::assertNotNull($orderModel, StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        ExceptionAssert::assertTrue(in_array($orderModel['order_status'],
            [Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY, Order::ORDER_STATUS_RECEIVE]),
            StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        $orderGoodsModels = (new Query())->from(OrderGoods::tableName())->where(['id' => $orderGoodsIds, 'order_no' => $orderNo])->all();
        ExceptionAssert::assertNotEmpty($orderGoodsModels, StatusCode::createExp(StatusCode::ORDER_GOODS_NOT_EXIST));
        $orderCustomerGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())
            ->leftJoin(OrderCustomerService::tableName(), OrderCustomerServiceGoods::tableName() . ".customer_service_id=" . OrderCustomerService::tableName() . ".id")->where([
                "AND",
                ['status'=>[OrderCustomerService::STATUS_UN_DEAL, OrderCustomerService::STATUS_ACCEPT]],
                ['order_goods_id' => $orderGoodsIds],
            ])
            ->select("order_goods_id")->all();
        ExceptionAssert::assertEmpty($orderCustomerGoodsModels, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, implode(",", ArrayHelper::getColumn($orderCustomerGoodsModels,'id')) . '已申请售后'));
        return $orderModel;
    }


    /**
     * 计算金额转化为收货数量
     * @param $orderGoodsModel
     * @param $claimAmount
     * @return array
     */
    public static function calcCustomerServiceNumAndAmount($orderGoodsModel,$claimAmount){
        ExceptionAssert::assertNotNull($orderGoodsModel, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "订单中无此商品"));
        $claimUpRatio = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ORDER_CLAIM_UP_RATIO);
        ExceptionAssert::assertTrue($claimAmount*100/$orderGoodsModel['amount']<=$claimUpRatio, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_ERROR, "超过赔付比例{$claimUpRatio}%"));

        $numAc = $orderGoodsModel['num']*($orderGoodsModel['amount']-$claimAmount)/$orderGoodsModel['amount'];
        $numAc = PriceUtils::accurateTo4Point($numAc);
        $amountAc = PriceUtils::accurateToTen(($numAc*$orderGoodsModel['amount'])/$orderGoodsModel['num']);
        return [$numAc,$orderGoodsModel['num'],$amountAc,$orderGoodsModel['amount']];
    }




}