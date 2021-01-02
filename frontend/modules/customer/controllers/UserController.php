<?php

namespace frontend\modules\customer\controllers;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\UserInfo;
use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\CustomerInvitationService;
use frontend\services\CustomerService;
use frontend\services\DeliveryService;
use frontend\services\OrderService;
use frontend\services\RegionService;
use frontend\services\UserInfoService;
use frontend\services\UserService;
use frontend\utils\RestfulResponse;
use Yii;

class UserController extends FController {

	public function actionWaitOrder() {
        $orderType = Yii::$app->request->get("order_type",GoodsConstantEnum::OWNER_SELF);
        $customerId = FrontendCommon::requiredCustomerId();
        $orders = OrderService::getWaitGetOrder($customerId,$orderType);
        return RestfulResponse::success($orders);
    }

    public function actionInfo()
    {
        $uid = FrontendCommon::requiredUserId();
        $userInfo = UserInfoService::requiredUserInfo($uid);
        RegionService::setProvinceAndCityAndCounty($userInfo);
        return RestfulResponse::success($userInfo);
    }

    public function actionPopularizer()
    {
        $userId = FrontendCommon::requiredUserId();
        $companyId = FrontendCommon::requiredFCompanyId();
        $popularizer = UserService::getPopularizer($userId,$companyId);
        RegionService::setProvinceAndCityAndCounty($popularizer);
        return RestfulResponse::success($popularizer);
    }

    public function actionDelivery()
    {
        $userId = FrontendCommon::requiredUserId();
        $deliveries = UserService::getDeliveries($userId);
        RegionService::batchSetProvinceAndCityAndCounty($deliveries);
        return RestfulResponse::success($deliveries);
    }

    public function actionInvitation()
    {
        $customerId = FrontendCommon::requiredCustomerId();
        $parentCustomer = CustomerInvitationService::getParentCustomerInfoB($customerId);
        return RestfulResponse::success($parentCustomer);
    }


    public function actionRelativePhone(){
	    $res = [];
        $customer = FrontendCommon::requiredActiveCustomer();
        $res['customer'] = [
            'phone'=>$customer['phone'],
            'nickname'=>$customer['nickname'],
        ];
        list($res['customer']['sex'],$res['customer']['headimgurl']) = UserInfoService::getUserSexAndHead($customer['user_id']);
        $deliveryId = FrontendCommon::getDeliveryId();
        if (StringUtils::isNotBlank($deliveryId)){
            $delivery = DeliveryService::getModel($deliveryId);
            if (!empty($delivery)){
                $res['delivery'] = [
                    'phone'=>$delivery['phone'],
                    'nickname'=>$delivery['nickname'],
                ];
                list($res['delivery']['sex'],$res['delivery']['headimgurl']) = UserInfoService::getUserSexAndHead($customer['user_id']);
            }
        }
        $parentCustomer = CustomerInvitationService::getParentCustomerInfo($customer['id']);
        if (!empty($parentCustomer)){
            $res['invitation'] = [
                'phone'=>$parentCustomer['phone'],
                'nickname'=>$parentCustomer['nickname'],
            ];
            list($res['invitation']['sex'],$res['invitation']['headimgurl']) = UserInfoService::getUserSexAndHead($customer['user_id']);
        }
        return RestfulResponse::success($res);
    }
}