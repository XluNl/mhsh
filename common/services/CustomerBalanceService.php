<?php


namespace common\services;


use common\models\CustomerBalance;
use common\models\CustomerBalanceItem;
use common\models\Order;
use yii\db\Query;

class CustomerBalanceService
{
    /**
     * 调整余额(订单)
     * @param $order Order
     * @param $amount
     * @param $remark
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    public static function adjustBalance($order, $amount, $remark, $operatorId, $operatorName){
        list($success,$msg,$balanceId) =  self::adjustBalanceCommon(CustomerBalanceItem::BIZ_TYPE_ORDER_COMPLETE,$order['order_no'],$order['customer_id'],$amount, $remark, $operatorId, $operatorName);
        return [$success,$msg];
    }

    /**
     * 调整余额
     * @param $bizType
     * @param $bizCode
     * @param $customerId
     * @param $amount
     * @param $remark
     * @param $operatorId
     * @param $operatorName
     * @return array
     */
    public static function adjustBalanceCommon($bizType,$bizCode,$customerId, $amount, $remark, $operatorId, $operatorName){
        if ($amount==0){
            return [true,'',null];
        }
        $balance = CustomerBalance::find()->where(['customer_id'=>$customerId])->one();
        if ($balance==null){
            $balance = new CustomerBalance();
            $balance->freeze_amount = 0;
            $balance->amount = $amount;
            $balance->version = 0;
            $balance->customer_id = $customerId;
            if (!$balance->save()){
                return [false,'余额保存失败',null];
            }
        }
        else{
            $balance->amount += $amount;
            $updateCount = CustomerBalance::updateAllCounters(
                [
                    'amount'=>$amount,
                    'version'=>1
                ],
                [
                    'id'=>$balance->id,
                    'version'=>$balance['version']
                ]);
            if ($updateCount<1){
                return [false,'余额更新失败',null];
            }
        }
        $balanceItem = new CustomerBalanceItem();
        $balanceItem->customer_id = $customerId;
        $balanceItem->operator_id = $operatorId;
        $balanceItem->operator_name = $operatorName;
        $balanceItem->status = CustomerBalanceItem::STATUS_ACTIVE;
        $balanceItem->action = CustomerBalanceItem::ACTION_ACCEPT;
        $balanceItem->in_out = $amount>0?CustomerBalanceItem::IN_OUT_IN:CustomerBalanceItem::IN_OUT_OUT;
        $balanceItem->biz_type = $bizType;
        $balanceItem->biz_code = $bizCode;
        $balanceItem->remark = $remark;
        $balanceItem->amount = $amount;
        $balanceItem->remain_amount = $balance->amount>0? $balance->amount:-$balance->amount;
        if (!$balanceItem->save()){
            return [false,'余额日志插入失败',null];
        }
        return [true,$balanceItem->id,$balance->id];
    }



    public static function getByCustomerId($customerId){
        $balance = (new Query())->from(CustomerBalance::tableName())->where([
            'customer_id'=>$customerId,
        ])->one();
        return $balance==false?null:$balance;
    }

}