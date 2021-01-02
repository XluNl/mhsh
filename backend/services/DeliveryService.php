<?php

namespace backend\services;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\Common;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use yii\db\Query;

class DeliveryService extends \common\services\DeliveryService
{
    /**
     * @param $company_id
     * @return array
     */
    public static function getAllDelivery($company_id){
        $deliveryArray = (new Query())->from(Delivery::tableName())->where(['company_id'=>$company_id])->all();
        return $deliveryArray;
    }

    public static function generateOptions($models){
        if (empty($models)){
            return [];
        }
        $options = [];
        foreach ($models as $model){
            $options[$model['id']] = "{$model['nickname']}({$model['realname']}-{$model['phone']})";
        }
        return $options;
    }

    public static function generateAllDeliveryOptions($companyId){
        $deliveryModels = self::getAllDelivery($companyId);
        return self::generateOptions($deliveryModels);
    }



    /**
     * 是否下单操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operateAllowOrder($id, $commander, $company_id, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[Delivery::ALLOW_ORDER_TRUE,Delivery::ALLOW_ORDER_FALSE]),$validateException);
        $count = Delivery::updateAll(['allow_order'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

    /**
     * 删除操作
     * @param $id
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operateStatus($id, $commander, $company_id, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CommonStatus::STATUS_DISABLED]),$validateException);
        $count = Delivery::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }


    /**
     * 获取并校验
     * @param $id
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$company_id,$validateException,$model = false){
        $model = self::getActiveModel($id,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 统计团长地图团长的订单信息
     * @param $companyId
     * @param $date
     * @return array
     */
    public static function getDeliveryMapList($companyId,$date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime =DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));;
        $orderTable = Order::tableName();
        $deliveryOrderList = (new Query())->from($orderTable)->select([
            "SUM({$orderTable}.real_amount) as real_amount",
            "COUNT({$orderTable}.order_no) as order_count",
            "COUNT(DISTINCT({$orderTable}.order_no)) as customer_count",
            "{$orderTable}.delivery_id",
            "{$orderTable}.delivery_nickname",
        ])->where([
            'and',
            [
                "{$orderTable}.company_id"=>$companyId,
                "{$orderTable}.order_status"=>Order::$downloadStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF
            ],
            ['between',"{$orderTable}.created_at",$startTime,$endTime]
        ])->groupBy(['delivery_id']
        )->all();

        $customerServiceList = (new Query())->from($orderTable)->select([
            "COUNT({$orderTable}.order_no) as order_count",
            "{$orderTable}.delivery_id",
        ])->where([
            'and',
            [
                "{$orderTable}.company_id"=>$companyId,
                "{$orderTable}.order_status"=>Order::$downloadStatusArr,
                "{$orderTable}.order_owner"=>GoodsConstantEnum::OWNER_SELF,
                "{$orderTable}.customer_service_status"=>Order::CUSTOMER_SERVICE_STATUS_TRUE,
            ],
            ['between',"{$orderTable}.created_at",$startTime,$endTime]
        ])->groupBy(['delivery_id']
        )->all();


        if (!empty($deliveryOrderList)){
            $deliveryIds = ArrayUtils::getColumnWithoutNull('delivery_id',$deliveryOrderList);
            $deliveryModels = DeliveryService::getActiveModels($deliveryIds,$companyId);
            $deliveryModels = ArrayUtils::index($deliveryModels,'id');

            $customerServiceList = ArrayUtils::index($customerServiceList,'delivery_id');
            foreach ($deliveryOrderList as $k=>$v){
                $v['real_amount'] = Common::showAmount($v['real_amount']);
                if (key_exists($v['delivery_id'],$deliveryModels)){
                    $v['lat'] = $deliveryModels[$v['delivery_id']]['lat'];
                    $v['lng'] = $deliveryModels[$v['delivery_id']]['lng'];
                }
                else{
                    $v['lat'] = '30.2741500000';
                    $v['lng'] = '120.1551500000';
                }
                if (key_exists($v['delivery_id'],$customerServiceList)){
                    $v['customer_service_count'] =  $customerServiceList[$v['delivery_id']]['order_count'];
                }
                else{
                    $v['customer_service_count'] = 0;
                }
                $deliveryOrderList[$k] = $v;
            }

        }

        return $deliveryOrderList;

    }


    /**
     * 给团点批量投放商品
     * @param $companyId
     * @param $deliveryId
     * @param $goodsIds
     */
    public static function goodsDeliveryChannel($companyId,$deliveryId,$goodsIds){
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            list($result,$error) = parent::goodsDeliveryChannelAdd($companyId,$deliveryId,$goodsIds);
            BExceptionAssert::assertTrue($result,BStatusCode::createExpWithParams(BStatusCode::DRAW_BONUS_ERROR,$error));
            $transaction->commit();
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,BStatusCode::createExpWithParams(BStatusCode::DRAW_BONUS_ERROR,$e->getMessage()));
        }
    }

}