<?php


namespace inner\services;

use common\models\Common;
use common\models\CustomerBalance;
use common\models\CustomerBalanceItem;
use common\models\StarExchangeLog;
use common\services\CustomerService;
use inner\utils\ExceptionAssert;
use inner\utils\exceptions\BusinessException;
use inner\utils\StatusCode;
use Yii;

class ExchangeService
{


    public static function exchangeBalance($tradeNo,$phone,$changeAmount,$exchangeTime,$bizType=StarExchangeLog::BIZ_TYPE_CUSTOMER_BALANCE){
        $changeAmount = Common::setAmount($changeAmount);
        $transaction = Yii::$app->db->beginTransaction();
        $oldStarExchangeLog = StarExchangeLogService::getModelByTradeNo($tradeNo);
        if (!empty($oldStarExchangeLog)){
            return;
        }
        try {
            $starExchangeLog = new StarExchangeLog();
            $starExchangeLog->trade_no = $tradeNo;
            $starExchangeLog->phone = $phone;
            $starExchangeLog->exchange_time = $exchangeTime;
            $starExchangeLog->amount = $changeAmount;
            $starExchangeLog->biz_type = $bizType;

            if ($bizType==StarExchangeLog::BIZ_TYPE_CUSTOMER_BALANCE){
                list($res,$error,$bizId,$balanceId,$balanceLogId) = self::exchangeCustomerBalance($tradeNo,$phone,$changeAmount);
                ExceptionAssert::assertTrue($res,StatusCode::createExpWithParams(StatusCode::STAR_EXCHANGE_BALANCE_ERROR,$error));
                $starExchangeLog->biz_id = $bizId;
                $starExchangeLog->balance_id = $balanceId;
                $starExchangeLog->balance_log_id = $balanceLogId;
            }
            else{
                ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::STAR_EXCHANGE_BALANCE_ERROR,'未知的兑换方式'));

            }
            ExceptionAssert::assertTrue($starExchangeLog->save(),StatusCode::createExpWithParams(StatusCode::STAR_EXCHANGE_BALANCE_ERROR,Common::getModelErrors($starExchangeLog)));

            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            throw StatusCode::createExpWithParams(StatusCode::STAR_EXCHANGE_BALANCE_ERROR,$e->getMessage());
        }
    }

    private static function exchangeCustomerBalance($tradeNo,$phone,$changeAmount){
        $customer = CustomerService::searchActiveCustomerByPhone($phone);
        if (empty($customer)){
            return [false,'不存在此用户',null,null,null];
        }
        $balance = CustomerBalance::findOne(['customer_id'=>$customer['id']]);
        if (empty($balance)){
            $balance = new CustomerBalance();
            $balance->amount = 0;
            $balance->freeze_amount = 0;
            $balance->customer_id = $customer['id'];
            $balance->version = 0;
            if (!$balance->save()){
                return [false,'余额账户创建失败:'.Common::getModelErrors($balance),null,null,null];
            }
        }
        $balanceItem = new CustomerBalanceItem();
        $balanceItem->amount = $changeAmount;
        $balanceItem->remain_amount = $changeAmount+$balance->amount;
        $balanceItem->customer_id = $customer['id'];
        $balanceItem->operator_id = $customer['id'];
        $balanceItem->operator_name = $customer['nickname'];
        $balanceItem->status = CustomerBalanceItem::STATUS_ACTIVE;
        $balanceItem->in_out = CustomerBalanceItem::IN_OUT_IN;
        $balanceItem->biz_type = CustomerBalanceItem::BIZ_TYPE_STAR_EXCHANGE;
        $balanceItem->action = CustomerBalanceItem::ACTION_ACCEPT;
        $balanceItem->biz_code = $tradeNo;
        $balanceItem->remark = '星球兑换余额';
        if (!$balanceItem->save()){
            return [false,'余额流水创建失败:'.Common::getModelErrors($balanceItem),null,null,null];
        }
        $updateCount = CustomerBalance::updateAll(['amount'=>$balance->amount+$changeAmount,'version'=>$balance->version+1],['id'=>$balance->id,'version'=>$balance->version]);
        if ($updateCount<1){
            return [false,'余额更新失败，请重试',null,null,null];
        }
        return [true,'',$customer['id'],$balance->id,$balanceItem->id];
    }

}