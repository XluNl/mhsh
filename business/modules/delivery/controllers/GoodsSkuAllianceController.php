<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\models\BusinessCommon;
use business\services\GoodsSkuAllianceService;
use business\services\TagService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuAlliance;
use common\utils\StringUtils;
use Yii;

class GoodsSkuAllianceController extends FController
{

    public function actionList() {
        $pageNo = Yii::$app->request->get("page_no", 1);
        $pageSize = Yii::$app->request->get("page_size", 20);
        $status = Yii::$app->request->get("status", GoodsSkuAlliance::$showAuditStatusArr);
        $delivery = BusinessCommon::requiredDelivery();
        $skuList = GoodsSkuAllianceService::getGoodsInfoList($delivery['id'],$delivery['company_id'],$status,$pageNo,$pageSize);
        return RestfulResponse::success($skuList);
    }

    public function actionInfo(){
        $delivery = BusinessCommon::requiredDelivery();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $model = GoodsSkuAllianceService::getGoodsInfo($id,$delivery['id'],$delivery['company_id']);
        GoodsSkuAllianceService::completeStock($model);
        return RestfulResponse::success($model);
    }

    public function actionModify(){
        $delivery = BusinessCommon::requiredDelivery();
        $modelId = BusinessCommon::getModelValueFromFormData('GoodsSkuAlliance');
        if (!StringUtils::isBlank($modelId)){
            $model = GoodsSkuAllianceService::getGoodsInfo($modelId,$delivery['id'],$delivery['company_id'],true);
            ExceptionAssert::assertTrue($model->audit_status==GoodsSkuAlliance::AUDIT_STATUS_EDIT,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"商品已不支持编辑"));
            $model->restoreForm();
        }
        else{
            $model = new GoodsSkuAlliance();
        }
        $model->setScenario("delivery");
        $load = $model->load(\Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"数据格式错误"));
        $model->company_id = $delivery['company_id'];
        $model->audit_status = GoodsSkuAlliance::AUDIT_STATUS_EDIT;
        $model->goods_owner_type = GoodsConstantEnum::OWNER_DELIVERY;
        $model->goods_owner_id = $delivery['id'];
        $model->sku_img = $model->goods_img;
        $model->sku_status = GoodsConstantEnum::STATUS_DOWN;
        $model->storeForm();
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,BusinessCommon::getModelErrors($model)));
        return RestfulResponse::success($model->id);
    }


    public function actionAudit(){
        $delivery = BusinessCommon::requiredDelivery();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::submitAudit($id,$delivery['id'],$delivery['company_id']);
        return RestfulResponse::success(true);
    }


    public function actionWithdraw(){
        $delivery = BusinessCommon::requiredDelivery();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::withdraw($id,$delivery['id'],$delivery['company_id']);
        return RestfulResponse::success(true);
    }

    public function actionPublish(){
        $delivery = BusinessCommon::requiredDelivery();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::publish($id,$delivery,$delivery['company_id']);
        return RestfulResponse::success(true);
    }


    public function actionSavePublish(){
        $delivery = BusinessCommon::requiredDelivery();
        $modelId = BusinessCommon::getModelValueFromFormData('GoodsSkuAlliance');
        if (!StringUtils::isBlank($modelId)){
            $model = GoodsSkuAllianceService::getGoodsInfo($modelId,$delivery['id'],$delivery['company_id'],true);
            ExceptionAssert::assertTrue($model->audit_status==GoodsSkuAlliance::AUDIT_STATUS_EDIT,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"商品已不支持编辑"));
            $model->restoreForm();
        }
        else{
            $model = new GoodsSkuAlliance();
        }
        $model->setScenario("delivery");
        $load = $model->load(\Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"数据格式错误"));
        $model->company_id = $delivery['company_id'];
        $model->audit_status = GoodsSkuAlliance::AUDIT_STATUS_ACCEPT;
        $model->goods_owner_type = GoodsConstantEnum::OWNER_DELIVERY;
        $model->goods_owner_id = $delivery['id'];
        $model->goods_type = GoodsConstantEnum::TYPE_OBJECT;
        $model->display_channel = GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_NORMAL;
        $model->sku_img = $model->goods_img;
        $model->sku_status = GoodsConstantEnum::STATUS_UP;
        $model->company_rate = TagService::getPlatformRoyaltyValue($delivery['company_id'],$delivery['id']);
        $model->storeForm();
        GoodsSkuAllianceService::saveAndPublish($model,$delivery,$delivery['company_id']);
        return RestfulResponse::success($model->id);
    }

}