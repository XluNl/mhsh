<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:21
 */

namespace frontend\services;



use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\CommonStatus;
use common\models\CustomerBalance;
use common\models\CustomerBalanceItem;
use common\services\CustomerBalanceVOService;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use frontend\utils\AssertDefaultUtil;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;

class CustomerBalanceService extends \common\services\CustomerBalanceService
{

    /**
     * 获取最大可用余额
     * @param $company_id
     * @param $customer_id
     * @param $amount
     * @return int
     * @throws \Exception
     */
    public static function getAvailableMaxBalance($company_id,$customer_id,$amount){
        $balance = CustomerBalance::findOne(['company_id'=>$company_id,'customer_id'=>$customer_id]);
        if (empty($balance)){
            return 0;
        }
        if ($balance->amount<=0){
            return 0;
        }

        if ($balance->amount<$amount){
            return $balance->amount;
        }
        else {
            return $amount;
        }
    }


    /**
     * 获取最大可用余额并核销
     * @param $customer_id
     * @param $amount
     * @param $orderNo
     * @param $operationId
     * @param $operationName
     * @return int
     * @throws \Exception
     */
    public static function getAvailableMaxBalanceAndVerify($customer_id,$amount,$orderNo,$operationId,$operationName){
        $balance = CustomerBalance::findOne(['customer_id'=>$customer_id]);
        if (empty($balance)){
            return 0;
        }
        $balanceItem = new CustomerBalanceItem();
        $balanceItem->customer_id = $customer_id;
        $balanceItem->operator_id = $operationId;
        $balanceItem->operator_name = $operationName;
        $balanceItem->status = CustomerBalanceItem::STATUS_ACTIVE;
        $balanceItem->in_out = CustomerBalanceItem::IN_OUT_OUT;
        $balanceItem->biz_type = CustomerBalanceItem::BIZ_TYPE_ORDER_PAY;
        $balanceItem->action = CustomerBalanceItem::ACTION_ACCEPT;
        $balanceItem->biz_code = $orderNo;
        if ($balance->amount<=0){
            $balanceItem->remark = '订单补足';
            $balanceItem->in_out = CustomerBalanceItem::IN_OUT_IN;
            $balanceItem->amount = - $balance->amount;
            $balance->amount = 0;
            $balanceItem->remain_amount = 0;
        }
        else if ($balance->amount<$amount){
            $balanceItem->remark = '订单支付';
            $balanceItem->amount = $balance->amount;
            $balance->amount = 0;
            $balanceItem->remain_amount = 0;
        }
        else {
            $balanceItem->remark = '订单支付';
            $balance->amount = $balance->amount - $amount;
            $balanceItem->amount = $amount;
            $balanceItem->remain_amount = $balance->amount;
        }
        ExceptionAssert::assertTrue($balanceItem->save(),StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'余额明细保存失败'));
        $updateCount = CustomerBalance::updateAll(['amount'=>$balance->amount,'version'=>$balance->version+1],['id'=>$balance->id,'version'=>$balance->version]);
        ExceptionAssert::assertTrue($updateCount>0,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'余额保存失败'));
        return $balanceItem->amount;
    }

    /**
     * 取消订单恢复余额
     * @param $order
     * @param $amount
     * @param string $remark
     * @param null $operatorId
     * @param null $operatorName
     */
    public static function refundBalance($order, $amount, $remark, $operatorId=null, $operatorName=null){
        AssertDefaultUtil::setNotNull($operatorId,$order['customer_id']);
        AssertDefaultUtil::setNotNull($operatorName,$order['accept_nickname']);
        list($success,$errorMsg) = parent::adjustBalance($order, $amount, $remark, $operatorId, $operatorName);
        ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$errorMsg));
    }

    public static function getAmount($customerId){
        $model = (new Query())->from(CustomerBalance::tableName())
            ->where(['customer_id'=>$customerId])
            ->one();
        $amountResult = [
            'amount'=>0,
            'withdraw_amount'=>0,
            'freeze_amount'=>0,
        ];
        if (!empty($model)){
            $amountResult = [
                'amount'=>$model['amount'],
                'withdraw_amount'=>$model['amount']-$model['freeze_amount'],
                'freeze_amount'=>$model['freeze_amount'],
            ];
        }
        return $amountResult;
    }


    public static function getBalanceDetail($customerId,$date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $list = self::getListByDate($customerId,$startTime,$endTime);
        $inAmount = 0;
        $outAmount = 0;
        if (!empty($list)){
            foreach ($list as $k=>$v){
                if ($v['action']!=CustomerBalanceItem::ACTION_DENY){
                    if ($v['in_out']==CustomerBalanceItem::IN_OUT_IN){
                        $inAmount += $v['amount'];
                    }
                    else if ($v['in_out']==CustomerBalanceItem::IN_OUT_OUT){
                        $outAmount += $v['amount'];
                    }
                }
            }
        }
        $res = [
            'date_text'=>DateTimeUtils::formatYearAndMonthChinese($date),
            'in_amount'=>$inAmount,
            'out_amount'=>$outAmount,
            'item'=>CustomerBalanceVOService::batchSetVO($list),
        ];
        return $res;
    }

    /**
     * 明细查询
     * @param $customerId
     * @param $startTime
     * @param $endTime
     * @param null $bizType
     * @return array
     */
    public static function getListByDate($customerId,$startTime,$endTime, $bizType=null ){
        $conditions = [
            'and',
            ['customer_id'=>$customerId,'status'=>CommonStatus::STATUS_ACTIVE],
            ['>=','created_at',$startTime],
            ['<=','created_at',$endTime],
        ];
        if (!StringUtils::isBlank($bizType)){
            $conditions[] = ['biz_type'=>$bizType];
        }
        $result = (new Query())->from(CustomerBalanceItem::tableName())
            ->where($conditions)
            ->orderBy('created_at desc')->all();
        return $result;
    }


    /**
     * 明细查询
     * @param $customerId
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getItemList($customerId,$pageNo=1,$pageSize=20){
        $items = (new Query())->from(CustomerBalanceItem::tableName())
            ->where(['customer_id'=>$customerId])
            ->offset(($pageNo-1)*$pageSize)->limit($pageSize)
            ->orderBy('id desc')->all();
        return $items;
    }


    public static function completeBalance($order, $amount, $remark, $operatorId, $operatorName){
        list($success,$errorMsg) = parent::adjustBalance($order, $amount, $remark, $operatorId, $operatorName);
        BExceptionAssert::assertTrue($success,BStatusCode::createExpWithParams(BStatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
    }
}