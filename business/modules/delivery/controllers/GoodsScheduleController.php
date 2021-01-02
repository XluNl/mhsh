<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\models\BusinessCommon;
use business\services\GoodsScheduleCollectionService;
use business\services\GoodsScheduleService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\models\GoodsScheduleCollection;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;

class GoodsScheduleController extends FController
{

    public function actionList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $name = Yii::$app->request->get("name", null);
        $startTime = Yii::$app->request->get("start_time", null);
        $endTime = Yii::$app->request->get("end_time", null);
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($startTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"时间格式错误：{$startTime}"));
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatIfNotBlack($endTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"时间格式错误：{$endTime}"));
        $delivery = BusinessCommon::requiredDelivery();
        $list = GoodsScheduleCollectionService::getFilterList($delivery['id'],$delivery['company_id'],$name,$startTime,$endTime,$pageNo,$pageSize);
        return RestfulResponse::success($list);
    }


    public function actionModify(){
        $delivery = BusinessCommon::requiredDelivery();
        $modelId = BusinessCommon::getModelValueFromFormData('GoodsScheduleCollection');
        if (!StringUtils::isBlank($modelId)){
            $model = GoodsScheduleCollectionService::getInfo($modelId,$delivery['id'],$delivery['company_id'],true);
        }
        else{
            $model = new GoodsScheduleCollection();
        }
        $load = $model->load(\Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::GOODS_SCHEDULE_COLLECTION_MODIFY,"数据格式错误"));
        $model->company_id = $delivery['company_id'];
        $model->owner_type = GoodsConstantEnum::OWNER_DELIVERY;
        $model->owner_id = $delivery['id'];
        $model->status = CommonStatus::STATUS_ACTIVE;
        $model->operation_id = $delivery['id'];
        $model->operation_name = $delivery['nickname'];
        $model->storeForm();
        GoodsScheduleCollectionService::modify($model);
        return RestfulResponse::success($model->id);
    }

    public function actionBatchStatus()
    {
        $id = Yii::$app->request->get("id", null);
        $status = Yii::$app->request->get("status", null);
        ExceptionAssert::assertNotNull($status,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'status'));
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $delivery = BusinessCommon::requiredDelivery();
        GoodsScheduleCollectionService::statusOperation($delivery,$id,$status);
        return RestfulResponse::success(true);
    }

    public function actionGoodsList() {
        $collectionId = Yii::$app->request->get("collection_id", null);
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        ExceptionAssert::assertNotNull($collectionId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'collection_id'));
        $delivery = BusinessCommon::requiredDelivery();
        $list = GoodsScheduleService::getFilterList($delivery,$collectionId,$pageNo,$pageSize);
        return RestfulResponse::success($list);
    }


    public function actionBatchGoods()
    {
        $postData = Yii::$app->request->post();
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'goods_type',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods_type缺失'));
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'collection_id',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods_type缺失'));
        ExceptionAssert::assertKeyExistAndNotBlack($postData,'goods',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods缺失'));
        foreach ($postData['goods'] as $value){
            ExceptionAssert::assertKeyExistAndNotBlack($value,'sku_id',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods的sku_id缺失'));
            ExceptionAssert::assertKeyExistAndNotBlack($value,'stock',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods的stock缺失'));
            ExceptionAssert::assertKeyExistAndNotBlack($value,'expect_arrive_time',StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'goods的expect_arrive_time缺失'));
            ExceptionAssert::assertTrue(DateTimeUtils::checkYearAndMonthAndDayFormatIfNotBlack($value['expect_arrive_time']),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,"时间格式错误：{$value['expect_arrive_time']}"));
            ExceptionAssert::assertTrue($value['stock']>=0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'stock>=0'));
        }
        $delivery = BusinessCommon::requiredDelivery();
        GoodsScheduleCollectionService::batchAddGoods($delivery,$postData['collection_id'],$postData['goods'],$postData['goods_type']);
        return RestfulResponse::success(true);
    }

    public function actionStatus()
    {
        $id = Yii::$app->request->get("id", null);
        $status = Yii::$app->request->get("status", null);
        ExceptionAssert::assertNotNull($status,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'status'));
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $delivery = BusinessCommon::requiredDelivery();
        GoodsScheduleService::statusOperation($delivery,$id,$status);
        return RestfulResponse::success(true);
    }


    public function actionStock()
    {
        $id = Yii::$app->request->get("id", null);
        $stock = Yii::$app->request->get("stock", null);
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        ExceptionAssert::assertNotNull($stock,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'stock'));
        $delivery = BusinessCommon::requiredDelivery();
        GoodsScheduleService::setStock($delivery,$id,$stock);
        return RestfulResponse::success(true);
    }
}