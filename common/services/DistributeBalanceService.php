<?php


namespace common\services;


use common\models\BizTypeEnum;
use common\models\Common;
use common\models\CommonStatus;
use common\models\DistributeBalance;
use common\models\DistributeBalanceItem;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderPreDistribute;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;
use yii\helpers\Json;

class DistributeBalanceService
{
    /**
     * 根据id查询
     * @param $id
     * @param bool $model
     * @return array|bool|DistributeBalance|\yii\db\ActiveRecord|null
     */
    public static function getModel($id,$model = false){
        $conditions = ['id' => $id];
        if ($model){
            return DistributeBalance::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(DistributeBalance::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 根据biz查询
     * @param $bizId
     * @param $bizType
     * @param null $userId
     * @param bool $model
     * @return array|bool|DistributeBalance|\yii\db\ActiveRecord|null
     */
    public static function getModelByBiz($bizId,$bizType,$userId=null,$model = false){
        $conditions = ['biz_id'=>$bizId,'biz_type' => $bizType];
        if (!StringUtils::isBlank($userId)){
            $conditions['user_id'] = $userId;
        }
        if ($model){
            return DistributeBalance::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(DistributeBalance::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }
    /**
     * 分润
     * @param $order Order
     * @param $isAllSuccessDelivery
     * @param $operationId
     * @param $operationName
     * @return array
     */
    public static function distributeBalance($order,&$isAllSuccessDelivery,$operationId,$operationName){
        if (!key_exists($order['order_owner'],GoodsConstantEnum::$ownerArr)){
            return [false,'不支持的订单类型，无法结算'];
        }
        if ($order['order_status']!=Order::ORDER_STATUS_RECEIVE){
            return [false,'只允许已送达的订单进行结算'];
        }
        if ($order['real_amount_ac']<=0){
            return [true,'此订单无实际金额，无法结算，直接退款'];
        }
        $orderCustomerServiceModels = OrderCustomerServiceService::getDealing($order['order_no']);
        if (!empty($orderCustomerServiceModels)){
            return [false,'此订单有未处理的售后单，请先联系团长或代理商处理'];
        }
        $orderGoodsModels = OrderService::getOrderGoodsModel($order['order_no']);
        if (empty($orderGoodsModels)){
            return [false,'订单不包含任何商品'];
        }
        $isAllSuccessDelivery = self::isAllSuccessDelivery($orderGoodsModels);

        if ($order['order_owner']==GoodsConstantEnum::OWNER_SELF){
           list($error,$errorMsg) = self::distributeSelfOrder($order,$orderGoodsModels,$operationId,$operationName);
           return [$error,$errorMsg];
        }
        else if ($order['order_owner']==GoodsConstantEnum::OWNER_HA){
            list($error,$errorMsg) = self::distributeHAOrder($order,$orderGoodsModels,$operationId,$operationName);
            return [$error,$errorMsg];
        }
        else if ($order['order_owner']==GoodsConstantEnum::OWNER_DELIVERY){
            list($error,$errorMsg) = self::distributeDeliveryOrder($order,$orderGoodsModels,$operationId,$operationName);
            return [$error,$errorMsg];
        }
        return [true,''];
    }


    public static function isAllSuccessDelivery($orderGoodsModels){
        if (empty($orderGoodsModels)){
            return true;
        }
        foreach ($orderGoodsModels as $orderGoodsModel){
            if (in_array($orderGoodsModel['delivery_status'],[OrderGoods::DELIVERY_STATUS_PREPARE,OrderGoods::DELIVERY_STATUS_DELIVERY,OrderGoods::DELIVERY_STATUS_SELF_DELIVERY,OrderGoods::DELIVERY_STATUS_REFUND_MONEY_AND_GOODS,OrderGoods::DELIVERY_STATUS_REFUND_MONEY_ONLY,OrderGoods::DELIVERY_STATUS_CLAIM])){
                return false;
            }
        }
        return true;
    }


    public static function distributeSelfOrder($order,$orderGoodsModels,$operationId,$operationName){
        $distributeAmount =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount'=>0,
            'companyAmount'=>0,
            'paymentHandlingFee'=>0,
        ];
        $distributeAmountItems =[
            'oneLevelItems' => [],
            'twoLevelItems' =>[],
            'share1Item' => [],
            'share2Items' => [],
            'deliveryItems' => [],
            'agentItems' =>[],
            'companyItems'=>[],
            'paymentHandlingFeeItem'=>[],
        ];
        $initCompanyId= Yii::$app->params['option.init.companyId'];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        foreach ($orderGoodsModels as $orderGoodsModel){
            $orderGoodsAmount = $orderGoodsModel['amount_ac'];
            $usedAmount = 0;
            if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['one_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['oneLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"oneLevelItems","one_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['two_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['twoLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"twoLevelItems","two_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_1'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share1Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share1Items","share_rate_1", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_2'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share2Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share2Items","share_rate_2", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['delivery_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['delivery_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['deliveryAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"deliveryItems","delivery_rate", $dAmount);
            }

            //平台分润
            if ($orderGoodsModel['company_rate']>0){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['company_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['companyAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"companyItems","company_rate", $dAmount);
            }

            //代理商分润
            $agentAmount = $orderGoodsAmount - $usedAmount;
            if ($agentAmount<0){
                return [false,"{$orderGoodsModel['goods_name']}分润设置错误".
                    ',用户一级分润'.Common::showPercentWithUnit($orderGoodsModel['one_level_rate']).
                    ',用户二级分润'.Common::showPercentWithUnit($orderGoodsModel['two_level_rate']).
                    ',分享团长一级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_1']).
                    ',分享团长二级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_2']).
                    ',配送团长分润'.Common::showPercentWithUnit($orderGoodsModel['delivery_rate']).
                    ',支付渠道费用比例'.Common::showPercentWithUnit($paymentHandlingFeeRate).
                    ',平台分润'.Common::showPercentWithUnit($orderGoodsModel['company_rate'])
                ];
            }
            $distributeAmountItems['agentItems'][] = [
                'name'=>"{$orderGoodsModel['id']}-{$orderGoodsModel['schedule_name']}({$orderGoodsModel['goods_name']}{$orderGoodsModel['sku_name']})",
                'distribute_rate'=>10000
                    -$orderGoodsModel['one_level_rate']
                    -$orderGoodsModel['two_level_rate']
                    -$orderGoodsModel['share_rate_1']
                    -$orderGoodsModel['share_rate_2']
                    -$orderGoodsModel['delivery_rate']
                    -$orderGoodsModel['company_rate'],
                'orderGoodsAmount'=>$agentAmount,
            ];
        }
        //配送费结算给配送团长
        if ($order['freight_amount']>0){
            $distributeAmount['deliveryAmount'] += $order['freight_amount'];
            $distributeAmountItems['deliveryItems'][] = [
                'name'=>"配送费",
                'distribute_rate'=>10000,
                'orderGoodsAmount'=> $order['freight_amount'],
            ];
        }
        //支付手续费
        $distributeAmount['paymentHandlingFee'] = ceil($order['real_amount_ac']*$paymentHandlingFeeRate/10000);

        if ($distributeAmount['paymentHandlingFee']>0){
            $distributeAmountItems['paymentHandlingFeeItem'][] = [
                'name'=>"支付手续费",
                'distribute_rate'=>-$paymentHandlingFeeRate,
                'orderGoodsAmount'=>-$distributeAmount['paymentHandlingFee'],
            ];
            $distributeAmountItems['agentItems'][] = $distributeAmountItems['paymentHandlingFeeItem'][0];
        }

        //代理商分润
        $distributeAmount['agentAmount'] =
            $order['real_amount_ac']
            -$distributeAmount['paymentHandlingFee']
            -$distributeAmount['oneLevelAmount']
            -$distributeAmount['twoLevelAmount']
            -$distributeAmount['share1Amount']
            -$distributeAmount['share2Amount']
            -$distributeAmount['deliveryAmount']
            -$distributeAmount['companyAmount'];
        if ($distributeAmount['agentAmount']<0){
            return [false,"代理商分润金额（{$distributeAmount['agentAmount']}）不能为负"];
        }
        //保存分润详情
        if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['one_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['oneLevelAmount'],
                $distributeAmountItems['oneLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['two_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['twoLevelAmount'],
                $distributeAmountItems['twoLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])&&$distributeAmount['share1Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_1'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share1Amount'],
                $distributeAmountItems['share1Item'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$distributeAmount['share2Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_2'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share2Amount'],
                $distributeAmountItems['share2Items'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_DELIVERY,
            $order['delivery_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['deliveryAmount'],
            $distributeAmountItems['deliveryItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }


        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_PAYMENT_HANDLING_FEE,
            $order['company_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['paymentHandlingFee'],
            $distributeAmountItems['paymentHandlingFeeItem'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_AGENT,
            $order['company_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['agentAmount'],
            $distributeAmountItems['agentItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        if ($distributeAmount['companyAmount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_COMPANY,
                $initCompanyId,
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['companyAmount'],
                $distributeAmountItems['companyItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        return [true,""];
    }


    public static function distributeHAOrder($order,$orderGoodsModels,$operationId,$operationName){
        $distributeAmount =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount'=>0,
            'companyAmount'=>0,
            'allianceAmount'=>0,
            'paymentHandlingFee'=>0,
        ];
        $distributeAmountItems =[
            'oneLevelItems' => [],
            'twoLevelItems' =>[],
            'share1Item' => [],
            'share2Items' => [],
            'deliveryItems' => [],
            'agentItems' =>[],
            'companyItems' =>[],
            'allianceItems' =>[],
            'paymentHandlingFeeItem'=>[],
        ];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $initCompanyId= Yii::$app->params['option.init.companyId'];
        foreach ($orderGoodsModels as $orderGoodsModel){
            $orderGoodsAmount = $orderGoodsModel['amount_ac'];
            $usedAmount = 0;
            if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['one_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['oneLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"oneLevelItems","one_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['two_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['twoLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"twoLevelItems","two_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_1'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share1Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share1Items","share_rate_1", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_2'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share2Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share2Items","share_rate_2", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['delivery_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['delivery_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['deliveryAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"deliveryItems","delivery_rate", $dAmount);
            }

            //代理点分润
            if ($orderGoodsModel['agent_rate']>0){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['agent_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['agentAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"agentItems","agent_rate", $dAmount);
            }

            //平台分润
            if ($orderGoodsModel['company_rate']>0){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['company_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['companyAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"companyItems","company_rate", $dAmount);
            }

            //联盟点分润
            $allianceAmount = $orderGoodsAmount - $usedAmount;
            if ($allianceAmount<0){
                return [false,"{$orderGoodsModel['goods_name']}分润设置错误".
                    ',用户一级分润'.Common::showPercentWithUnit($orderGoodsModel['one_level_rate']).
                    ',用户二级分润'.Common::showPercentWithUnit($orderGoodsModel['two_level_rate']).
                    ',分享团长一级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_1']).
                    ',分享团长二级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_2']).
                    ',配送团长分润'.Common::showPercentWithUnit($orderGoodsModel['delivery_rate']).
                    ',支付渠道费用分润'.Common::showPercentWithUnit($paymentHandlingFeeRate).
                    ',代理商分润'.Common::showPercentWithUnit($orderGoodsModel['agent_rate']).
                    ',平台分润'.Common::showPercentWithUnit($orderGoodsModel['company_rate'])
                ];
            }
            $distributeAmountItems['allianceItems'][] = [
                'name'=>"{$orderGoodsModel['id']}-{$orderGoodsModel['schedule_name']}({$orderGoodsModel['goods_name']}{$orderGoodsModel['sku_name']})",
                'distribute_rate'=>10000
                    -$orderGoodsModel['one_level_rate']
                    -$orderGoodsModel['two_level_rate']
                    -$orderGoodsModel['share_rate_1']
                    -$orderGoodsModel['share_rate_2']
                    -$orderGoodsModel['delivery_rate']
                    -$orderGoodsModel['agent_rate']
                    -$orderGoodsModel['company_rate'],
                'orderGoodsAmount'=>$allianceAmount,
            ];
        }


        //配送费结算给联盟点（因此不需要特殊计算）
        if ($order['freight_amount']>0){
            $distributeAmountItems['allianceItems'][] = [
                'name'=>"配送费",
                'distribute_rate'=>10000,
                'orderGoodsAmount'=> $order['freight_amount'],
            ];
        }


        //支付手续费
        $distributeAmount['paymentHandlingFee'] = ceil($order['real_amount_ac']*$paymentHandlingFeeRate/10000);

        if ($distributeAmount['paymentHandlingFee']>0){
            $distributeAmountItems['paymentHandlingFeeItem'][] = [
                'name'=>"支付手续费",
                'distribute_rate'=>-$paymentHandlingFeeRate,
                'orderGoodsAmount'=>-$distributeAmount['paymentHandlingFee'],
            ];
            $distributeAmountItems['allianceItems'][] = $distributeAmountItems['paymentHandlingFeeItem'][0];
        }

        //联盟点分润
        $distributeAmount['allianceAmount'] =
            $order['real_amount_ac']
            -$distributeAmount['paymentHandlingFee']
            -$distributeAmount['oneLevelAmount']
            -$distributeAmount['twoLevelAmount']
            -$distributeAmount['share1Amount']
            -$distributeAmount['share2Amount']
            -$distributeAmount['deliveryAmount']
            -$distributeAmount['agentAmount']
            -$distributeAmount['companyAmount'];
        if ($distributeAmount['allianceAmount']<0){
            return [false,"联盟点分润金额（{$distributeAmount['allianceAmount']}）不能为负"];
        }

        //保存分润详情
        if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['one_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['oneLevelAmount'],
                $distributeAmountItems['oneLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['two_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['twoLevelAmount'],
                $distributeAmountItems['twoLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])&&$distributeAmount['share1Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_1'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share1Amount'],
                $distributeAmountItems['share1Item'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$distributeAmount['share2Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_2'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share2Amount'],
                $distributeAmountItems['share2Items'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_DELIVERY,
            $order['delivery_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['deliveryAmount'],
            $distributeAmountItems['deliveryItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }


        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_PAYMENT_HANDLING_FEE,
            $order['company_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['paymentHandlingFee'],
            $distributeAmountItems['paymentHandlingFeeItem'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        if ($distributeAmount['agentAmount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_AGENT,
                $order['company_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['agentAmount'],
                $distributeAmountItems['agentItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if ($distributeAmount['companyAmount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_COMPANY,
                $initCompanyId,
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['companyAmount'],
                $distributeAmountItems['companyItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_HA,
            $order['order_owner_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['allianceAmount'],
            $distributeAmountItems['allianceItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        return [true,""];
    }

    public static function distributeDeliveryOrder($order,$orderGoodsModels,$operationId,$operationName){
        $distributeAmount =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount'=>0,
            'companyAmount'=>0,
            'paymentHandlingFee'=>0,
        ];
        $distributeAmountItems =[
            'oneLevelItems' => [],
            'twoLevelItems' =>[],
            'share1Item' => [],
            'share2Items' => [],
            'deliveryItems' => [],
            'agentItems' =>[],
            'companyItems' =>[],
            'paymentHandlingFeeItem'=>[],
        ];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $initCompanyId= Yii::$app->params['option.init.companyId'];

        foreach ($orderGoodsModels as $orderGoodsModel){
            $orderGoodsAmount = $orderGoodsModel['amount_ac'];
            $usedAmount = 0;
            if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['one_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['oneLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"oneLevelItems","one_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['two_level_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['twoLevelAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"twoLevelItems","two_level_rate", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_1'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share1Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share1Items","share_rate_1", $dAmount);
            }

            if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['share_rate_2'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['share2Amount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"share2Items","share_rate_2", $dAmount);
            }

            //代理点分润
            if ($orderGoodsModel['agent_rate']>0){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['agent_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['agentAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"agentItems","agent_rate", $dAmount);
            }

            //平台分润
            if ($orderGoodsModel['company_rate']>0){
                $dAmount = intval($orderGoodsAmount * $orderGoodsModel['company_rate'] / 10000);
                $usedAmount = $usedAmount+$dAmount;
                $distributeAmount['companyAmount'] += $dAmount;
                self::assembleDistributeDetailItem( $distributeAmountItems,$orderGoodsModel,"companyItems","company_rate", $dAmount);
            }

            //团长分润
            $deliveryRestAmount = $orderGoodsAmount - $usedAmount;
            if ($deliveryRestAmount<0){
                return [false,"{$orderGoodsModel['goods_name']}分润设置错误".
                    ',用户一级分润'.Common::showPercentWithUnit($orderGoodsModel['one_level_rate']).
                    ',用户二级分润'.Common::showPercentWithUnit($orderGoodsModel['two_level_rate']).
                    ',分享团长一级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_1']).
                    ',分享团长二级分润'.Common::showPercentWithUnit($orderGoodsModel['share_rate_2']).
                    ',配送团长分润'.Common::showPercentWithUnit($orderGoodsModel['delivery_rate']).
                    ',支付渠道费用分润'.Common::showPercentWithUnit($paymentHandlingFeeRate).
                    ',代理商分润'.Common::showPercentWithUnit($orderGoodsModel['agent_rate']).
                    ',平台分润'.Common::showPercentWithUnit($orderGoodsModel['company_rate'])
                ];
            }
            $distributeAmountItems['deliveryItems'][] = [
                'name'=>"{$orderGoodsModel['id']}-{$orderGoodsModel['schedule_name']}({$orderGoodsModel['goods_name']}{$orderGoodsModel['sku_name']})",
                'distribute_rate'=>10000
                    -$orderGoodsModel['one_level_rate']
                    -$orderGoodsModel['two_level_rate']
                    -$orderGoodsModel['share_rate_1']
                    -$orderGoodsModel['share_rate_2']
                    -$orderGoodsModel['agent_rate']
                    -$orderGoodsModel['company_rate'],
                'orderGoodsAmount'=>$deliveryRestAmount,
            ];
        }


        //配送费结算给配送点（因此不需要特殊计算）
		if ($order['freight_amount']>0){
			$distributeAmountItems['deliveryItems'][] = [
				'name'=>"配送费",
				'distribute_rate'=>10000,
				'orderGoodsAmount'=> $order['freight_amount'],
			];
		}
        

        //支付手续费
        $distributeAmount['paymentHandlingFee'] = ceil($order['real_amount_ac']*$paymentHandlingFeeRate/10000);

        if ($distributeAmount['paymentHandlingFee']>0){
            $distributeAmountItems['paymentHandlingFeeItem'][] = [
                'name'=>"支付手续费",
                'distribute_rate'=>-$paymentHandlingFeeRate,
                'orderGoodsAmount'=>-$distributeAmount['paymentHandlingFee'],
            ];
            $distributeAmountItems['deliveryItems'][] = $distributeAmountItems['paymentHandlingFeeItem'][0];
        }

        //团长订单最后分润
        $deliveryRestOrderAmount =
            $order['real_amount_ac']
            -$distributeAmount['paymentHandlingFee']
            -$distributeAmount['oneLevelAmount']
            -$distributeAmount['twoLevelAmount']
            -$distributeAmount['share1Amount']
            -$distributeAmount['share2Amount']
            -$distributeAmount['agentAmount']
            -$distributeAmount['companyAmount'];
        $distributeAmount['deliveryAmount'] = $deliveryRestOrderAmount;
        if ($deliveryRestOrderAmount<0){
            return [false,"团长自营订单分润金额（{$deliveryRestOrderAmount}）不能为负"];
        }

        //保存分润详情
        if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['one_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['oneLevelAmount'],
                $distributeAmountItems['oneLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,
                $order['two_level_rate_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['twoLevelAmount'],
                $distributeAmountItems['twoLevelItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])&&$distributeAmount['share1Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_1'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share1Amount'],
                $distributeAmountItems['share1Item'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$distributeAmount['share2Amount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_POPULARIZER,
                $order['share_rate_id_2'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['share2Amount'],
                $distributeAmountItems['share2Items'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_DELIVERY,
            $order['delivery_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['deliveryAmount'],
            $distributeAmountItems['deliveryItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }


        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_PAYMENT_HANDLING_FEE,
            $order['company_id'],
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['paymentHandlingFee'],
            $distributeAmountItems['paymentHandlingFeeItem'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        if ($distributeAmount['agentAmount']>0){
            list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_AGENT,
                $order['company_id'],
                $order['company_id'],
                $order['order_no'],
                $order['real_amount_ac'],
                $distributeAmount['agentAmount'],
                $distributeAmountItems['agentItems'],
                $operationId,$operationName,
                DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
            if (!$result){
                return [false,$errorMsg];
            }
        }

        list($result,$errorMsg) = self::createItem(BizTypeEnum::BIZ_TYPE_COMPANY,
            $initCompanyId,
            $order['company_id'],
            $order['order_no'],
            $order['real_amount_ac'],
            $distributeAmount['companyAmount'],
            $distributeAmountItems['companyItems'],
            $operationId,$operationName,
            DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE);
        if (!$result){
            return [false,$errorMsg];
        }

        return [true,""];
    }

    public static function createItem($bizType,$bizId,$company_id,$orderNo,$orderAmount,$distributeAmount,$detail,$operationId,$operationName,$type,$remark='',$typeId=0){
        list($result,$errorMsg) = self::innerCreateItem(DistributeBalanceItem::IN_OUT_IN,$bizType,$bizId,$company_id,$orderNo,$orderAmount,$distributeAmount,$detail,$operationId,$operationName,$type,$remark,$typeId);
        return [$result,$errorMsg];
    }

    public static function createOutItem($bizType,$bizId,$company_id,$orderNo,$orderAmount,$distributeAmount,$detail,$operationId,$operationName,$type,$remark='',$typeId=0){
        list($result,$errorMsg) = self::innerCreateItem(DistributeBalanceItem::IN_OUT_OUT,$bizType,$bizId,$company_id,$orderNo,$orderAmount,$distributeAmount,$detail,$operationId,$operationName,$type,$remark,$typeId);
        return [$result,$errorMsg];
    }

    /**
     * 出入账
     * @param $inOut
     * @param $bizType
     * @param $bizId
     * @param $company_id
     * @param $orderNo
     * @param $orderAmount
     * @param $distributeAmount
     * @param $detail
     * @param $operationId
     * @param $operationName
     * @param $type
     * @param string $remark
     * @param int $typeId
     * @return array
     */
    private static function innerCreateItem($inOut,$bizType,$bizId,$company_id,$orderNo,$orderAmount,$distributeAmount,$detail,$operationId,$operationName,$type,$remark='',$typeId=0){
        list($checkResult,$checkErrorMsg,$userId,$company_id) = self::getUserIdAndCompanyId($bizType,$bizId,$company_id);
        if (!$checkResult){
            return [false,$checkErrorMsg];
        }
        $balance = DistributeBalance::findOne(['biz_type'=>$bizType,'biz_id'=>$bizId]);
        if (empty($balance)){
            $balance = new DistributeBalance();
            $balance->biz_type = $bizType;
            $balance->biz_id = $bizId;
            $balance->company_id = $company_id;
            $balance->amount = 0;
            $balance->remain_amount = 0;
            $balance->version = 0;
            $balance->user_id = $userId;
            if (!$balance->save()){
                return [false,'出账保存失败'];
            }
        }
        if ($inOut==DistributeBalanceItem::IN_OUT_OUT){
            $balance->amount -= $distributeAmount;
        }
        else if ($inOut==DistributeBalanceItem::IN_OUT_IN){
            $balance->amount += $distributeAmount;
        }
        else {
            return [false,'未知的出入账类型'];
        }
        $updateCount = DistributeBalance::updateAll(['amount'=>$balance->amount,'version'=>$balance->version+1],['id'=>$balance->id,'version'=>$balance->version]);
        if ($updateCount<1){
            return [false,'账户明细更新失败'];
        }
        $item = new DistributeBalanceItem();
        $item->distribute_balance_id= $balance['id'];
        $item->order_no = $orderNo;
        $item->order_amount = $orderAmount;
        $item->amount = $distributeAmount;
        $item->distribute_detail = Json::encode($detail);
        $item->status = CommonStatus::STATUS_ACTIVE;
        $item->action = DistributeBalanceItem::ACTION_ACCEPT;
        $item->company_id = $company_id;
        $item->in_out = $inOut;
        $item->biz_id = $bizId;
        $item->biz_type = $bizType;
        $item->type = $type;
        $item->type_id= $typeId;
        $item->user_id = $userId;
        $item->operator_name = $operationName;
        $item->operator_id = $operationId;
        $item->remain_amount = $balance->amount;
        $item->remark = $remark;
        if (!$item->save()){
            return [false,'账户明细保存失败'];
        }
        return [true,''];
    }

    /**
     * 明细
     * @param $bizType
     * @param $bizId
     * @param $type
     * @param int $pageNo
     * @param int $pageSize
     * @return array|DistributeBalanceItem[]|\yii\db\ActiveRecord[]
     */
    public static function getListByBizType($bizType, $bizId, $type=null, $pageNo=1, $pageSize=20){
        $conditions = ['biz_id'=>$bizId,'biz_type'=>$bizType,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isBlank($type)){
            $conditions['type'] = $type;
        }
        $result = (new Query())->from(DistributeBalanceItem::tableName())
            ->where($conditions)
            ->offset(($pageNo-1)*$pageSize)->limit($pageSize)->orderBy('id desc')->all();
        return $result;
    }

    /**
     * 按日期查询
     * @param $bizType
     * @param $bizId
     * @param $startTime
     * @param $endTime
     * @param null $type
     * @return array
     */
    public static function getListByDate($bizType,$bizId,$startTime,$endTime, $type=null ){
        $conditions = [
            'and',
            ['biz_id'=>$bizId,'biz_type'=>$bizType,'status'=>CommonStatus::STATUS_ACTIVE],
            ['>=','created_at',$startTime],
            ['<=','created_at',$endTime],
        ];
        if (!StringUtils::isBlank($type)){
            $conditions[] = ['type'=>$type];
        }
        $result = (new Query())->from(DistributeBalanceItem::tableName())
            ->where($conditions)
            ->orderBy('created_at desc')->all();
        return $result;
    }

    /**
     * 查询分润明细（带订单信息）
     * @param $bizType
     * @param $bizId
     * @param $startTime
     * @param $endTime
     * @return array|DistributeBalanceItem[]|Order[]|\yii\db\ActiveRecord[]
     */
    public static function getDistributeListByDateWithOrder($bizType,$bizId,$startTime,$endTime){
        $conditions = [
            'and',
            ['biz_id'=>$bizId,'biz_type'=>$bizType,'type'=>DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE,'status'=>CommonStatus::STATUS_ACTIVE],
            ['>=','created_at',$startTime],
            ['<=','created_at',$endTime],
        ];
        $result = DistributeBalanceItem::find()
            ->where($conditions)
            ->with(['order'])
            ->orderBy('created_at desc')->asArray()->all();
        return $result;
    }


    /**
     * 按月查询提现流水
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array
     */
    public static function getBalanceDetail($bizType, $bizId,$date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $list = self::getListByDate($bizType,$bizId,$startTime,$endTime);
        $inAmount = 0;
        $outAmount = 0;
        if (!empty($list)){
            foreach ($list as $k=>$v){
                if ($v['action']!=DistributeBalanceItem::ACTION_DENY){
                    if ($v['in_out']==DistributeBalanceItem::IN_OUT_IN){
                        $inAmount += $v['amount'];
                    }
                    else if ($v['in_out']==DistributeBalanceItem::IN_OUT_OUT){
                        $outAmount += $v['amount'];
                    }
                }
            }
        }
        $res = [
            'date_text'=>DateTimeUtils::formatYearAndMonthChinese($date),
            'in_amount'=>$inAmount,
            'out_amount'=>$outAmount,
            'item'=>DistributeBalanceVOService::batchSetVO($list),
        ];
        return $res;
    }


    /**
     * 组装分润详情
     * @param $distributeAmountItems
     * @param $orderGoodsModel
     * @param $itemName
     * @param $rateName
     * @param $dAmount
     */
    public static function assembleDistributeDetailItem(&$distributeAmountItems,$orderGoodsModel,$itemName,$rateName, $dAmount)
    {
        $distributeAmountItems[$itemName][] = [
            'name'=>"{$orderGoodsModel['id']}-{$orderGoodsModel['schedule_name']}({$orderGoodsModel['goods_name']}{$orderGoodsModel['sku_name']})",
            'distribute_rate'=>$orderGoodsModel[$rateName],
            'amount'=>$dAmount,
        ];
    }


    /*------------------------------------预分润相关---------------------------------------------*/

    /**
     * 返回预分润统计
     * @param $bizType
     * @param $bizId
     * @param $startTime
     * @param $endTime
     * @return array|bool
     */
    public static function getPreDistributeOrderSum($bizType,$bizId, $startTime, $endTime)
    {
        $orderTable = Order::tableName();
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $conditions=[
            "and",
            [
                "{$orderPreDistributeTable}.biz_id"=>$bizId,
                "{$orderPreDistributeTable}.biz_type"=>$bizType,
            ],
            ["{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE, Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY, Order::ORDER_STATUS_RECEIVE, Order::ORDER_STATUS_COMPLETE]],
        ];
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        $preSum = (new Query())->from($orderPreDistributeTable)
            ->select(["COALESCE(SUM({$orderPreDistributeTable}.amount),0) as amount","COUNT(*) as count"])
            ->leftJoin($orderTable,"{$orderTable}.order_no={$orderPreDistributeTable}.order_no")
            ->where($conditions)->one();

        return $preSum;
    }



    /**
     * 预分润汇总 所有
     * @param $bizType
     * @param $bizId
     * @return array|bool
     */
    public static function preDistributeAllSum($bizType,$bizId){
        $preDistributeOrdersSum = self::getPreDistributeOrderSum($bizType,$bizId, null, null);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(日)
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeDaySum($bizType,$bizId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum($bizType,$bizId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }

    /**
     * 预分润汇总(月)
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array|bool
     */
    public static function preDistributeMonthSum($bizType,$bizId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $preDistributeOrdersSum = self::getPreDistributeOrderSum($bizType,$bizId, $startTime, $endTime);
        return $preDistributeOrdersSum;
    }


    /**
     * 预分润详情(日)
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array
     */
    public static function preDistributeDay($bizType,$bizId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($date));
        $preDistributeOrders = self::getPreDistributeOrder($bizType,$bizId, $startTime, $endTime);
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
     * @param $bizType
     * @param $bizId
     * @param $date
     * @return array
     */
    public static function preDistributeMonth($bizType,$bizId, $date){
        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($date));
        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($date));
        $preDistributeOrders = self::getPreDistributeOrder($bizType,$bizId, $startTime, $endTime);
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
     * 获取预分润订单信息
     * @param $bizType
     * @param $bizId
     * @param $startTime
     * @param $endTime
     * @return array
     */
    public static function getPreDistributeOrder($bizType,$bizId, $startTime, $endTime)
    {
        $orderTable = Order::tableName();
        $orderPreDistributeTable = OrderPreDistribute::tableName();
        $conditions = [
            "and",
            [
                "{$orderPreDistributeTable}.biz_id"=>$bizId,
                "{$orderPreDistributeTable}.biz_type"=>$bizType,
            ],
            ["{$orderTable}.order_status"=>[Order::ORDER_STATUS_PREPARE, Order::ORDER_STATUS_DELIVERY, Order::ORDER_STATUS_SELF_DELIVERY, Order::ORDER_STATUS_RECEIVE, Order::ORDER_STATUS_COMPLETE]],
        ];
        if (!StringUtils::isBlank($startTime)){
            $conditions[] = ['>=', "{$orderPreDistributeTable}.order_time", $startTime];
        }
        if (!StringUtils::isBlank($endTime)){
            $conditions[] = ['<=',  "{$orderPreDistributeTable}.order_time", $endTime];
        }
        $preDistributeOrders = (new Query())->from($orderPreDistributeTable)
            ->innerJoin($orderTable,"{$orderTable}.order_no={$orderPreDistributeTable}.order_no")
            ->where($conditions)->orderBy("{$orderPreDistributeTable}.order_time desc")->all();
        return $preDistributeOrders;
    }


    /**
     * 组装详情
     * @param $preDistributeOrders
     * @param $preDistributeArr
     * @param $preDistributeAmount
     */
    public static function assembleDetail($preDistributeOrders, &$preDistributeArr, &$preDistributeAmount){
        if (!empty($preDistributeOrders)){
            foreach ($preDistributeOrders as $k=>$v){
                $preOrderDetail = [
                    'order_no'=>$v['order_no'],
                    'order_time'=>$v['order_time'],
                    'real_amount'=>$v['real_amount'],
                    'amount'=>$v['amount'],
                ];
                $preDistributeArr[] = $preOrderDetail;
                $preDistributeAmount += $preOrderDetail['amount'];
            }
        }
    }


    /**
     * 如果是null，返回0
     * @param $model
     * @return DistributeBalance
     */
    public static function createEmptyIfNull($model){
        if ($model==null){
            $model = new DistributeBalance();
            $model->remain_amount = 0;
            $model->amount = 0;
        }
        return $model;
    }

    /**
     * 校验分润账户有效性
     * @param $bizType
     * @param $bizId
     * @param $company_id
     * @return array
     */
    private static function getUserIdAndCompanyId($bizType,$bizId,$company_id){
        $userId = null;
        if ($bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE){
            $customerModel = CustomerService::getActiveModel($bizId);
            if (empty($customerModel)){
                return [false,'用户无法找到',null,null];
            }
            $userId = $customerModel['user_id'];
            $company_id = Yii::$app->params['option.init.companyId'];
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            $popularizerModel = PopularizerService::getActiveModel($bizId,$company_id);
            if (empty($popularizerModel)){
                return [false,'分享者无法找到',null,null];
            }
            $userId = $popularizerModel['user_id'];
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            $deliveryModel = DeliveryService::getActiveModel($bizId,$company_id);
            if (empty($deliveryModel)){
                return [false,'配送团长无法找到',null,null];
            }
            $userId = $deliveryModel['user_id'];
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_HA){
            $allianceModel = AllianceService::getActiveModel($bizId,$company_id);
            if (empty($allianceModel)){
                return [false,'联盟商户无法找到',null,null];
            }
            $userId = $allianceModel['user_id'];
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_PAYMENT_HANDLING_FEE){
            $userId = $company_id;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_AGENT){
            $userId = $company_id;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_COMPANY){
            $userId = $company_id;
            $company_id = Yii::$app->params['option.init.companyId'];
        }
        return [true,'',$userId,$company_id];
    }


    public static function encodePayChargeAttachMessage($bizType,$bizId){
        return "{$bizType}-{$bizId}";
    }

    public static function decodePayChargeAttachMessage($attach){
        if (StringUtils::isEmpty($attach)||!StringUtils::containsSubString($attach,"-")){
            return [false,"",""];
        }
        $arr = explode("-",$attach);
        return [true,$arr[0],$arr[1]];
    }


}