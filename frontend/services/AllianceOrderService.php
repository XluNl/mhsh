<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/03/003
 * Time: 2:02
 */
namespace frontend\services;


use common\configuration\DistributeSwitchUtil;
use common\models\Alliance;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderPreDistribute;
use common\utils\ArrayUtils;
use common\utils\CopyUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class AllianceOrderService extends \common\services\OrderService
{


    public static function checkAllianceStatus($companyId, $skuList) {
        ExceptionAssert::assertNotEmpty($skuList,StatusCode::createExp(StatusCode::GOODS_NOT_EXIST));
        $allianceId = null;
        foreach ($skuList as $k=>$v){
            $allianceId = $v['goods_owner_id'];
            break;
        }
        $alliance = AllianceService::getActiveModel($allianceId,$companyId);
        ExceptionAssert::assertNotNull($alliance,StatusCode::createExp(StatusCode::ALLIANCE_NOT_EXIST));
        ExceptionAssert::assertTrue($alliance['status']==Alliance::STATUS_ONLINE,StatusCode::createExp(StatusCode::ALLIANCE_NOT_ONLINE));
        AllianceService::getDisplayVO($alliance);
        return $alliance;
    }



    /**
     * 校验空购物车
     * @param $skuList
     */
    public static function checkSkuListNotEmpty($skuList){
        ExceptionAssert::assertTrue(!empty($skuList),StatusCode::createExp(StatusCode::GOODS_NOT_EXIST));
    }


    /**
     * 计算价格、节省金额
     * @param $company_id
     * @param $scheduleId
     * @param $num
     * @return array
     */
    public static function calculatePrice($company_id,$scheduleId,$num){
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $skuList = [$scheduleId=>$num];
        $goods_total = $num;
        $price_total = 0;
        if (!empty($skuList)){
            $skuModels = GoodsScheduleService::getSoldUpByIds(null,$company_id,$scheduleId,[GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_OUTER],$deliveryId);
            $skuModels = ArrayUtils::index($skuModels,'schedule_id');
            foreach ($skuList as $key => $value) {
                if (ArrayHelper::keyExists($key,$skuModels)){
                    $model = $skuModels[$key];
                    $price_total += $model['price'] * $value;
                    $goods = $model;
                    $goods["num"] = $value;
                    $skuList[$key] = $goods;
                }
                else{
                    unset($skuList[$key]);
                }
            }
        }
        return [$price_total,$goods_total,$skuList];
    }



    /**
     * 确认特价菜是否超过购买限制
     * @param $company_id
     * @param $cModel
     * @param $skuList
     */
    public static function checkBargainGoodCount($company_id,$cModel, $skuList) {
        $msg = "";
        foreach ($skuList as $k=> $sku){
            if ($sku['schedule_limit_quantity']>=0){
                if ($sku['schedule_limit_quantity']==0){
                    $msg = "{$msg}{$sku['schedule_name']}({$sku['goods_name']}{$sku['sku_name']})-最多还可购买0个;";
                }
                $boughtNum = OrderStatisticsService::getBoughtNumInScheduled($sku["sku_id"],$cModel->id,$company_id,$sku['online_time']);
                $remain_num  = $sku['schedule_limit_quantity']-$boughtNum;
                $remain_num = $remain_num<0?0:$remain_num;
                $goods_num = $sku['num'];
                if ($remain_num<$goods_num){
                    $msg = "{$msg}{$sku['schedule_name']}({$sku['goods_name']}{$sku['sku_name']})最多还可购买{$remain_num}个;";
                    $sku["num"] = $remain_num;
                    if ($remain_num==0){
                        unset($skuList[$k]);
                    }
                }
            }
        }
        if (!empty($msg)){
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::REMOVE_EXTRA_SKU_NUM,$msg));
        }
    }

    /**
     * 创建订单（事务）
     * @param $companyId
     * @param $cModel
     * @param $skuList
     * @param $coupon_no
     * @param $deliveryType
     * @param $addressId
     * @param $orderNote
     * @param $alliance
     * @return string
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public static function createOrderTransaction($companyId, $cModel, $skuList, $coupon_no, $deliveryType, $addressId, $orderNote, $alliance){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orderNo = self::createOrder($companyId, $cModel, $skuList, $coupon_no, $deliveryType, $addressId, $orderNote, $alliance);
            $transaction->commit();
            return $orderNo;
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            \yii::error($e->getMessage());
            throw $e;
        }
        catch (Exception $e) {
            $transaction->rollBack();
            \yii::error($e->getMessage());
            throw StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,$e->getMessage());
        }
    }



    /**
     * 创建订单
     * @param $companyId
     * @param $cModel
     * @param $skuList
     * @param $coupon_no
     * @param $deliveryType
     * @param $addressId
     * @param $orderNote
     * @param $alliance
     * @return string
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public static function createOrder($companyId, $cModel, $skuList, $coupon_no, $deliveryType, $addressId, $orderNote, $alliance,$orderType=GoodsConstantEnum::TYPE_OBJECT){
        //下单
        $remark = [];
        $order = new Order();
        $order->company_id = $companyId;
        $discountAmount = 0;
        $discountDetails = [];


        $deliveryModel = FrontendCommon::requiredCanOrderDelivery();
        $deliveryId = $deliveryModel['id'];
        $address = AddressService::getAddressById($addressId,$cModel['id']);
        ExceptionAssert::assertNotNull($address,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'地址信息不存在'));
        $order->order_no = $order->generateOrderNo();
        $order->delivery_code = $order->generateDeliveryCode($cModel['id']);
        $order->delivery_id = $deliveryId;
        $order->delivery_nickname = $alliance['nickname'];
        $order->delivery_name = $alliance['realname'];
        $order->delivery_phone = $alliance['phone'];
        $order->need_amount = 0;
        $order->need_amount_ac = 0;
        $order->goods_num = 0;
        $order->order_note = $orderNote;
        $couponDistribution = [];
        if (!StringUtils::isBlank($coupon_no)){
            $couponModel = CouponService::getCouponArrayByNo($coupon_no,$cModel->id,$companyId);
            $canUse = CouponService::couponUseForGoodsList($skuList,$couponModel);
            ExceptionAssert::assertTrue($canUse,StatusCode::createExpWithParams(StatusCode::ORDER_COUPON_CAN_NOT_USE,"优惠券不存在"));
            CouponService::distributeDiscount($couponModel);
            $couponDistribution = $couponModel['couponDistribution'];
            $couponDistribution = ArrayHelper::index($couponDistribution,'sku_id');
        }
        foreach ($skuList as $key => $value) {
            self::addOrderGoods($value, $order, $companyId, $deliveryId, $couponDistribution, $remark);
            $skuList[$key] = $value;
        }
        if (!StringUtils::isBlank($coupon_no)){
            CouponService::verifyCoupon($companyId,$cModel->id,$coupon_no,$order->order_no);
            $discountAmount += $couponModel['calcDiscount'];
            $discountDetails[] = [
                'type'=>'coupon',
                'discount'=>$couponModel['calcDiscount'],
                'code'=>$couponModel['coupon_no'],
                'desc'=>CouponService::generateDescModel($couponModel)];
        }
        $order->goods_num_ac = 0;
        $order->order_type = $orderType;
        $order->order_owner = GoodsConstantEnum::OWNER_HA;
        $order->order_owner_id = $alliance['id'];
        $order->pay_status = Order::PAY_STATUS_UN_PAY;
        $order->order_status = Order::ORDER_STATUS_UN_PAY;
        $order->customer_service_status = Order::CUSTOMER_SERVICE_STATUS_FALSE;
        $order->customer_id = $cModel['id'];
        $order->accept_nickname = $address['name'];
        $order->accept_name = $address['name'];
        $order->accept_mobile = $address['phone'];
        $order->accept_province_id = $address['province_id'];
        $order->accept_city_id = $address['city_id'];
        $order->accept_county_id = $address['county_id'];
        $order->accept_community = $address['community'];
        $order->accept_address = $address['address'];
        $order->accept_lat = $address['lat'];
        $order->accept_lng = $address['lng'];
        $order->accept_delivery_type = $deliveryType;
        $order->discount_amount = $discountAmount;
        $order->discount_details = Json::encode($discountDetails);
        $order->freight_amount = AllianceService::getFreight($deliveryType,$alliance['id'],$companyId,$order->need_amount,0);
        $order->real_amount = $order->calcRealAmount();
        ExceptionAssert::assertTrue($order->real_amount>=10,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'订单金额最小为0.01'));
        $order->real_amount_ac = 0;
        $order->admin_note = Json::encode($remark);
        $order->balance_pay_amount = CustomerBalanceService::getAvailableMaxBalanceAndVerify($cModel->id,$order->real_amount,$order->order_no,$cModel->id,$cModel->nickname);
        $order->three_pay_amount = $order->real_amount - $order->balance_pay_amount;
        $order->share_rate_id_1 = DistributeSwitchUtil::isDistributeOpen('share_rate_id_1')?PopularizerBindService::queryPopularizerRelative($companyId,$cModel['id']):null;
        $order->one_level_rate_id = DistributeSwitchUtil::isDistributeOpen('one_level_rate_id')?CustomerInvitationService::getInvitationById($cModel['id']):null;
        $order->two_level_rate_id = DistributeSwitchUtil::isDistributeOpen('two_level_rate_id')?CustomerInvitationService::getTwoInvitationById($cModel['id'],$order->one_level_rate_id):null;
        ExceptionAssert::assertTrue($order->save(),StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'订单保存失败'));

        //记录预分润数据
        self::preDistributeHA($skuList,$order);
        //记录订单日志
        OrderLogService::addCreateOrderLog($order);
        //FrontendCommon::sendOrderMessage($order->order_no,Yii::$app->user->identity->openid);
        return $order->order_no;
    }



    /**
     * 增加订单商品
     * @param $value
     * @param Order $order
     * @param $company_id
     * @param $deliveryId
     * @param array $couponDistribution
     * @param array $remark
     * @throws \ReflectionException
     */
    public static function addOrderGoods(&$value, Order $order, $company_id, $deliveryId, array $couponDistribution, array &$remark)
    {
        $orderGoods = new OrderGoods();
        CopyUtils::copyFromArrayToObject($value, $orderGoods);
        $order->goods_num += 1;
        $orderGoods->id = null;
        $orderGoods->sku_price = $value['price'];
        $orderGoods->company_id = $company_id;
        $orderGoods->order_no = $order->order_no;
        $orderGoods->num = $value['num'];
        $orderGoods->num_ac = 0;
        $orderGoods->delivery_id = $deliveryId;
        $orderGoods->delivery_status = OrderGoods::DELIVERY_STATUS_PREPARE;
        $orderGoods->expect_arrive_time = $value['expect_arrive_time'];
        $order->need_amount += $orderGoods->num * $orderGoods->sku_price;
        if (key_exists($orderGoods->sku_id, $couponDistribution)) {
            $orderGoods->discount = $couponDistribution[$orderGoods->sku_id]['discount'];
        } else {
            $orderGoods->discount = 0;
        }
        $orderGoods->amount = $orderGoods->num * $orderGoods->sku_price-$orderGoods->discount;
        $orderGoods->amount_ac = 0;
        $orderGoods->status = CommonStatus::STATUS_ACTIVE;
        $value['amount'] = $orderGoods->amount;
        if (array_keys($value, 'remark') && !empty($value['remark'])) {
            $remark[] = ['sku_name' => $value['sku_name'], 'remark' => $value['remark']];
        }
        GoodsSkuService::reduceStock($orderGoods->schedule_id, $orderGoods->sku_id, $orderGoods->num);
        if (!$orderGoods->validate()) {
            Yii::error("订单商品保存失败" . Json::encode($orderGoods->errors));
        }
        ExceptionAssert::assertTrue($orderGoods->save(), StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR, '订单商品保存失败'));
    }

    /**
     * 校验只有团长才能购买
     * @param $userId
     * @param $companyId
     */
    public static function checkDeliveryCanBuy($userId,$companyId){
        $deliveries = UserService::getDeliveriesByCompanyId($userId,$companyId);
        ExceptionAssert::assertNotEmpty($deliveries,StatusCode::createExp(StatusCode::DELIVERY_CAN_BUY_ALLIANCE_GOODS));
    }

    /**
     * 记录预分润数据
     * @param $skuList
     * @param $order Order
     * @return array
     */
    public static function preDistributeHA($skuList, $order){
        $initCompanyId= Yii::$app->params['option.init.companyId'];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $preDistribute =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount' => 0,
            'allianceAmount'=>0,
            'companyAmount' => 0,
            'paymentHandlingFee'=>0,
        ];
        if (!empty($skuList)) {
            foreach ($skuList as $k => $v) {
                $amount = $v['amount'];
                $preDistribute['oneLevelAmount'] += intval($amount * $v['one_level_rate'] / 10000);
                $preDistribute['twoLevelAmount'] += intval($amount * $v['two_level_rate'] / 10000);
                $preDistribute['share1Amount'] += intval($amount * $v['share_rate_1'] / 10000);
                $preDistribute['share2Amount'] += intval($amount * $v['share_rate_2'] / 10000);
                $preDistribute['deliveryAmount'] += intval($amount * $v['delivery_rate'] / 10000);
                $preDistribute['agentAmount'] += intval($amount * $v['agent_rate'] / 10000);
                $preDistribute['companyAmount'] += intval($amount * $v['company_rate'] / 10000);

            }
        }
        //支付渠道费
        $preDistribute['paymentHandlingFee'] = intval($order['real_amount'] * $paymentHandlingFeeRate / 10000);
        //联盟的单子配送费给商户
        $preDistribute['allianceAmount'] =
            $order['real_amount']
            -$preDistribute['paymentHandlingFee']
            -$preDistribute['oneLevelAmount']
            -$preDistribute['twoLevelAmount']
            -$preDistribute['share1Amount']
            -$preDistribute['share2Amount']
            -$preDistribute['deliveryAmount']
            -$preDistribute['agentAmount']
            -$preDistribute['companyAmount'];

        if (NumberUtils::notNullAndPositiveInteger($order['one_level_rate_id'])){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_CUSTOMER,$order['one_level_rate_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['oneLevelAmount']);
        }
        if (NumberUtils::notNullAndPositiveInteger($order['two_level_rate_id'])){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_CUSTOMER,$order['two_level_rate_id'],OrderPreDistribute::LEVEL_TWO,$preDistribute['twoLevelAmount']);
        }
        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_1'])&&$preDistribute['share1Amount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_POPULARIZER,$order['share_rate_id_1'],OrderPreDistribute::LEVEL_ONE,$preDistribute['share1Amount']);
        }
        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$preDistribute['share1Amount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_POPULARIZER,$order['share_rate_id_2'],OrderPreDistribute::LEVEL_TWO,$preDistribute['share2Amount']);
        }
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_DELIVERY,$order['delivery_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['deliveryAmount']);
        if ($preDistribute['agentAmount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_AGENT,$order['company_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['agentAmount']);
        }
        if ($preDistribute['companyAmount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_COMPANY,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['companyAmount']);
        }
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_PAYMENT_HANDLING_FEE,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['paymentHandlingFee']);
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_ALLIANCE,$order['order_owner_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['allianceAmount']);
        return $preDistribute;

    }

}