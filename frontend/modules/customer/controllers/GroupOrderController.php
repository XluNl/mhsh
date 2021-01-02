<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\CouponService;
use frontend\services\DeliveryService;
use frontend\services\GroupOrderService;
use frontend\services\IndexService;
use frontend\services\OrderService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class GroupOrderController extends FController {

    public function actionConfirm() {
        $activeNo = Yii::$app->request->get("activeNo");
        $roomNo = Yii::$app->request->get("roomNo");
        ExceptionAssert::assertNotBlank($activeNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'activeNo'));
        $groupOrderType = Yii::$app->request->get("groupOrderType");
        ExceptionAssert::assertNotBlank($groupOrderType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'groupOrderType'));
        $num = Yii::$app->request->get("num",1);

        $companyId = FrontendCommon::requiredFCompanyId();
        GroupOrderService::checkGroupOrderType($groupOrderType);

        //如果是加入团，则判断团是否还允许下单
        GroupOrderService::checkRoomsStatus($groupOrderType,$roomNo,$companyId);

        //获取用户信息
        $cModel = FrontendCommon::requiredActiveCustomer();

        //计算价格、节省金额，统计特价商品
        list($price_total,$goods_total,$skuList) = GroupOrderService::calculatePrice($companyId,$activeNo,$num);


        //组装显示数据
        $skuList = IndexService::assembleStatusAndImageAndExceptTime($skuList);
        //校验sku是否为空
        GroupOrderService::checkSkuListNotEmpty($skuList);

        //拼团不允许使用优惠券
        $coupons = [];
        //$coupons = CouponService::getAvailableCoupon($companyId,$cModel->id,$skuList);

        //配送方案
        $freights = DeliveryService::getAvailableFreight($price_total,0);

        $data = ['goods_total' => $goods_total, 'skuList' => array_values($skuList), 'price_total' => $price_total,'coupons'=>array_values($coupons),'freights'=>$freights];
        return RestfulResponse::success($data);
    }



	public function actionCreate() {
        $activeNo = Yii::$app->request->get("activeNo");
        $roomNo = Yii::$app->request->get("roomNo");
        ExceptionAssert::assertNotBlank($activeNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'activeNo'));
        $groupOrderType = Yii::$app->request->get("groupOrderType");
        ExceptionAssert::assertNotBlank($groupOrderType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'groupOrderType'));
        $num = Yii::$app->request->get("num",1);

        $companyId = FrontendCommon::requiredFCompanyId();
        GroupOrderService::checkGroupOrderType($groupOrderType);

        //如果是加入团，则判断团是否还允许下单
        GroupOrderService::checkRoomsStatus($groupOrderType,$roomNo,$companyId);

        //获取用户信息
        $cModel = FrontendCommon::requiredActiveCustomer();

        //获取用户id
        $userId = FrontendCommon::requiredUserId();

        //计算价格、节省金额，统计特价商品
        list($price_total,$goods_total,$skuList) = GroupOrderService::calculatePrice($companyId,$activeNo,$num);

        //校验sku是否为空
        OrderService::checkSkuList($skuList);
		
        //拼团不允许使用优惠券
        //$couponNo = Yii::$app->request->get('couponNo');
        //OrderService::checkCoupon($companyId,$couponNo,$cModel->id,$skuList);
        $couponNo = null;

        //创建订单
        $deliveryType = Yii::$app->request->get('deliveryType');
        $addressId = Yii::$app->request->get('addressId');
        $orderNote = Yii::$app->request->get('orderNote');
        ExceptionAssert::assertNotBlank($deliveryType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'deliveryType'));
        ExceptionAssert::assertTrue(array_key_exists($deliveryType,GoodsConstantEnum::$deliveryTypeArr),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'deliveryType'));
        ExceptionAssert::assertNotBlank($addressId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'addressId'));

        $res = GroupOrderService::createOrder(
            $activeNo,
            $roomNo,
            $groupOrderType,
            $companyId,
            $userId,
            $cModel,
            $skuList,
            $couponNo,
            $deliveryType,
            $addressId,
            $orderNote,
        );
        return RestfulResponse::success($res);
    }

    public function actionCloseRoom(){
        $roomNo = Yii::$app->request->get("roomNo");
        ExceptionAssert::assertNotBlank($roomNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'roomNo'));
        $companyId = FrontendCommon::requiredFCompanyId();
        $customerModel = FrontendCommon::requiredCustomer();
        GroupOrderService::closeRoom($roomNo,$companyId,$customerModel['id'],$customerModel['nickname']);
        return RestfulResponse::success(true);
    }


}