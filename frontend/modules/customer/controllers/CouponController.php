<?php

namespace frontend\modules\customer\controllers;

use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\CouponBatchService;
use frontend\services\CouponService;
use frontend\services\CustomerService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class CouponController extends FController {
	public function actionList() {
	    $companyId = FrontendCommon::requiredFCompanyId();
	    $customerId = FrontendCommon::requiredCustomerId();
 		$data = CouponService::getCustomerCouponList($companyId,$customerId);
		return RestfulResponse::success($data);
	}

	public function actionDraw(){
        $companyId = FrontendCommon::requiredFCompanyId();
        $cModel = FrontendCommon::requiredActiveCustomer();
        $batchNo = Yii::$app->request->get("batch_no" );
        ExceptionAssert::assertNotBlank($batchNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'batch_no不能为空'));
        CouponBatchService::drawPublicCoupon($companyId,$cModel['id'],$batchNo,1,$cModel['id'],$cModel['nickname']);
        return RestfulResponse::success(true);
	}

    public function actionAvailable(){
        $companyId = FrontendCommon::requiredFCompanyId();
        $skuId = Yii::$app->request->get("sku_id");
        ExceptionAssert::assertNotBlank($skuId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'sku_id不能为空'));
        $couponBatchArr = CouponBatchService::getAvailableSkuCouponList($companyId,$skuId);
        return RestfulResponse::success($couponBatchArr);
    }

    public function actionCenter(){
        $configs = CouponBatchService::couponCenterConfig();
        return RestfulResponse::success($configs);
    }

    public function actionAvailableCoupon(){
        $type = Yii::$app->request->get("type");
        if (StringUtils::isNotBlank($type)){
            $type = ExceptionAssert::assertNotBlankAndNotEmpty($type,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'type'));
        }
        $companyId = FrontendCommon::requiredFCompanyId();
        $cModel = FrontendCommon::requiredCustomer();
        $couponBatchArr = CouponBatchService::getAvailableCouponList($companyId,$cModel,$type);
        return RestfulResponse::success($couponBatchArr);
    }

    public function actionNewCouponRemind(){
        if (StringUtils::isBlank($userId = FrontendCommon::getUserId())
            ||StringUtils::isBlank(($deliveryId =  FrontendCommon::getDeliveryId()))
            ||StringUtils::isBlank(($companyId =  FrontendCommon::requiredFCompanyId()))
            ||StringUtils::isBlank(($customerId =  FrontendCommon::getCustomerId()))){
            return RestfulResponse::success([]);
        }
        $data = CouponBatchService::getUnRemindCouponAndSetReminded($customerId,$companyId);
        return RestfulResponse::success($data);
    }

    public function actionTestNewCoupon(){
        $data = CouponBatchService::automaticDrawCoupon();
        return RestfulResponse::success($data);
    }

    public function actionUnUsed(){
        if (StringUtils::isBlank($userId = FrontendCommon::getUserId())
            ||StringUtils::isBlank(($deliveryId =  FrontendCommon::getDeliveryId()))
            ||StringUtils::isBlank(($companyId =  FrontendCommon::requiredFCompanyId()))
            ||StringUtils::isBlank(($customerId =  FrontendCommon::getCustomerId()))){
            return RestfulResponse::success([]);
        }
        $couponType = Yii::$app->request->get("couponType");
        $data = CouponBatchService::getUnUsedCoupon($customerId,$companyId,$couponType);
        return RestfulResponse::success($data);
    }

}
