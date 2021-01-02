<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use common\models\Common;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceGoods;
use common\models\OrderCustomerServiceLog;
use common\models\OrderGoods;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\PriceUtils;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\Query;

class OrderCustomerServiceService extends \common\services\OrderCustomerServiceService
{
    /**
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function renameImages($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        GoodsDisplayDomainService::batchRenameImageUrl($models,'images');
        $dataProvider->setModels($models);
        return $dataProvider;
    }


    public static function getCustomerServiceDetail($customerServiceId,$companyId,$validateException){
        $model = OrderCustomerService::find()->where(['id' => $customerServiceId,'company_id'=>$companyId])->with(['order','delivery', 'customerServiceGoods', 'logs'])->one();
        if ($validateException!=null){
            BExceptionAssert::assertNotNull($model,$validateException);
        }
        return $model;
    }

    /**
     * 审核操作
     * @param $customerServiceId
     * @param $commander
     * @param $company_id
     * @param $operatorId
     * @param $operatorName
     * @param $auditRemark
     * @param $validateException RedirectParams
     * @throws \Exception
     */
    public static function operate($customerServiceId,$commander,$company_id,$operatorId,$operatorName,$auditRemark,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[OrderCustomerService::STATUS_ACCEPT,OrderCustomerService::STATUS_DENY]),$validateException->updateMessage('只允许指定操作'));
        $customerServiceModel = parent::getModel($customerServiceId,true);
        BExceptionAssert::assertNotNull($customerServiceModel, $validateException->updateMessage('售后记录不存在'));
        BExceptionAssert::assertTrue($customerServiceModel['status']==OrderCustomerService::STATUS_UN_DEAL,$validateException->updateMessage('已不支持操作'));
        $orderModel = OrderService::requireOrder($customerServiceModel['order_no'],$company_id,false, $validateException->updateMessage('订单不存在'));
        BExceptionAssert::assertTrue(
            in_array($orderModel['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE])
            , $validateException->updateMessage('订单状态已不支持售后'));
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            if ($commander==OrderCustomerService::STATUS_ACCEPT){
                self::acceptAndLog($orderModel,$customerServiceModel,$operatorId,$operatorName,$auditRemark);
            }
            else if ($commander==OrderCustomerService::STATUS_DENY){
                if ($commander==OrderCustomerService::STATUS_DENY){
                    self::denyAndLog($customerServiceModel,$operatorId,$operatorName,$auditRemark);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            $validateException->updateMessage($e->getMessage());
            \Yii::error($e);
            BExceptionAssert::assertTrue(false,$validateException);
        }
    }

    /**
     * 审核通过
     * @param $orderModel
     * @param OrderCustomerService $customerServiceModel
     * @param $operatorId
     * @param $operatorName
     * @param $auditRemark
     */
    private static function acceptAndLog($orderModel,$customerServiceModel,$operatorId,$operatorName,$auditRemark){
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())->where(['customer_service_id'=>$customerServiceModel['id']])->all();
        BExceptionAssert::assertNotNull($customerServiceGoodsModels, BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录错误，未包含售后商品"));
        if ($customerServiceModel['type']==OrderCustomerService::TYPE_REFUND_CHANGE){
            foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
                OrderGoods::updateAll(['delivery_status'=>OrderCustomerService::TYPE_REFUND_CHANGE,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            }
        }else if ($customerServiceModel['type']==OrderCustomerService::TYPE_REFUND_MONEY_ONLY){
            foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
                OrderGoods::updateAll(['delivery_status'=>OrderCustomerService::TYPE_REFUND_MONEY_ONLY,'num_ac'=>0,'amount_ac'=>0,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            }
            OrderService::refreshOrderWeightAndAmount($orderModel);
        }
        else if ($customerServiceModel['type']==OrderCustomerService::TYPE_REFUND_MONEY_AND_GOODS){
            foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
                OrderGoods::updateAll(['delivery_status'=>OrderCustomerService::TYPE_REFUND_MONEY_AND_GOODS,'num_ac'=>0,'amount_ac'=>0,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            }
            OrderService::refreshOrderWeightAndAmount($orderModel);
        }
        else if ($customerServiceModel['type']==OrderCustomerService::TYPE_REFUND_CLAIM){
            foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
                $orderGoodsModel = OrderGoodsService::getModels($customerServiceGoodsModel['order_goods_id'],$orderModel['order_no'],$orderModel['company_id']);
                $amountAc = PriceUtils::accurateToTen(($customerServiceGoodsModel['order_goods_num']*$orderGoodsModel['amount'])/$orderGoodsModel['num']);
                OrderGoods::updateAll(['delivery_status'=>OrderGoods::DELIVERY_STATUS_CLAIM,'num_ac'=>$customerServiceGoodsModel['order_goods_num'],'amount_ac'=>$amountAc,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            }
            OrderService::refreshOrderWeightAndAmount($orderModel);
        }
        $customerServiceModel->status = OrderCustomerService::STATUS_ACCEPT;
        $customerServiceModel->audit_remark = $auditRemark;
        BExceptionAssert::assertTrue($customerServiceModel->save(), BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录更新失败"));
        $customerServiceLog = new OrderCustomerServiceLog();
        $customerServiceLog->customer_service_id = $customerServiceModel['id'];
        $customerServiceLog->operator_id = $operatorId;
        $customerServiceLog->operator_name = $operatorName;
        $customerServiceLog->action = OrderCustomerServiceLog::ACTION_ACCEPT_AGENT;
        BExceptionAssert::assertTrue($customerServiceLog->save(), BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后日志记录创建失败"));
    }

    /**
     * 审核拒绝
     * @param $customerServiceModel OrderCustomerService
     * @param $operatorId
     * @param $operatorName
     * @param $auditRemark
     */
    private static function denyAndLog($customerServiceModel,$operatorId,$operatorName,$auditRemark){
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())->where(['customer_service_id'=>$customerServiceModel['id']])->all();
        BExceptionAssert::assertNotNull($customerServiceGoodsModels, BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录错误，未包含售后商品"));
        $customerServiceModel->status = OrderCustomerService::STATUS_DENY;
        $customerServiceModel->audit_remark = $auditRemark;
        BExceptionAssert::assertTrue($customerServiceModel->save(), BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录更新失败"));
        $customerServiceLog = new OrderCustomerServiceLog();
        $customerServiceLog->customer_service_id = $customerServiceModel['id'];
        $customerServiceLog->operator_id = $operatorId;
        $customerServiceLog->operator_name = $operatorName;
        $customerServiceLog->action = OrderCustomerServiceLog::ACTION_DENY_AGENT;
        BExceptionAssert::assertTrue($customerServiceLog->save(), BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后日志记录创建失败"));

        foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
            $updateCount = OrderGoods::updateAll(['customer_service_status'=>CommonStatus::STATUS_DISABLED,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            BExceptionAssert::assertTrue($updateCount>0, BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后状态更新失败"));
        }
    }

    public static function generateVO($model){
        $order = key_exists('order',$model->relatedRecords)?$model['order']:[];
        $logs =  key_exists('logs',$model->relatedRecords)?$model['logs']:[];
        $orderGoods = [];
        if (!empty($model['customerServiceGoods'])){
            foreach ($model['customerServiceGoods'] as $customerServiceGood){
                if (!empty($customerServiceGood['orderGoods'])){
                    $customerServiceGood['orderGoods']['refund_amount'] = $customerServiceGood['order_goods_order_amount']-$customerServiceGood['order_goods_ac_amount'];
                    $orderGoods[] = $customerServiceGood['orderGoods'];
                }
            }
        }
        $customerServiceLogProvider = new ArrayDataProvider([
            'allModels' => $logs,
            'sort' => [
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $orderGoodsProvider = new ArrayDataProvider([
            'allModels' => $orderGoods,
            'sort' => [
            ],
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        $customerServiceVO = [];
        $customerServiceVO[] = ['title'=>'申请类型','text'=>ArrayUtils::getArrayValue($model['type'],OrderCustomerService::$typeArr)];
        $customerServiceVO[] = ['title'=>'审核状态','text'=>ArrayUtils::getArrayValue($model['status'],OrderCustomerService::$statusArr)];
        $customerServiceVO[] = ['title'=>'审核等级','text'=>ArrayUtils::getArrayValue($model['audit_level'],OrderCustomerService::$auditLevelArr)];

        $orderVO = [];
        $orderVO[] = ['title'=>'订单号','text'=>$order['order_no']];
        $orderVO[] = ['title'=>'下单时间','text'=>$order['created_at']];
        $orderVO[] = ['title'=>'订单状态','text'=>ArrayUtils::getArrayValue($order['order_status'],Order::$order_status_list)];
//        $orderVO[] = ['title'=>'收件人','text'=>$order['accept_name']];
//        $orderVO[] = ['title'=>'收件人手机号','text'=>$order['accept_mobile']];
//        $orderVO[] = ['title'=>'配送方式','text'=>Common::getArrayValue($order['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr)];

        $receiveVO = [];
        $receiveVO[] = ['title'=>'收件人','text'=>$order['accept_name']];
        $receiveVO[] = ['title'=>'收件人手机号','text'=>$order['accept_mobile']];
        $receiveVO[] = ['title'=>'配送方式','text'=>ArrayUtils::getArrayValue($order['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr)];

        $deliveryVO = [];
        $deliveryVO[] =  ['title'=>'配送点','text'=>$order['delivery_nickname']];
        $deliveryVO[] =  ['title'=>'配送联系人','text'=>$order['delivery_name']];
        $deliveryVO[] =  ['title'=>'配送手机号','text'=>$order['delivery_phone']];

        $displayVO = [
            'customerService'=>$customerServiceVO,
            'order'=>$orderVO,
            'delivery'=>$deliveryVO,
            'receive'=>$receiveVO,
        ];
        return [$model,$order,$displayVO,$orderGoodsProvider,$customerServiceLogProvider];
    }
}