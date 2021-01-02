<?php

namespace frontend\modules\customer\controllers;
use common\configuration\WithdrawTypeConfig;
use common\models\BizTypeEnum;
use common\models\Common;
use common\utils\DateTimeUtils;
use frontend\models\FrontendCommon;
use frontend\services\CustomerInvitationLevelService;
use frontend\services\DistributeBalanceService;
use frontend\services\WithdrawApplyService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class DistributeBalanceController extends Controller {


    public function actionPreDetail() {
        $date = Yii::$app->request->get("date");
        $targetCustomerId = Yii::$app->request->get("target_customer_id");
        ExceptionAssert::assertNotNull($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $customerId = FrontendCommon::requiredCustomerId();
        $data = DistributeBalanceService::preDistributeMonthF($customerId,$date,$targetCustomerId);
        return RestfulResponse::success($data);
    }


    public function actionPreSum() {
        $customerId = FrontendCommon::requiredCustomerId();
        $allSum = DistributeBalanceService::preDistributeAllSumF($customerId);
        $monthSum = DistributeBalanceService::preDistributeMonthSumF($customerId,DateTimeUtils::formatYearAndMonth(time(),false));
        $daySum = DistributeBalanceService::preDistributeDaySumF($customerId,DateTimeUtils::formatYearAndMonthAndDay(time(),false));
        $invitationCount = DistributeBalanceService::getInvitationPeopleCount($customerId);
        $userInfo = CustomerInvitationLevelService::getModelWithUserInfo($customerId);
        $res = [
            'user_info'=>$userInfo,
            'all'=>$allSum,
            'month'=>$monthSum,
            'day'=>$daySum,
            'invitation_count'=>$invitationCount
        ];
        return RestfulResponse::success($res);
    }


    public function actionBalance(){
        $customerId = FrontendCommon::requiredCustomerId();
        $userId = FrontendCommon::requiredUserId();
        $data  = DistributeBalanceService::getCustomerDistributeBalance($customerId,$userId);
        return RestfulResponse::success(["balance"=>$data,'withdraw_type'=>WithdrawTypeConfig::getDefaultConfig()]);
    }

    public function actionBalanceDetail(){
        $date = Yii::$app->request->get("date");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $customerId = FrontendCommon::requiredCustomerId();
        $data  = DistributeBalanceService::getBalanceDetail(BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,$customerId,$date);
        return RestfulResponse::success($data);
    }

    public function actionBalanceWithdraw(){
        $type = Yii::$app->request->get("type");
        $amount = Yii::$app->request->get("amount",0);
        $remark = Yii::$app->request->get("remark","");
        $amount = intval(Common::setAmount($amount));
        ExceptionAssert::assertTrue($amount>0,StatusCode::createExp(StatusCode::AMOUNT_MUST_POSITIVE));
        $userId = FrontendCommon::requiredUserId();
        $openId = FrontendCommon::requiredUserModel()['openid'];
        $customer = FrontendCommon::requiredActiveCustomer();
        $customerId = $customer['id'];
        $userName = $customer['nickname'];
        WithdrawApplyService::createDistributeBalanceWithdrawApplyF($customerId,BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE,$amount,$type,$userId,$userName,$openId,$remark);
        return RestfulResponse::success(true);
    }

}