<?php

namespace alliance\modules\alliance\controllers;

use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\DistributeBalanceService;
use alliance\services\WithdrawApplyService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
use common\configuration\WithdrawTypeConfig;
use common\models\BizTypeEnum;
use common\models\Common;
use common\utils\DateTimeUtils;
use Yii;

class DistributeBalanceController extends FController {



    public function actionPreDetail() {
        $date = Yii::$app->request->get("date");
        ExceptionAssert::assertNotNull($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType(BizTypeEnum::BIZ_TYPE_HA,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        $data = DistributeBalanceService::preDistributeMonthF($bizId,$date);
        return RestfulResponse::success($data);
    }


    public function actionPreSum() {
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType(BizTypeEnum::BIZ_TYPE_HA,$userId);
        $allSum = DistributeBalanceService::preDistributeAllSumF($bizId);
        $monthSum = DistributeBalanceService::preDistributeMonthSumF($bizId,DateTimeUtils::formatYearAndMonth(time(),false));
        $daySum = DistributeBalanceService::preDistributeDaySumF($bizId,DateTimeUtils::formatYearAndMonthAndDay(time(),false));
        $res = [
            'all'=>$allSum,
            'month'=>$monthSum,
            'day'=>$daySum,
        ];
        return RestfulResponse::success($res);
    }





    public function actionDistributeDetail(){
        $date = Yii::$app->request->get("date");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getDistributeDetail($bizType,$bizId,$date);
        return RestfulResponse::success($data);
    }

    public function actionDistributeOrder(){
        $distributeItemId = Yii::$app->request->get("id");
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        ExceptionAssert::assertNotBlank($distributeItemId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getDistributeOrder($bizType,$bizId,$distributeItemId);
        return RestfulResponse::success($data);
    }

    public function actionDistributeStatistic(){
        $date = Yii::$app->request->get("date");
        $dateType = Yii::$app->request->get("date_type");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        ExceptionAssert::assertNotBlank($dateType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date_type'));
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::calcDistributeStatistics($date,$dateType,$bizType,$bizId);
        return RestfulResponse::success($data);
    }

    public function actionBalance(){
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
       // DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getModelByBiz($bizId,$bizType,$userId);
        $data = DistributeBalanceService::createEmptyIfNull($data);
        $withdrawingAmount = WithdrawApplyService::getWithdrawingAmount($bizId,$bizType);
        return RestfulResponse::success([
            "balance"=>$data,
            "withdrawingAmount"=>$withdrawingAmount,
            'withdraw_type'=>WithdrawTypeConfig::getDefaultConfig()
        ]);
    }

    public function actionBalanceDetail(){
        $date = Yii::$app->request->get("date");
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $userId = AllianceCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getBalanceDetail($bizType,$bizId,$date);
        return RestfulResponse::success($data);
    }

    public function actionBalanceWithdraw(){
        $bizType = BizTypeEnum::BIZ_TYPE_HA;
        $type = Yii::$app->request->get("type");
        $remark = Yii::$app->request->get("remark","");
        $amount = Yii::$app->request->get("amount",0);
        $amount = intval(Common::setAmount($amount));
        ExceptionAssert::assertTrue($amount>0,StatusCode::createExp(StatusCode::AMOUNT_MUST_POSITIVE));
        $userId = AllianceCommon::requiredUserId();
        $userName = AllianceCommon::requiredUserName();
        $openId = AllianceCommon::requiredOpenId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        WithdrawApplyService::createDistributeBalanceWithdrawApplyA($bizId,$bizType,$amount,$type,$userId,$userName,$openId,$remark);
        return RestfulResponse::success(true);
    }



}
