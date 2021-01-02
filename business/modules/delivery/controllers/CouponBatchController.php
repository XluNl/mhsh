<?php


namespace business\modules\delivery\controllers;

use business\components\FController;
use business\models\BusinessCommon;
use business\services\CouponBatchService;
use business\services\CouponService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use Yii;

class CouponBatchController extends FController
{

    public function actionList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $isPublic = Yii::$app->request->get("is_public", null);
        $status = Yii::$app->request->get("status", null);
        $delivery = BusinessCommon::requiredDelivery();
        $batches = CouponBatchService::getPageFilterList($delivery['company_id'],$delivery['id'],$isPublic,$status,$pageNo,$pageSize);
        return RestfulResponse::success($batches);
    }

    public function actionModify(){
        $delivery = BusinessCommon::requiredDelivery();
        $modelId = BusinessCommon::getModelValueFromFormData('CouponBatch');
        if (!StringUtils::isBlank($modelId)){
            $model = CouponBatchService::getInfo($modelId,$delivery['id'],$delivery['company_id'],true);
            $model->restoreForm();
            $model->version = null;
            $model->draw_amount = null;
        }
        else{
            $model = new CouponBatch();
            $model->status = CouponBatch::STATUS_ACTIVE;
            $model->draw_amount = 0;
            $model->version = 0;
            $model->type = Coupon::TYPE_CASH_BACK;
        }
        $load = $model->load(\Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_MODIFY_ERROR,"数据格式错误"));
        ExceptionAssert::assertTrue(in_array($model->use_limit_type,[Coupon::LIMIT_TYPE_OWNER,Coupon::LIMIT_TYPE_SORT,Coupon::LIMIT_TYPE_GOODS_SKU]),StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_MODIFY_ERROR,"不支持的使用类型"));
        if ($model->use_limit_type==Coupon::LIMIT_TYPE_OWNER){
            $model->use_limit_type_params = (string)GoodsConstantEnum::OWNER_DELIVERY;
        }
        $model->company_id = $delivery['company_id'];
        $model->operator_id = $delivery['id'];
        $model->operator_name = $delivery['nickname'];
        $model->owner_type=  GoodsConstantEnum::OWNER_DELIVERY;
        $model->owner_id=  $delivery['id'];
        $model->goods_owner = GoodsConstantEnum::OWNER_DELIVERY;
        $model->storeForm();
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::COUPON_BATCH_MODIFY_ERROR,BusinessCommon::getModelErrors($model)));
        return RestfulResponse::success($model->id);
    }

    public function actionUseLimitOption(){
        $useLimitType = Yii::$app->request->get("use_limit_type");
        ExceptionAssert::assertNotNull($useLimitType,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'use_limit_type'));
        $delivery = BusinessCommon::requiredDelivery();
        $options = CouponBatchService::getUseLimitOption($useLimitType,$delivery['company_id'],$delivery['id']);
        $options = ArrayUtils::mapToArray($options,'id','name');
        return RestfulResponse::success($options);
    }

    public function actionStatus(){
        $id = Yii::$app->request->get("id");
        $status = Yii::$app->request->get("status");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        ExceptionAssert::assertNotNull($status,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'status'));
        $delivery = BusinessCommon::requiredDelivery();
        CouponBatchService::statusOperation($delivery,$id,$status);
        return RestfulResponse::success(true);
    }


    public function actionDrawLog(){
        $batchId = Yii::$app->request->get("batch_id");
        $status = Yii::$app->request->get("status", null);
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        ExceptionAssert::assertNotNull($batchId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'batch_id'));
        $delivery = BusinessCommon::requiredDelivery();
        CouponBatchService::getInfo($batchId,$delivery['id'],$delivery['company_id'],false);
        $data = CouponService::getPageFilterList($batchId,$delivery['company_id'],$delivery['id'],$status,$pageNo,$pageSize);
        return RestfulResponse::success($data);
    }

    public function actionDraw(){
        $customerId = Yii::$app->request->get("customer_id");
        $batchId = Yii::$app->request->get("batch_id");
        $num = Yii::$app->request->get("num");
        $remark = Yii::$app->request->get("remark");
        ExceptionAssert::assertNotNull($batchId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'batch_id'));
        ExceptionAssert::assertNotNull($customerId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'customer_id'));
        ExceptionAssert::assertTrue($num>0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'num>0'));
        ExceptionAssert::assertNotBlank($remark,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'remark'));
        $delivery = BusinessCommon::requiredDelivery();
        $batch = CouponBatchService::getInfo($batchId,$delivery['id'],$delivery['company_id'],false);
        CouponBatchService::manualDrawCoupon($delivery['company_id'],$batch,$customerId,$num,$delivery,$remark);
        return RestfulResponse::success(true);
    }

}