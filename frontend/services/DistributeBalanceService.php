<?php


namespace frontend\services;


use common\models\BizTypeEnum;
use common\models\CustomerBalance;
use common\models\DistributeBalance;
use common\models\Order;
use common\models\OrderPreDistribute;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DistributeBalanceService extends \common\services\DistributeBalanceService
{
    /**
     * 预分润汇总 所有
     * @param $customerId
     * @return array|bool
     */
    public static function preDistributeAllSumF($customerId){
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_CUSTOMER,$customerId, null, null);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(日)
     * @param $customerId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeDaySumF($customerId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_CUSTOMER,$customerId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(月)
     * @param $customerId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeMonthSumF($customerId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum(OrderPreDistribute::BIZ_TYPE_CUSTOMER,$customerId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }


    /**
     * 预分润详情(日)
     * @param $customerId
     * @param $date
     * @param null $targetCustomerId
     * @return array
     */
    public static function preDistributeDayF($customerId, $date,$targetCustomerId=null){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $level = CustomerInvitationService::getInvitationLevel($customerId,$targetCustomerId);
        if (!StringUtils::isBlank($targetCustomerId)){
            ExceptionAssert::assertNotNull($level,StatusCode::createExp(StatusCode::INVITATION_NOT_EXIST));
        }
        $preDistributeOrders = self::getPreDistributeOrderF($customerId, $startTime, $endTime,$targetCustomerId,$level);
        $orderCount = count($preDistributeOrders);
        $preDistributeAmount = 0;
        $preDistributeArr= [];
        self::assembleDetail($preDistributeOrders, $preDistributeArr, $preDistributeAmount);
        $res = [
            'date'=>DateTimeUtils::formatYearAndMonthAndDayChinese($date),
            'order_count' =>$orderCount,
            'pre_distribute_amount'=>$preDistributeAmount,
            'preDistributeOrders'=>$preDistributeArr
        ];
        return $res;
    }


    /**
     * 预分润详情(月)
     * @param $customerId
     * @param $date
     * @param null $targetCustomerId
     * @return array
     */
    public static function preDistributeMonthF($customerId, $date,$targetCustomerId=null){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $level = CustomerInvitationService::getInvitationLevel($customerId,$targetCustomerId);
        if (!StringUtils::isBlank($targetCustomerId)){
            ExceptionAssert::assertNotNull($level,StatusCode::createExp(StatusCode::INVITATION_NOT_EXIST));
        }
        $preDistributeOrders = self::getPreDistributeOrderF($customerId, $startTime, $endTime,$targetCustomerId,$level);
        $orderCount = count($preDistributeOrders);
        $preDistributeAmount = 0;
        $preDistributeArr= [];
        self::assembleDetail($preDistributeOrders, $preDistributeArr, $preDistributeAmount);
        $res = [
            'date'=>DateTimeUtils::formatYearAndMonthChinese($date),
            'order_count' =>$orderCount,
            'pre_distribute_amount'=>$preDistributeAmount,
            'orders'=>$preDistributeArr
        ];
        return $res;
    }

    /**
     * @param $customerId
     * @param $startTime
     * @param $endTime
     * @param null $targetCustomerId
     * @param null $level
     * @return array
     */
    public static function getPreDistributeOrderF($customerId, $startTime, $endTime,$targetCustomerId=null,$level=null)
    {
        $orderTable = Order::tableName();
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $conditions = [
            "and",
            [
                "{$orderPreDistributeTable}.biz_id"=>$customerId,
                "{$orderPreDistributeTable}.biz_type"=>OrderPreDistribute::BIZ_TYPE_CUSTOMER,
            ],
            ["{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE, Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY, Order::ORDER_STATUS_RECEIVE, Order::ORDER_STATUS_COMPLETE]],
        ];
        if (!StringUtils::isBlank($targetCustomerId)&&!StringUtils::isBlank($level)){
            if ($level==OrderPreDistribute::LEVEL_ONE){
                $conditions[] = ["{$orderTable}.customer_id"=>$targetCustomerId];
            }
            else if ($level==OrderPreDistribute::LEVEL_TWO){
                $conditions[] = ["{$orderTable}.one_level_rate_id"=>$targetCustomerId];
            }
        }
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        $preDistributeOrders = (new Query())->from($orderPreDistributeTable)
            ->leftJoin($orderTable,"{$orderTable}.order_no={$orderPreDistributeTable}.order_no")
            ->where($conditions)->orderBy("{$orderPreDistributeTable}.order_time desc")->all();
        return $preDistributeOrders;
    }



    /**
     * 统计一级分销和二级分销数量
     * @param $id
     * @return array
     */
    public static function getInvitationPeopleCount($id){
        $oneLevel = CustomerInvitationService::getInvitationByParentId($id);
        $twoLevel = [];
        if (!empty($oneLevel)){
            $oneLevelIds = ArrayHelper::getColumn($oneLevel,'customer_id');
            $twoLevel =  CustomerInvitationService::getInvitationByParentId($oneLevelIds);
        }
        $res = [
            'one_level_count'=>count($oneLevel),
            'two_level_count'=>count($twoLevel),
        ];
        return $res;
    }



    /**
     *
     * @param $customerId
     * @param $uid
     * @return array|bool|CustomerBalance|\common\models\DistributeBalance|\yii\db\ActiveRecord|null
     */
    public static function getCustomerDistributeBalance($customerId,$uid){
        $model = DistributeBalanceService::getModelByBiz($customerId,BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,$uid);
        if ($model==null){
            $model = new DistributeBalance();
            $model->loadDefaultValues();
            $model->biz_type = BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE;
            $model->biz_id = $customerId;
        }
        return $model;
    }




}