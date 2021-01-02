<?php

namespace frontend\modules\customer\controllers;
use common\configuration\WithdrawTypeConfig;
use common\models\Common;
use frontend\models\FrontendCommon;
use frontend\services\CustomerBalanceService;
use frontend\services\WithdrawApplyService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class BalanceController extends Controller {


    public function actionAmount() {
        $customerId = FrontendCommon::requiredCustomerId();
        $data = CustomerBalanceService::getAmount($customerId);
        return RestfulResponse::success($data);
    }

    public function actionBalance() {
        $customerId = FrontendCommon::requiredCustomerId();
        $data = CustomerBalanceService::getAmount($customerId);
        return RestfulResponse::success(["balance"=>$data,'withdraw_type'=>WithdrawTypeConfig::getDefaultConfig()]);
    }


    public function actionBalanceDetail(){
        $date = Yii::$app->request->get("date");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $customerId = FrontendCommon::requiredCustomerId();
        $data  = CustomerBalanceService::getBalanceDetail($customerId,$date);
        return RestfulResponse::success($data);
    }

    public function actionItemList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $customerId = FrontendCommon::requiredCustomerId();
        $data = CustomerBalanceService::getItemList($customerId,$pageNo,$pageSize);
        return RestfulResponse::success($data);
    }


    public function actionBalanceWithdraw(){
        $type = Yii::$app->request->get("type");
        $amount = Yii::$app->request->get("amount",0);
        $amount = intval(Common::setAmount($amount));
        ExceptionAssert::assertTrue($amount>0,StatusCode::createExp(StatusCode::AMOUNT_MUST_POSITIVE));
        $userId = FrontendCommon::requiredUserId();
        $openId = FrontendCommon::requiredUserModel()['openid'];
        $customer = FrontendCommon::requiredActiveCustomer();
        $customerId = $customer['id'];
        $userName = $customer['nickname'];
        WithdrawApplyService::createCustomerBalanceWithdrawApplyF($customerId,$amount,$type,$userId,$userName,$openId);
        return RestfulResponse::success(true);
    }

}