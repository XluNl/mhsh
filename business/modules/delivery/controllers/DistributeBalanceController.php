<?php

namespace business\modules\delivery\controllers;

use business\components\FController;
use business\models\BusinessCommon;
use business\services\DistributeBalanceService;
use business\services\PreDistributeService;
use business\services\WithdrawApplyService;
use business\services\DeliveryService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\configuration\WithdrawTypeConfig;
use common\models\BizTypeEnum;
use common\models\Common;
use common\utils\DateTimeUtils;
use Yii;

class DistributeBalanceController extends FController {

    public function actionPreDetail() {
        $date = Yii::$app->request->get("date");
        ExceptionAssert::assertNotNull($date, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        $data = DistributeBalanceService::preDistributeMonth($bizType,$bizId,$date);
        return RestfulResponse::success($data);
    }

    /**
     * [actionPreSum 预统计分润汇总（总、当月、当日）]
     * @return [type] [description]
     */
    public function actionPreSum() {
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        $allSum = DistributeBalanceService::preDistributeAllSum($bizType,$bizId);
        $monthSum = DistributeBalanceService::preDistributeMonthSum($bizType,$bizId,DateTimeUtils::formatYearAndMonth(time(),false));
        $daySum = DistributeBalanceService::preDistributeDaySum($bizType,$bizId,DateTimeUtils::formatYearAndMonthAndDay(time(),false));
        $res = [
            'all'=>$allSum,
            'month'=>$monthSum,
            'day'=>$daySum,
        ];
        return RestfulResponse::success($res);
    }

    /**
     * [actionPreOrder 账户佣金统计]
     * @return [type] [description]
     */
    public function actionPreOrder() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $bizType = Yii::$app->request->get("biz_type");
        $isOuth = Yii::$app->request->get("is_outh");
        $start_time = Yii::$app->request->get("start_time");
        $end_time = Yii::$app->request->get("end_time",date('Y-m-d'));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        ExceptionAssert::assertNotBlank($end_time,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'结束时间不可缺失'));
        
