<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\Payment;
use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\CouponService;
use frontend\services\DeliveryService;
use frontend\services\GoodsDisplayDomainService;
use frontend\services\IndexService;
use frontend\services\OrderService;
use frontend\services\PaymentService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\helpers\Json;

class OrderController extends FController {

	public $enableCsrfValidation = false;

	public function actionList() {
	    $uid = FrontendCommon::requiredUserId();
		$pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $keyword = Yii::$app->request->get("search_keyword",null);
        $ownerType = Yii::$app->request->get("type", GoodsConstantEnum::OWNER_SELF.','.GoodsConstantEnum::OWNER_DELIVERY);
        if (StringUtils::isNotBlank($ownerType)&&StringUtils::containsSubString($ownerType,',')){
            $ownerType =  $arr = explode(",", $ownerType);
        }
        $orderType = Yii::$app->request->get("order_type");
        if (StringUtils::isNotBlank($orderType)){
            $orderType = ExceptionAssert::assertNotBlankAndNotEmpty($orderType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'order_type'));
        }
        $filter = Yii::$app->request->get("filter");
        //校验下单模式
        OrderService::checkOwnerType($ownerType);
		$customerId = FrontendCommon::requiredCustomerId();
        $orders = OrderService::getPageFilterOrder($customerId,$filter,$ownerType,$orderType,$keyword,$pageNo,$pageSize);
        return RestfulResponse::success($orders);
	}

	public function actionCart() {
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        //校验下单模式
        OrderService::checkOwnerType($ownerType);
		$userId = FrontendCommon::requiredUserId();
        $companyId = FrontendCommon::requiredFCompanyId();
        $deliveryId = FrontendCommon::requiredDeliveryId();

        $cModel = FrontendCommon::requiredCustomer();

		//检查代收点是否允许下单
        OrderService::checkStorageAllowOrder($cModel);
        //获取购物车信息
		$cart = OrderService::getCartGoods($cModel,$companyId,$userId,$deliveryId,$ownerType);
		//$mark = OrderService::getRemarkPara($cModel);
        return RestfulResponse::success($cart);
	}

	public function actionConfirm() {
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        //校验下单模式
        OrderService::checkOwnerType($ownerType);
	    $companyId = FrontendCommon::requiredFCompanyId();
        $userId = FrontendCommon::requiredUserId();
		//检查营业时间
        OrderService::checkOpeningHours();

        //校验空购物车
        OrderService::checkEmptyCart($userId);
		$cModel = FrontendCommon::requiredActiveCustomer();
	    //检查仓库是否允许下单
        OrderService::checkStorageAllowOrder($cModel);

		//计算价格、节省金额，统计特价商品
		list($price_total,$goods_total,$skuList) = OrderService::calculatePrice($userId,$cModel,$ownerType);
        //组装显示数据
        $skuList = IndexService::assembleStatusAndImageAndExceptTime($skuList);
		//校验sku是否为空
		OrderService::checkSkuList($skuList);
		//校验单品起售数量
		OrderService::startSaleNumCheck($skuList);
        //确认特价菜是否超过购买限制
        OrderService::checkBargainGoodCount($userId,$cModel,$skuList);

		//获取购物车商品备注
        //OrderService::getMarks($cModel,$skuList);

        //确认起送价格
        OrderService::checkDeliveryAmount($cModel,$price_total);

		//获得各品类的优惠券
		$coupons = CouponService::getAvailableCoupon($companyId,$cModel->id,$skuList);

		//配送方案
		$freights = DeliveryService::getAvailableFreight($price_total,0);

		$data = ['goods_total' => $goods_total, 'skuList' => array_values($skuList), 'price_total' => $price_total,'freights'=>$freights,'coupons'=>array_values($coupons)];
		return RestfulResponse::success($data);
	}

	public function actionCreate() {
        $ownerType = Yii::$app->request->get("owner_type", GoodsConstantEnum::OWNER_SELF);
        //校验下单模式
        OrderService::checkOwnerType($ownerType);
	    $companyId = FrontendCommon::requiredFCompanyId();
        $userId = FrontendCommon::requiredUserId();
		//检查营业时间
        OrderService::checkOpeningHours();
		//校验空购物车
		OrderService::checkEmptyCart($userId);

        $cModel =  FrontendCommon::requiredActiveCustomer();

        //检查代收点是否允许下单
        OrderService::checkStorageAllowOrder($cModel);

		//计算价格、节省金额，统计特价商品
        list($price_total,$goods_total,$skuList) = OrderService::calculatePrice($userId,$cModel,$ownerType);

        //将商品图片复制到sku图片
        $skuList = GoodsDisplayDomainService::batchReplaceIfNotExist($skuList,'sku_img','goods_img');

        //校验sku是否为空
        OrderService::checkSkuList($skuList);

        //校验单品起售数量
        OrderService::startSaleNumCheck($skuList);

		//确认特价菜是否超过购买限制
        OrderService::checkBargainGoodCount($userId,$cModel,$skuList);

        //确认起送价格
        OrderService::checkDeliveryAmount($cModel,$price_total);
		
		//获取购物车商品备注
        //OrderService::getMarks($cModel,$skuList);


		//校验coupon有效性
        $coupon_no = Yii::$app->request->get('coupon_no');
        OrderService::checkCoupon($companyId,$coupon_no,$cModel->id,$skuList);


        //创建订单
        $deliveryType = Yii::$app->request->get('delivery_type');
        $addressId = Yii::$app->request->get('address_id');
        $orderNote = Yii::$app->request->get('order_note');
        ExceptionAssert::assertNotBlank($deliveryType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'deliveryType'));
        ExceptionAssert::assertTrue(array_key_exists($deliveryType,GoodsConstantEnum::$deliveryTypeArr),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'deliveryType'));
        ExceptionAssert::assertNotBlank($addressId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'addressId'));

        $order_no = OrderService::createOrderTransaction($companyId,$userId,$cModel,$skuList,$coupon_no,$deliveryType,$addressId,$orderNote,$ownerType,GoodsConstantEnum::TYPE_OBJECT);

        return RestfulResponse::success($order_no);

	}


    public function actionPayConfirm() {
        $companyId = FrontendCommon::requiredFCompanyId();
        $order_no = Yii::$app->request->get("order_no");
        $order = OrderService::validateOrderModel($order_no,$companyId);
        ExceptionAssert::assertTrue($order['order_status']==Order::ORDER_STATUS_UN_PAY,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,"订单已支付，请勿重复支付"));
        $payments = PaymentService::getAvailable();
        ExceptionAssert::assertNotEmpty($payments,StatusCode::createExp(StatusCode::PAYMENT_NOT_EXIST));
        OrderService::displayPayments($payments,$order,FrontendCommon::requiredUserModel()['openid']);
        $data = ["model" => $order->attributes, "payments" => array_values($payments)];
        return RestfulResponse::success($data);
    }

    public function actionPay() {
        $companyId = FrontendCommon::requiredFCompanyId();
        $order_no = Yii::$app->request->get("order_no");
        $order = OrderService::validateOrderModel($order_no,$companyId);
        ExceptionAssert::assertTrue($order['order_status']==Order::ORDER_STATUS_UN_PAY,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,"订单不存在"));
        $payments = PaymentService::getAvailable();
        ExceptionAssert::assertNotEmpty($payments,StatusCode::createExp(StatusCode::PAYMENT_NOT_EXIST));
        $payId = Yii::$app->request->get("pay_id");
        ExceptionAssert::assertNotBlank($payId,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,"支付方式为空"));
        $payment = PaymentService::getById($payId);
        ExceptionAssert::assertNotEmpty($payment,StatusCode::createExp(StatusCode::PAYMENT_NOT_EXIST));
        if ($payment['type'] == Payment::TYPE_BALANCE){
            OrderService::payByBalance($payment,$order);
        }
        else if ($payment['type'] == Payment::TYPE_WECHAT){
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,"微信支付会在回调之后更新订单状态,请刷新查询"));
        }
        else{
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ORDER_PAY_ERROR,"支付方式不存在"));
        }
        return RestfulResponse::success("支付成功");
    }

    public function actionCancelOrder(){
        $companyId = FrontendCommon::requiredFCompanyId();
        $order_no = Yii::$app->request->get("order_no");
        $order = OrderService::validateOrderArray($order_no,$companyId);
        ExceptionAssert::assertTrue($order['order_status']!=Order::ORDER_STATUS_CANCELED,StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"订单已取消"));
        ExceptionAssert::assertTrue(in_array($order['order_status'],Order::$allowCancelStatusArr),StatusCode::createExpWithParams(StatusCode::ORDER_CANCEL_ERROR,"订单只有在未发货之前才允许取消"));
        OrderService::cancelOrder($order);
        return RestfulResponse::success("订单取消成功");
    }

    public function actionCompleteOrder(){
        $companyId = FrontendCommon::requiredFCompanyId();
        $order_no = Yii::$app->request->get("order_no");
        $order = OrderService::validateOrderArray($order_no,$companyId);
        ExceptionAssert::assertTrue(in_array($order['order_status'],[Order::ORDER_STATUS_RECEIVE]),StatusCode::createExpWithParams(StatusCode::ORDER_COMPLETE_ERROR,"已送达订单才能收货"));
        OrderService::complete($order);
        return RestfulResponse::success("确认收货成功");
    }



	public function actionNotify(){
        Yii::error(Yii::$app->request->getRawBody(),'pay');
        $response = Yii::$app->frontendWechat->payment->handlePaidNotify(function ($message, $fail) {
            return OrderService::payCallBack($message,$fail);
        });
        $response->send();
        exit(0);
    }

    public function actionNotify2()
    {
        $message = Json::decode("{\"appid\":\"wx1791fd2f9118b468\",\"attach\":\"B528582231875329\",\"bank_type\":\"ICBC_DEBIT\",\"cash_fee\":\"1\",\"fee_type\":\"CNY\",\"is_subscribe\":\"N\",\"mch_id\":\"1543153571\",\"nonce_str\":\"5ced58007d4dc\",\"openid\":\"oJIjm5V0pWu9mWz0x76mhIczSZXQ\",\"out_trade_no\":\"B528582231875329_20190528234712\",\"result_code\":\"SUCCESS\",\"return_code\":\"SUCCESS\",\"sign\":\"5BBBE70AD915BBD38322198EDFCC33C3\",\"time_end\":\"20190528234728\",\"total_fee\":\"1\",\"trade_type\":\"JSAPI\",\"transaction_id\":\"4200000296201905287458648843\"}");
        OrderService::payCallBack($message,$fail);
    }


/*    public function actionDetail(){
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"order_no"));
        $customerId = FrontendCommon::requiredCustomerId();
        $order = OrderService::getOrderWithGoods($orderNo,$customerId);
        return RestfulResponse::success($order);
    }*/


    public function actionDetail(){
        $orderNo = Yii::$app->request->get("order_no");
        ExceptionAssert::assertNotNull($orderNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"order_no"));
        $customerId = FrontendCommon::requiredCustomerId();
        $order = OrderService::getOrderDetail($orderNo,$customerId);
        return RestfulResponse::success($order);
    }
}
