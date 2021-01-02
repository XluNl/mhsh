<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/03/003
 * Time: 2:02
 */
namespace frontend\services;


use common\configuration\DistributeSwitchUtil;
use common\models\Common;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\Options;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\models\OrderPay;
use common\models\OrderPreDistribute;
use common\models\Payment;
use common\services\GroupRoomOrderService;
use common\utils\ArrayUtils;
use common\utils\CopyUtils;
use common\utils\DateTimeUtils;
use common\utils\NumberUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\AssertDefaultUtil;
use frontend\utils\ExceptionAssert;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\StatusCode;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class OrderService extends \common\services\OrderService
{
    /**
     * 订单列表
     * @param $customer_id
     * @param $filter
     * @param $ownerType
     * @param $orderType
     * @param $keyword
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getPageFilterOrder($customer_id,$filter,$ownerType,$orderType,$keyword,$pageNo=1,$pageSize=20){
        $orderTable = Order::tableName();
        $orderGoodsTable = OrderGoods::tableName();

        $conditions = ['and',["{$orderTable}.customer_id" => $customer_id,"{$orderTable}.order_owner"=>$ownerType]];
        if (StringUtils::isNotBlankAndNotEmpty($orderType)){
            $conditions[] = ["{$orderTable}.order_type"=>$orderType];
        }

        $query = Order::find()->with(['goods','delivery'])->offset(($pageNo - 1) * $pageSize)->limit($pageSize);
        $query->orderBy("{$orderTable}.created_at desc");

        if (StringUtils::isNotBlank($keyword)){
            $conditions[] = [
                'or',
                ["{$orderTable}.accept_mobile" => $keyword],
                ["{$orderTable}.order_no" => $keyword],
                ["{$orderTable}.accept_name" => $keyword],
                ['like',"{$orderGoodsTable}.goods_name",$keyword],
            ];
            $query->leftJoin($orderGoodsTable,"{$orderTable}.order_no={$orderGoodsTable}.order_no")
                ->select(["DISTINCT({$orderTable}.id)","{$orderTable}.*"]);
        }

        switch ($ownerType){
            case [GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_DELIVERY]:
                break;
            case GoodsConstantEnum::OWNER_SELF:
                break;
            case GoodsConstantEnum::OWNER_DELIVERY:
                break;
            case GoodsConstantEnum::OWNER_HA:
                $query = $query->with('alliance');
                break;
            default:
                return [];
                break;
        }
        switch ($filter) {
            case "all":
                $query = $query->with('evaluate');
                break;
            case "unpay":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_UN_PAY,
                ];
                $condition['pay_status'] = Order::PAY_STATUS_UN_PAY;
                $conditions[]= $condition;
                break;
            case "transport":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_PREPARE,
                ];
                $conditions[]= $condition;
                break;
            case "delivery":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_DELIVERY,
                    Order::ORDER_STATUS_SELF_DELIVERY,
                ];
                $conditions[]= $condition;
                break;
            case "complete":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_RECEIVE,
                    Order::ORDER_STATUS_COMPLETE,
                    Order::ORDER_STATUS_CANCELING,
                    Order::ORDER_STATUS_CANCELED
                ];
                $conditions[]= $condition;
                break;
            case "customer-service":
                $condition['customer_service_status'] = Order::CUSTOMER_SERVICE_STATUS_TRUE;
                $query->orderBy("{$orderTable}.updated_at desc");
                $conditions[]= $condition;
                break;
            case "checking":
                $condition['order_status'] = [
                    Order::ORDER_STATUS_CHECKING,
                ];
                $conditions[]= $condition;
                break;
            default:
                return [];
                break;
        }
        $orders = $query->where($conditions)
            ->asArray()
            ->all();
        //处理状态展示文本
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        OrderDisplayDomainService::completeDeliveryInfoList($orders);
        $orders = self::completeGroupRoomsInfoCommon($orders);
        $orders = GroupRoomService::batchSetRoomOwnerTagForOrder($orders);
        return $orders;
    }

    /**
     * @param $customer_id
     * @param $orderType
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getWaitGetOrder($customer_id,$orderType){
        $conditions = ['customer_id' => $customer_id,'order_owner'=>$orderType,'order_status'=>[Order::ORDER_STATUS_DELIVERY,Order::ORDER_STATUS_SELF_DELIVERY]];
        $orders = (new Query())->from(Order::tableName())->where($conditions)->all();
        $orders = OrderDisplayDomainService::batchDefineOrderDisplayData($orders);
        return $orders;
    }


    /**
     * 校验订单是否存在array
     * @param $order_no
     * @param $company_id
     * @return array|bool
     */
    public static function validateOrderArray($order_no,$company_id){
        ExceptionAssert::assertNotBlank($order_no,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_no'));
        $order = (new Query())->from(Order::tableName())->where(['order_no' => $order_no,'company_id'=>$company_id])->all();
        ExceptionAssert::assertNotEmpty($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order[0];
    }

    /**
     * 校验订单是否存在model
     * @param $order_no
     * @param $company_id
     * @return Order|null
     */
    public static function validateOrderModel($order_no,$company_id){
        ExceptionAssert::assertNotBlank($order_no,StatusCode::createExp(StatusCode::STATUS_PARAMS_MISS));
        $order = Order::findOne(['order_no' => $order_no,'company_id'=>$company_id]);
        ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }

    /**
     * 校验空购物车
     * @param $userId
     */
    public static function checkEmptyCart($userId){
        ExceptionAssert::assertTrue(!CartOperationService::isEmpty($userId),StatusCode::createExp(StatusCode::CART_EMPTY));
    }

    /**
     * 校验空购物车
     * @param $skuList
     */
    public static function checkSkuList($skuList){
        ExceptionAssert::assertTrue(!empty($skuList),StatusCode::createExp(StatusCode::CART_EMPTY));
    }

    /**
     * 检查营业时间
     */
    public static function checkOpeningHours(){
        $nowStr = date("H:i",time());
        $start_order_time = Options::get_option_with_default("start_order_time","00:00");
        $end_order_time = Options::get_option_with_default("end_order_time","23:59");
        ExceptionAssert::assertTrue($start_order_time<=$nowStr&&$end_order_time>=$nowStr,StatusCode::createExpWithParams(StatusCode::STORE_NOT_OPEN,$start_order_time,$end_order_time));
    }

    /**
     * 校验下单模式
     * @param $ownerType
     */
    public static function checkOwnerType($ownerType){
        ExceptionAssert::assertNotNull($ownerType,StatusCode::createExp(StatusCode::ORDER_NOT_ALLOW_OWNER_TYPE));
        if (is_array($ownerType)){
            foreach ($ownerType as $v){
                ExceptionAssert::assertTrue(in_array($v,[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA,GoodsConstantEnum::OWNER_DELIVERY]),StatusCode::createExp(StatusCode::ORDER_NOT_ALLOW_OWNER_TYPE));
            }
        }
        else{
            ExceptionAssert::assertTrue(in_array($ownerType,[GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::OWNER_HA,GoodsConstantEnum::OWNER_DELIVERY]),StatusCode::createExp(StatusCode::ORDER_NOT_ALLOW_OWNER_TYPE));
        }
    }

    /**
     * 获得ownerId
     * @param $ownerType
     * @param $deliveryId
     * @param null $allianceId
     * @return int|null
     */
    public static function getOwnerId($ownerType,$deliveryId,$allianceId=null){
        if ($ownerType==GoodsConstantEnum::OWNER_SELF){
            return GoodsConstantEnum::OWNER_SELF_ID;
        }
        else if ($ownerType==GoodsConstantEnum::OWNER_DELIVERY){
            return $deliveryId;
        }
        else if ($ownerType==GoodsConstantEnum::OWNER_HA){
            return null;
        }
        return null;
    }

    /**
     * 检查团点是否允许下单
     */
    public static function checkStorageAllowOrder($cModel){
        $deliveryModel = FrontendCommon::requiredCanOrderDelivery();
    }

    /**
     * 获取购物车信息
     * @param $cModel
     * @param $company_id
     * @param $userId
     * @param $deliveryId
     * @param $ownerType
     * @return array
     */
    public static function getCartGoods($cModel, $company_id, $userId, $deliveryId,$ownerType)
    {
        $scheduleLists = CartOperationService::listGoodsWithCheck($userId);
        $price_total = 0;
        if (!empty($scheduleLists)){
            $scheduleIds = array_keys($scheduleLists);
            $scheduleModels = GoodsScheduleService::getDisplayUpByIds(null,$company_id,$scheduleIds,null,null);
            $scheduleModels = ArrayUtils::index($scheduleModels,'schedule_id');
            foreach ($scheduleLists as $key => $value) {
                if (ArrayHelper::keyExists($key,$scheduleModels)){
                    $model = $scheduleModels[$key];
                    if ($model['goods_owner']==$ownerType&&in_array($model['delivery_id'],[$deliveryId,'0'])){
                        $price_total += $model['price'] * $value['num'];
                        $goods = $model;
                        $goods["num"] = $value['num'];
                        $goods["is_check"] = $value['is_check'];
                        $scheduleLists[$key] = $goods;
                    }
                    else{
                        unset($scheduleLists[$key]);
                    }
                }
                else{
                    unset($scheduleLists[$key]);
                    CartOperationService::modifyGoods($userId,$key,0);
                }
            }
            $scheduleLists = IndexService::assembleStatusAndImageAndExceptTime($scheduleLists);
            $scheduleLists = array_values($scheduleLists);
        }
        $cart = ['goods_total' => count($scheduleLists),'goods_list' => $scheduleLists,'price_total' => $price_total];
        return $cart;
    }


    /**
     * 计算价格、节省金额，统计特价商品、商品名称
     * @param $userId
     * @param $cModel
     * @param $ownerType
     * @return array
     */
    public static function calculatePrice($userId,$cModel,$ownerType){
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();
        $skuList = CartOperationService::listCheckGoods($userId);
        $goods_total = 0;
        $price_total = 0;
        if (!empty($skuList)){
            $scheduleIds = array_keys($skuList);
            $skuModels = GoodsScheduleService::getSoldUpByIds($ownerType,$company_id,$scheduleIds,[GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_SPIKE,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT],$deliveryId);
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
            $goods_total = count($skuList);
        }
        return [$price_total,$goods_total,$skuList];
    }

    /**
     * 校验是否达到起送金额
     * @param $cModel
     * @param $price_total
     */
    public static function checkDeliveryAmount($cModel, $price_total) {
        $company_id = FrontendCommon::requiredFCompanyId();
        $deliveryModel = FrontendCommon::requiredDelivery();
        $minAmountLimit = $deliveryModel->min_amount_limit;
        if ($price_total < $minAmountLimit) {
            $existOrderAmount = OrderStatisticsService::getTodayRealAmount($cModel->id,$company_id);
            ExceptionAssert::assertTrue($existOrderAmount + $price_total >= $minAmountLimit,StatusCode::createExpWithParams(StatusCode::NOT_REACH_DELIVERY_START_LIMIT,Common::showAmount($minAmountLimit).'元'));
        }
    }


    /**
     * 订单确认
     * @param $order
     * @param $operationId
     * @param $operationName
     * @throws BusinessException
     * @throws Exception
     */
    public static function complete($order,$operationId=null,$operationName=null){
        AssertDefaultUtil::setNotNull($operationId,$order['customer_id']);
        AssertDefaultUtil::setNotNull($operationName,$order['accept_nickname']);
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($result,$errorMsg) = self::completeOrder($order,$paymentSdk,OrderLogs::ROLE_CUSTOMER,$operationId,$operationName);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::ORDER_COMPLETE_ERROR,$errorMsg));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ORDER_COMPLETE_ERROR,$e->getMessage()));
        }
    }

    /**
     * 确认特价菜是否超过购买限制
     * @param $userId
     * @param $cModel
     * @param $skuList
     */
    public static function checkBargainGoodCount($userId,$cModel, $skuList) {
        $company_id = FrontendCommon::requiredFCompanyId();
        $msg = "";
        if (!empty($skuList)) {
            foreach ($skuList as $k=> $sku){
                if ($sku['schedule_limit_quantity']>=0){
                    if ($sku['schedule_limit_quantity']==0){
                        $msg = "{$msg}{$sku['schedule_name']}({$sku['goods_name']}{$sku['sku_name']})-最多还可购买0个;";
                        CartOperationService::modifyGoods($userId,$sku["sku_id"], 0);
                    }
                    $boughtNum = OrderStatisticsService::getBoughtNumInScheduled($sku["sku_id"],$cModel->id,$company_id,$sku['online_time']);
                    $remain_num  = $sku['schedule_limit_quantity']-$boughtNum;
                    $remain_num = $remain_num<0?0:$remain_num;
                    $goods_num = $sku['num'];
                    if ($remain_num<$goods_num){
                        $msg = "{$msg}{$sku['schedule_name']}({$sku['goods_name']}{$sku['sku_name']})最多还可购买{$remain_num}个;";
                        $sku["num"] = $remain_num;
                        CartOperationService::modifyGoods($userId,$sku["sku_id"], $remain_num);
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
    }


    /**
     * 校验该优惠券是否可用
     * @param $company_id
     * @param $coupon_no
     * @param $customerId
     * @param $goods_list
     */
    public static function checkCoupon($company_id,$coupon_no,$customerId,$goods_list){
        if (StringUtils::isBlank($coupon_no)){
            return;
        }
        list($canUseCoupon,$couponError) = CouponService::checkCoupon($company_id,$coupon_no,$customerId,$goods_list);
        ExceptionAssert::assertTrue($canUseCoupon,StatusCode::createExpWithParams(StatusCode::ORDER_COUPON_CAN_NOT_USE,$couponError));
    }

    /**
     * 创建订单（事务）
     * @param $companyId
     * @param $userId
     * @param $cModel
     * @param $skuList
     * @param $coupon_no
     * @param $deliveryType
     * @param $addressId
     * @param $orderNote
     * @param $ownerType
     * @param int $orderType
     * @return string
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public static function createOrderTransaction($companyId,$userId,$cModel, $skuList, $coupon_no, $deliveryType, $addressId,$orderNote,$ownerType,$orderType=GoodsConstantEnum::TYPE_OBJECT){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orderNo =  self::createOrder($companyId,$userId,$cModel, $skuList, $coupon_no, $deliveryType, $addressId,$orderNote,$ownerType,$orderType);
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
     * @param $userId
     * @param $cModel
     * @param $skuList
     * @param $coupon_no
     * @param $deliveryType
     * @param $addressId
     * @param $orderNote
     * @param $ownerType
     * @param int $orderType
     * @return string
     * @throws BusinessException
     * @throws \ReflectionException
     */
    public static function createOrder($companyId,$userId,$cModel, $skuList, $coupon_no, $deliveryType, $addressId,$orderNote,$ownerType,$orderType=GoodsConstantEnum::TYPE_OBJECT){
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
        $order->delivery_nickname = $deliveryModel['nickname'];
        $order->delivery_name = $deliveryModel['realname'];
        $order->delivery_phone = $deliveryModel['phone'];
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
        $order->order_owner = $ownerType;
        $order->order_owner_id = OrderService::getOwnerId($ownerType,$deliveryId);
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
        $order->freight_amount = DeliveryService::getFreight($deliveryType,$deliveryId,$companyId,$order->need_amount,0);
        $order->real_amount = $order->calcRealAmount();
        ExceptionAssert::assertTrue($order->real_amount>=10,StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'订单金额最小为0.01'));
        $order->real_amount_ac = 0;
        $order->admin_note = Json::encode($remark);
        // 余额支付金额
        $order->balance_pay_amount = CustomerBalanceService::getAvailableMaxBalanceAndVerify($cModel->id,$order->real_amount,$order->order_no,$cModel->id,$cModel->nickname);
        // 第三方支付金额
        $order->three_pay_amount = $order->real_amount - $order->balance_pay_amount;
        $order->share_rate_id_1 = DistributeSwitchUtil::isDistributeOpen('share_rate_id_1')?PopularizerBindService::queryPopularizerRelative($companyId,$cModel['id']):null;
        $order->one_level_rate_id = DistributeSwitchUtil::isDistributeOpen('one_level_rate_id')?CustomerInvitationService::getInvitationById($cModel['id']):null;
        $order->two_level_rate_id = DistributeSwitchUtil::isDistributeOpen('two_level_rate_id')?CustomerInvitationService::getTwoInvitationById($cModel['id'],$order->one_level_rate_id):null;
        ExceptionAssert::assertTrue($order->save(),StatusCode::createExpWithParams(StatusCode::ORDER_ORDER_ERROR,'订单保存失败'));

        //记录预分润数据
        self::preDistribute($skuList,$order);
        //记录订单日志
        OrderLogService::addCreateOrderLog($order);

        self::removeFromCart($userId,$skuList);


        //FrontendCommon::sendOrderMessage($order->order_no,Yii::$app->user->identity->openid);
        return $order->order_no;
    }

    /**
     * 输出展示的支付方式
     * @param $payments
     * @param $order
     * @param $openId
     */
    public static function displayPayments(&$payments,$order,$openId){
        foreach ($payments as $paymentK =>$paymentV){
            if ($paymentV['type']==Payment::TYPE_WECHAT&&$order['three_pay_amount']>0){
                $openid = $openId;
                $jsApiParameters = PaymentService::generateJSSdkPayInfo($openid,$order['order_no'],$order['three_pay_amount'],$order['created_at']);


                $paymentV['text'] = "";
                if ($order['three_pay_amount']>0){
                    $paymentV['text'] .= "余额已支付：".Common::showAmount($order['balance_pay_amount'])."元";
                }
                $paymentV['text'] .= "微信支付：".Common::showAmount($order['three_pay_amount'])."元";
                $paymentV['params'] = $jsApiParameters;
                $payments[$paymentK] = $paymentV;
            }
            else if ($paymentV['type']==Payment::TYPE_BALANCE&&$order['three_pay_amount']==0&&$order['balance_pay_amount']==$order['real_amount']){
                $paymentV['text'] = "全部余额支付：".Common::showAmount($order['balance_pay_amount'])."元";
                $paymentV['params'] = "";
                $payments[$paymentK] = $paymentV;
            }
            else{
                unset($payments[$paymentK]);
            }
        }
    }

    /**
     * 更新预支付id
     * @param $orderNo
     * @param $prepayId
     */
    public static function updatePrepayId($orderNo,$prepayId){
        Order::updateAll(['prepay_id'=>$prepayId],['order_no'=>$orderNo]);
    }

    /**
     * 余额支付确认
     * @param $payment
     * @param $order
     * @throws BusinessException
     * @throws Exception
     */
    public static function payByBalance($payment,$order){
        ExceptionAssert::assertTrue($order->real_amount==$order->balance_pay_amount,StatusCode::createExpWithParams(StatusCode::PAY_BALANCE_ERROR,"余额不足，请选用其他支付方式"));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $order->order_status = OrderService::getPaidOrderStatus($order->order_type);
            $order->pay_id = $payment['id'];
            $order->pay_result = '余额全额支付';
            $order->pay_name = $payment['name'];
            $order->pay_type = $payment['type'];
            $order->pay_status = Order::PAY_STATUS_PAYED_ALL;
            $order->pay_time = DateTimeUtils::parseStandardWLongDate();
            ExceptionAssert::assertTrue($order->save(),StatusCode::createExpWithParams(StatusCode::PAY_BALANCE_ERROR,"订单更新失败"));
            OrderLogService::addPayOrderLog($order);

            // 更新团订单所属房间订单数
            list($success,$error) = GroupOrderService::increaseGroupRoomPaidCount($order);
            if(!$success){
                Yii::error("支付回调更新拼团订单所属团支付订单数错误:".$error);
            }

            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::PAY_BALANCE_ERROR,$e->getMessage()));
        }
    }

    /**
     * 取消订单
     * @param $order
     * @throws BusinessException
     * @throws Exception
     */
    public static function cancelOrder($order){
        $transaction = Yii::$app->db->beginTransaction();
        try{

            // 订单如果是团活动订单 则释放团占位
            list($success,$error) = GroupOrderService::releaseGroupRoomPlace($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //恢复优惠券
            list($success,$error) =  CouponService::recoveryCoupon($order['company_id'],$order['customer_id'],$order['order_no']);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));
            //取消库存
            list($success,$error) =  parent::refreshStock($order);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //增加日志
            list($success,$error) =  OrderLogService::addLogForCustomer($order,OrderLogs::ACTION_CANCEL_ORDER,"用户手动取消订单");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //取消订单
            list($success,$error) =  parent::refreshOrderStatusToCancel($order,"用户手动取消订单");
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //最后退余额+三方支付
            list($success,$error) = CustomerBalanceService::adjustBalance($order,$order['balance_pay_amount'],'整单退款', $order['customer_id'], $order['accept_nickname']);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            //最后三方支付
            $paymentSdk = Yii::$app->frontendWechat->payment;
            list($success,$error) = parent::refundThreePartPay($order,$paymentSdk);
            ExceptionAssert::assertTrue($success,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$error));

            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,$e->getMessage()));
        }
    }



    /**
     * 从购物车删除已下单的商品
     * @param $userId
     * @param $sku_list
     */
    private static function removeFromCart($userId,$sku_list){
        if (!empty($sku_list)){
            $skuIds = ArrayHelper::getColumn($sku_list,'schedule_id');
            CartOperationService::modifyGoods($userId,$skuIds,0);
        }
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
        $order->goods_num += $value['num'];
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
     * 记录预分润数据
     * @param $skuList
     * @param $order
     */
    public static function preDistribute($skuList,$order){
        if ($order['order_owner']==GoodsConstantEnum::OWNER_SELF){
            self::preDistributeSelf($skuList,$order);
        }
        else if ($order['order_owner']==GoodsConstantEnum::OWNER_DELIVERY){
            self::preDistributeDelivery($skuList,$order);
        }
    }

    /**
     * 记录预分润数据 自营订单
     * @param $skuList
     * @param $order Order
     * @return array
     */
    public static function preDistributeSelf($skuList,$order){
        $initCompanyId= Yii::$app->params['option.init.companyId'];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $preDistribute =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount' => 0,
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
                $preDistribute['companyAmount'] += intval($amount * $v['company_rate'] / 10000);
            }
        }

        //支付渠道费
        $preDistribute['paymentHandlingFee'] = ceil($order['real_amount'] * $paymentHandlingFeeRate / 10000);
        //团长的单子配送费给团长
        $preDistribute['deliveryAmount'] += $order['freight_amount'];

        $preDistribute['agentAmount'] =
            $order['real_amount']
            -$preDistribute['paymentHandlingFee']
            -$preDistribute['oneLevelAmount']
            -$preDistribute['twoLevelAmount']
            -$preDistribute['share1Amount']
            -$preDistribute['share2Amount']
            -$preDistribute['deliveryAmount']
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
        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$preDistribute['share2Amount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_POPULARIZER,$order['share_rate_id_2'],OrderPreDistribute::LEVEL_TWO,$preDistribute['share2Amount']);
        }
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_DELIVERY,$order['delivery_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['deliveryAmount']);
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_AGENT,$order['company_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['agentAmount']);
        if ($preDistribute['companyAmount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_COMPANY,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['companyAmount']);
        }
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_PAYMENT_HANDLING_FEE,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['paymentHandlingFee']);
        return $preDistribute;

    }

    /**
     * 记录预分润数据 团长订单
     * @param $skuList
     * @param $order
     * @return array
     */
    public static function preDistributeDelivery($skuList,$order){
        $initCompanyId= Yii::$app->params['option.init.companyId'];
        $paymentHandlingFeeRate= Yii::$app->params['payment.handling.fee.rate'];
        $preDistribute =[
            'oneLevelAmount' => 0,
            'twoLevelAmount' => 0,
            'share1Amount' => 0,
            'share2Amount' => 0,
            'deliveryAmount' => 0,
            'agentAmount' => 0,
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
                $preDistribute['agentAmount'] += intval($amount * $v['agent_rate'] / 10000);
                $preDistribute['companyAmount'] += intval($amount * $v['company_rate'] / 10000);
            }
        }
        //支付渠道费
        $preDistribute['paymentHandlingFee'] = ceil($order['real_amount'] * $paymentHandlingFeeRate / 10000);
        //团长的单子配送费给团长
        $preDistribute['deliveryAmount'] =
            $order['real_amount']
            -$preDistribute['paymentHandlingFee']
            -$preDistribute['oneLevelAmount']
            -$preDistribute['twoLevelAmount']
            -$preDistribute['share1Amount']
            -$preDistribute['share2Amount']
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
        if (NumberUtils::notNullAndPositiveInteger($order['share_rate_id_2'])&&$preDistribute['share2Amount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_POPULARIZER,$order['share_rate_id_2'],OrderPreDistribute::LEVEL_TWO,$preDistribute['share2Amount']);
        }
        if ($preDistribute['agentAmount']>0){
            OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_AGENT,$order['company_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['agentAmount']);
        }
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_COMPANY,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['companyAmount']);
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_PAYMENT_HANDLING_FEE,$initCompanyId,OrderPreDistribute::LEVEL_ONE,$preDistribute['paymentHandlingFee']);
        OrderPreDistributeService::create($order,OrderPreDistribute::BIZ_TYPE_DELIVERY,$order['delivery_id'],OrderPreDistribute::LEVEL_ONE,$preDistribute['deliveryAmount']);
        return $preDistribute;
    }

    /**
     * 支付回调
     * @param $data
     * @param $fail
     * @return bool
     * @throws Exception
     */
    public static function payCallBack($data,&$fail){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orderNo = $data['attach'];
            //判断是否已经处理过
            $exOrderPayCnt = OrderPay::find()->where(['transaction_id'=>$data['transaction_id']])->asArray()->count();
            if ($exOrderPayCnt>0){
                $transaction->rollBack();
                return true;
            }
            $orderQueryResult = Yii::$app->frontendWechat->payment->order->queryByOutTradeNumber($data['out_trade_no']);
            ExceptionAssert::assertTrue($orderQueryResult['return_code'] === 'SUCCESS',StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,'通信失败，请稍后再通知我'));
            if ($orderQueryResult['result_code'] !== 'SUCCESS'){
                $transaction->rollBack();
                return true;
            }

            $order = Order::findOne(['order_no' =>$orderNo,'order_status'=>[Order::ORDER_STATUS_UN_PAY]]);
            ExceptionAssert::assertNotNull($order,StatusCode::createExp(StatusCode::ORDER_PAY_ERROR));
            //ExceptionAssert::assertTrue($order->three_pay_amount==$data["total_fee"]*10,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,'需支付金额与实际金额不一致'));
            $orderPayModel = new OrderPay();
            $orderPayModel->company_id = !empty($order)?$order->company_id:OrderPay::$UN_KNOWN_COMPANY;
            $orderPayModel->order_no = $orderNo;
            $orderPayModel->out_trade_no = $data["out_trade_no"];
            $orderPayModel->transaction_id = $data["transaction_id"];
            $orderPayModel->attach = $data["attach"];
            $orderPayModel->total_fee = $data["total_fee"];
            $orderPayModel->settlement_total_fee = ArrayUtils::getArrayValue('settlement_total_fee',$data,'');
            $orderPayModel->bank_type = ArrayUtils::getArrayValue($data["bank_type"], Yii::$app->params['bank_type'],$data["bank_type"]);
            $orderPayModel->openid = $data["openid"];
            $orderPayModel->nonce_str = $data["nonce_str"];
            $orderPayModel->time_end = $data["time_end"];
            $orderPayModel->sign = $data["sign"];
            $orderPayModel->trade_type = $data["trade_type"];
            ExceptionAssert::assertTrue($orderPayModel->save(),StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,'回调数据保存失败'));

            $wxPayment = PaymentService::getWxPayment();
            $order->order_status = self::getPaidOrderStatus($order->order_type);
            $order->pay_status = Order::PAY_STATUS_PAYED_ALL;
            $order->pay_time = DateTimeUtils::parseStandardWLongDate(time());
            $order->pay_id = $wxPayment['id'];
            $order->pay_name = $wxPayment['name'];
            $order->pay_result = $data['result_code'];
            $order->pay_type = Payment::TYPE_WECHAT;
            $order->pay_amount = $data["total_fee"]*10;
            ExceptionAssert::assertTrue($order->save(),StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,'订单状态更新失败'));
            //NoticeService::orderStatusNotice ($orderNo,$data['openid']);
            
            // 更新团订单所属房间订单数
            list($success,$error) = GroupOrderService::increaseGroupRoomPaidCount($order);
            if(!$success){
                Yii::error("支付回调更新拼团订单所属团支付订单数错误:".$error);
            }

            $transaction->commit();
            try {
                PushMessageService::pushCommander($orderNo);
            }
            catch (\Exception $e){
                Yii::error("PushMessageService::pushCommander@error:".$e->getMessage());
            }
            return true;
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            $fail($e->getMessage());
        }
    }

    /**
     * 根据订单类型返回支付后的状态
     * @param $orderType
     * @return int
     */
    private static function getPaidOrderStatus($orderType){
        if ($orderType==GoodsConstantEnum::TYPE_GROUP_ACTIVE){
            return Order::ORDER_STATUS_CHECKING;
        }
        else{
            return Order::ORDER_STATUS_PREPARE;
        }
    }

    public static function requiredOrderModel($orderNo,$customerId){
        $conditions = ['order_no'=>$orderNo,'customer_id'=>$customerId];
        $order = (new Query())->from(Order::tableName())->where($conditions)->one();
        $order = $order===false?null:$order;
        ExceptionAssert::assertNotNull($order, StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $order;
    }


    /**
     * 校验单品起售数量
     * @param $skuList
     */
    public static function startSaleNumCheck($skuList){
        if (empty($skuList)){
            return;
        }
        foreach ($skuList as $v){
            ExceptionAssert::assertTrue($v['num']>=$v['start_sale_num'],StatusCode::createExpWithParams(StatusCode::START_SALE_NUM_CHECK_ERROR,"{$v['goods_name']}{$v['start_sale_num']}份"));
        }
    }

    public static function getOrderWithGoods($orderNo,$customerId){
        $order = self::requiredOrderModel($orderNo,$customerId);
        $orderGoods = self::getOrderGoodsModel($order['order_no']);
        $order['goods'] = $orderGoods;
        $order = OrderDisplayDomainService::defineOrderDisplayData($order);
        $order = OrderDisplayDomainService::setPreDistributeText($order);
        self::completeGroupRoomInfo($order);
        return $order;
    }

    /**
     * @param $order
     */
    public static function completeGroupRoomInfo(&$order){
        if ($order['order_type']!=GoodsConstantEnum::TYPE_GROUP_ACTIVE){
            return;
        }
        $roomOrder = GroupRoomOrderService::getActiveModel($order['order_no']);
        $roomModel = GroupRoomService::getRoomDetail( $roomOrder['room_no'], $roomOrder['company_id']);
        $order['room'] = $roomModel;
    }




    public static function getOrderDetail($orderNo, $customerId, $companyId=null)
    {
        $orderDetail = self::getOrderDetailCommon($orderNo, $customerId, $companyId);
        ExceptionAssert::assertNotNull($orderDetail, StatusCode::createExp(StatusCode::ORDER_NOT_EXIST));
        return $orderDetail;
    }

}