<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\AllianceOrderService;
use frontend\services\AllianceService;
use frontend\services\CouponService;
use frontend\services\GoodsDisplayDomainService;
use frontend\services\IndexService;
use frontend\services\OrderService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class AllianceOrderController extends FController {

	public $enableCsrfValidation = false;

	public function actionConfirm() {
        $scheduleId = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotBlank($scheduleId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $num = Yii::$app->request->get("num");
        ExceptionAssert::assertNotBlank($num,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'num'));
        ExceptionAssert::assertTrue($num>0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'num必须大于0'));


        $companyId = FrontendCommon::requiredFCompanyId();
        $userId = FrontendCommon::requiredUserId();
		//检查营业时间
        OrderService::checkOpeningHours();

        //获取用户信息
		$cModel = FrontendCommon::requiredActiveCustomer();

		//校验只有团长才能购买
		AllianceOrderService::checkDeliveryCanBuy($userId,$companyId);

		//计算价格、节省金额，统计特价商品
		list($price_total,$goods_total,$skuList) = AllianceOrderService::calculatePrice($companyId,$scheduleId,$num);
        //组装显示数据
        $skuList = IndexService::assembleStatusAndImageAndExceptTime($skuList);
		//校验sku是否为空
        AllianceOrderService::checkSkuListNotEmpty($skuList);

        //校验单品起售数量
        OrderService::startSaleNumCheck($skuList);

        //确认特价菜是否超过购买限制
        AllianceOrderService::checkBargainGoodCount($companyId,$cModel,$skuList);

        //获取异业联盟点信息
        $alliance = AllianceOrderService::checkAllianceStatus($companyId,$skuList);


		//获得各品类的优惠券
		$coupons = CouponService::getAvailableCoupon($companyId,$cModel->id,$skuList);

		//配送方案
		$freights = AllianceService::getAvailableFreight($alliance);

		$data = ['goods_total' => $goods_total, 'skuList' => array_values($skuList), 'price_total' => $price_total,'freights'=>$freights,'coupons'=>array_values($coupons),'alliance'=>$alliance];
		return RestfulResponse::success($data);
	}

	public function actionCreate() {


        $scheduleId = Yii::$app->request->get("schedule_id");
        ExceptionAssert::assertNotBlank($scheduleId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $num = Yii::$app->request->get("num");
        ExceptionAssert::assertNotBlank($num,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'num'));
        ExceptionAssert::assertTrue($num>0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'num必须大于0'));


        $companyId = FrontendCommon::requiredFCompanyId();
        $userId = FrontendCommon::requiredUserId();
		//检查营业时间
        OrderService::checkOpeningHours();

        //获取用户信息
        $cModel = FrontendCommon::requiredActiveCustomer();

        //校验只有团长才能购买
        AllianceOrderService::checkDeliveryCanBuy($userId,$companyId);

		//计算价格、节省金额，统计特价商品
        list($price_total,$goods_total,$skuList) = AllianceOrderService::calculatePrice($companyId,$scheduleId,$num);

        //将商品图片复制到sku图片
        $skuList = GoodsDisplayDomainService::batchReplaceIfNotExist($skuList,'sku_img','goods_img');

        //校验sku是否为空
        AllianceOrderService::checkSkuListNotEmpty($skuList);

        //校验单品起售数量
        OrderService::startSaleNumCheck($skuList);

		//确认特价菜是否超过购买限制
        AllianceOrderService::checkBargainGoodCount($userId,$cModel,$skuList);

        //获取异业联盟点信息
        $alliance = AllianceOrderService::checkAllianceStatus($companyId,$skuList);


		//校验coupon有效性
        $coupon_no = Yii::$app->request->get('coupon_no');
        OrderService::checkCoupon($companyId,$coupon_no,$cModel->id,$skuList);


        //创建订单
        $deliveryType = Yii::$app->request->get('delivery_type');
        $addressId = Yii::$app->request->get('address_id');
        $orderNote = Yii::$app->request->get('order_note');
        ExceptionAssert::assertNotBlank($deliveryType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'delivery_type'));
        ExceptionAssert::assertTrue(array_key_exists($deliveryType,GoodsConstantEnum::$deliveryTypeAllianceArr),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'delivery_type'));
        ExceptionAssert::assertNotBlank($addressId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'address_id'));

        $order_no = AllianceOrderService::createOrderTransaction(
            $companyId,
            $cModel,
            $skuList,
            $coupon_no,
            $deliveryType,
            $addressId,
            $orderNote,
            $alliance);

        return RestfulResponse::success($order_no);

	}
}