        $end_time = $end_time.' 23:59:59';
        $limittime = date('Y-m-d',strtotime(date("Y-m-d 23:59:59")." -90 day"));
        ExceptionAssert::assertTrue(strtotime($end_time)>=strtotime($limittime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'时间跨度3个月内-'.$limittime));
        if($start_time){
            ExceptionAssert::assertTrue(strtotime($end_time)>=strtotime($start_time),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'开始时间不能大于结束时间'));
            ExceptionAssert::assertTrue(strtotime($limittime)<=strtotime($start_time),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'时间跨度3个月内-'.$limittime));
        }
        
        $userId = BusinessCommon::requiredUserId();
        $data = PreDistributeService::preDistributeOrder($bizType,$userId,$isOuth,$pageNo,$pageSize,$start_time,$end_time);
        return RestfulResponse::success($data);
    }

    /**
     * [actionPreSaleStatistics 团长销售统计]
     * @return [type] [description]
     */
    public function actionPreSaleStatistics(){
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $bizType = Yii::$app->request->get("biz_type");
        $isOuth = Yii::$app->request->get("is_outh");
        $start_time = Yii::$app->request->get("start_time");
        $end_time = Yii::$app->request->get("end_time",date('Y-m-d'));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        ExceptionAssert::assertNotBlank($end_time,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'结束时间不可缺失'));
        
        $end_time = $end_time.' 23:59:59';
        $limittime = date('Y-m-d',strtotime(date("Y-m-d 23:59:59")." -90 day"));
        ExceptionAssert::assertTrue(strtotime($end_time)>=strtotime($limittime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'时间跨度3个月内-'.$limittime));
        if($start_time){
            ExceptionAssert::assertTrue(strtotime($end_time)>=strtotime($start_time),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'开始时间不能大于结束时间'));
            ExceptionAssert::assertTrue(strtotime($limittime)<=strtotime($start_time),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'时间跨度3个月内-'.$limittime));
        }
        
        $userId = BusinessCommon::requiredUserId();
        $data = PreDistributeService::preSaleStatistics($bizType,$userId,$isOuth,$pageNo,$pageSize,$start_time,$end_time);
        return RestfulResponse::success($data);
     }

    public function actionDistributeDetail(){
        $date = Yii::$app->request->get("date");
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getDistributeDetail($bizType,$bizId,$date);
        return RestfulResponse::success($data);
    }

    public function actionDistributeOrder(){
        $distributeItemId = Yii::$app->request->get("id");
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($distributeItemId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getDistributeOrder($bizType,$bizId,$distributeItemId);
        return RestfulResponse::success($data);
    }

    public function actionDistributeStatistic(){
        $bizType = Yii::$app->request->get("biz_type");
        $date = Yii::$app->request->get("date");
        $dateType = Yii::$app->request->get("date_type");
        $beforeNum = Yii::$app->request->get("before_num");
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        ExceptionAssert::assertNotBlank($dateType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::calcDistributeStatistics($date,$dateType,$bizType,$bizId,$beforeNum);
        return RestfulResponse::success($data);
    }

    /**
     * [actionBalance 账户余额(余额、提现中的金额、提现方式)]
     * @return [type] [description]
     */
    public function actionBalance(){
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
       // DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getModelByBiz($bizId,$bizType,$userId);
        $withdrawingAmount = WithdrawApplyService::getWithdrawingAmount($bizId,$bizType);
        return RestfulResponse::success([
            "balance"=>$data,
            "withdrawingAmount"=>$withdrawingAmount,
            'withdraw_type'=>WithdrawTypeConfig::getDefaultConfig()
        ]);
    }

    /**
     * [actionBalanceDetail 账户余额流水（按月查询提现流水）]
     * @return [type] [description]
     */
    public function actionBalanceDetail(){
        $date = Yii::$app->request->get("date");
        $bizType = Yii::$app->request->get("biz_type");
        ExceptionAssert::assertNotBlank($date,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'date'));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        $userId = BusinessCommon::requiredUserId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data  = DistributeBalanceService::getBalanceDetail($bizType,$bizId,$date);
        return RestfulResponse::success($data);
    }

    /**
     * [actionBalanceWithdraw 申请提现]
     * @return [type] [description]
     */
    public function actionBalanceWithdraw(){
        $bizType = Yii::$app->request->get("biz_type");
        $type = Yii::$app->request->get("type");
        $remark = Yii::$app->request->get("remark","");
        $amount = Yii::$app->request->get("amount",0);
        $amount = intval(Common::setAmount($amount));
        ExceptionAssert::assertNotBlank($bizType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'biz_type'));
        ExceptionAssert::assertTrue($amount>0,StatusCode::createExp(StatusCode::AMOUNT_MUST_POSITIVE));
        $userId = BusinessCommon::requiredUserId();
        $userName = BusinessCommon::requiredUserName();
        $openId = BusinessCommon::requiredOpenId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        WithdrawApplyService::createDistributeBalanceWithdrawApplyB($bizId,$bizType,$amount,$type,$userId,$userName,$openId,$remark);
        return RestfulResponse::success(true);
    }


    public function actionChargeConfirm(){
        $bizType = BizTypeEnum::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY;
        $amount = Yii::$app->request->get("amount",0);
        $amount = intval(Common::setAmount($amount));
        ExceptionAssert::assertTrue($amount>0,StatusCode::createExp(StatusCode::AMOUNT_MUST_POSITIVE));
        $userId = BusinessCommon::requiredUserId();
        $userName = BusinessCommon::requiredUserName();
        $openId = BusinessCommon::requiredOpenId();
        $bizId = DistributeBalanceService::getDefaultIdByBizType($bizType,$userId);
        ExceptionAssert::assertNotBlank($bizId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'没有设置默认的bizId'));
        //DistributeBalanceService::checkPermission($bizId,$bizType,$userId);
        $data = DistributeBalanceService::chargeConfirm($openId,$bizType,$bizId,$amount);
        return RestfulResponse::success($data);
    }

    public function actionChargeNotify(){
        Yii::error(Yii::$app->request->getRawBody(),'charge');
        $response = Yii::$app->businessWechat->payment->handlePaidNotify(function ($message, $fail) {
            return DistributeBalanceService::chargeCallBack($message,$fail);
        });
        $response->send();
        exit(0);
    }

}
