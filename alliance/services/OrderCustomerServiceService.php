<?php

namespace alliance\services;

use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\CommonStatus;
use common\models\Order;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceGoods;
use common\models\OrderCustomerServiceLog;
use common\models\OrderGoods;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class OrderCustomerServiceService extends \common\services\OrderCustomerServiceService
{

    public static function getListPageFilter($ownerType, $allianceId, $status, $keyword, $pageNo=1, $pageSize=20)
    {
        $orderTable = Order::tableName();
        $orderCustomerServiceTable = OrderCustomerService::tableName();
        $condition = ['and',["{$orderTable}.order_owner_id" => $allianceId,"{$orderTable}.order_owner"=>$ownerType]];
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
     * 团长售后列表
     * @param $orderNo
     * @param $userId
     * @return array
     */
    public static function getListByOrder($orderNo,$userId)
    {
        $orderModel = OrderService::requiredOrderModel($orderNo);
        self::checkPermission($orderModel,$userId);
        $customerServiceModels = (new Query())->from(OrderCustomerService::tableName())->where(['order_no' => $orderNo])->orderBy('id desc')
          ->all();
        if (empty($customerServiceModels)) {
            return [];
        }
        $customerServiceIds = ArrayHelper::getColumn($customerServiceModels, "id");
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())
            ->leftJoin(OrderGoods::tableName(),  OrderCustomerServiceGoods::tableName() . '.order_goods_id=' . OrderGoods::tableName() . '.id')
            ->where(['customer_service_id' => $customerServiceIds])->all();
        foreach ($customerServiceModels as $k1 => $v1) {
            foreach ($customerServiceGoodsModels as $v2) {
                if ($v1['id'] == $v2['customer_service_id']) {
                    if (!key_exists('goods', $v1)) {
                        $v1['goods'] = [];
                    }
                    $customerServiceModels[$k1]['goods'][] = $v2;
                }
            }
        }
        $customerServiceModels = OrderCustomerServiceDisplayVOService::batchSetDisplayVO($customerServiceModels);
        $orderModel['customer_service'] = $customerServiceModels;
        return $orderModel;
    }

    /**
     * 售后处理
     * @param $customerServiceId
     * @param $commander
     * @param $operatorId
     * @param $operatorName
     * @throws BusinessException
     * @throws \yii\db\Exception
     */
    public static function operate($customerServiceId,$commander,$operatorId,$operatorName)
    {
        ExceptionAssert::assertTrue(in_array($commander,[OrderCustomerService::STATUS_ACCEPT,OrderCustomerService::STATUS_DENY]), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "只允许指定操作"));
        $customerServiceModel = parent::getModel($customerServiceId,true);
        ExceptionAssert::assertNotNull($customerServiceModel, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录不存在"));
        ExceptionAssert::assertTrue($customerServiceModel['status']==OrderCustomerService::STATUS_UN_DEAL, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "已不支持操作"));
        ExceptionAssert::assertTrue($customerServiceModel['audit_level']!=OrderCustomerService::AUDIT_LEVEL_AGENT, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "团长已审核，请勿重复操作"));
        ExceptionAssert::assertTrue($customerServiceModel['audit_level']==OrderCustomerService::AUDIT_LEVEL_DELIVERY_OR_ALLIANCE, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "只有团长审核级别才能审核"));
        $orderModel = OrderService::requiredOrderModel($customerServiceModel['order_no']);
        ExceptionAssert::assertTrue(
            in_array($orderModel['order_status'],[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY,Order::ORDER_STATUS_RECEIVE])
            , StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "订单状态已不支持售后"));
        self::checkPermission($orderModel,$operatorId);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($commander==OrderCustomerService::STATUS_ACCEPT){
                self::acceptAndLog($customerServiceModel,$operatorId,$operatorName);
            }
            else if ($commander==OrderCustomerService::STATUS_DENY){
                if ($commander==OrderCustomerService::STATUS_DENY){
                    self::denyAndLog($customerServiceModel,$operatorId,$operatorName);
                }
            }
            $transaction->commit();
        } catch (BusinessException $e) {
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, $e->getMessage()));
        }
    }

    /**
     * 通过审核
     * @param $customerServiceModel OrderCustomerService|array
     * @param $operatorId
     * @param $operatorName
     */
    private static function acceptAndLog($customerServiceModel,$operatorId,$operatorName){
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())->where(['customer_service_id'=>$customerServiceModel['id']])->all();
        ExceptionAssert::assertNotNull($customerServiceGoodsModels, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录错误，未包含售后商品"));
        $customerServiceModel->audit_level = OrderCustomerService::AUDIT_LEVEL_AGENT;
        ExceptionAssert::assertTrue($customerServiceModel->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录更新失败"));
        $customerServiceLog = new OrderCustomerServiceLog();
        $customerServiceLog->customer_service_id = $customerServiceModel['id'];
        $customerServiceLog->operator_id = $operatorId;
        $customerServiceLog->operator_name = $operatorName;
        $customerServiceLog->action = OrderCustomerServiceLog::ACTION_ACCEPT_DELIVERY;
        ExceptionAssert::assertTrue($customerServiceLog->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后日志记录创建失败"));
    }

    /**
     * 审核拒绝
     * @param $customerServiceModel
     * @param $operatorId
     * @param $operatorName
     */
    private static function denyAndLog($customerServiceModel,$operatorId,$operatorName){
        $customerServiceGoodsModels = (new Query())->from(OrderCustomerServiceGoods::tableName())->where(['customer_service_id'=>$customerServiceModel['id']])->all();
        ExceptionAssert::assertNotNull($customerServiceGoodsModels, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录错误，未包含售后商品"));
        $customerServiceModel->status = OrderCustomerService::STATUS_DENY;
        ExceptionAssert::assertTrue($customerServiceModel->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后记录更新失败"));
        $customerServiceLog = new OrderCustomerServiceLog();
        $customerServiceLog->customer_service_id = $customerServiceModel['id'];
        $customerServiceLog->operator_id = $operatorId;
        $customerServiceLog->operator_name = $operatorName;
        $customerServiceLog->action = OrderCustomerServiceLog::ACTION_DENY_DELIVERY;
        ExceptionAssert::assertTrue($customerServiceLog->save(), StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后日志记录创建失败"));

        foreach ($customerServiceGoodsModels as $customerServiceGoodsModel){
            $updateCount = OrderGoods::updateAll(['customer_service_status'=>CommonStatus::STATUS_DISABLED,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$customerServiceGoodsModel['order_goods_id']]);
            ExceptionAssert::assertTrue($updateCount>0, StatusCode::createExpWithParams(StatusCode::CUSTOMER_SERVICE_OPERATION_ERROR, "售后状态更新失败"));
        }
    }


    /**
     * 校验订单权限
     * @param $orderModel
     * @param $userId
     */
    private static function checkPermission($orderModel, $userId)
    {
        $allianceModel = AllianceService::requiredModel($orderModel['order_owner_id']);
        ExceptionAssert::assertTrue($allianceModel['user_id']==$userId, StatusCode::createExp(StatusCode::ALLIANCE_BELONG_NOT_ALLOW));
    }

}