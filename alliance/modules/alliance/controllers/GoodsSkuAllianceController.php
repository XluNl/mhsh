<?php


namespace alliance\modules\alliance\controllers;


use alliance\components\FController;
use alliance\models\AllianceCommon;
use alliance\services\GoodsSkuAllianceService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
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
        $alliance = AllianceCommon::requiredAlliance();
        $skuList = GoodsSkuAllianceService::getGoodsInfoList($alliance['id'],$alliance['company_id'],$status,$pageNo,$pageSize);
        return RestfulResponse::success($skuList);
    }

    public function actionInfo(){
        $alliance = AllianceCommon::requiredAlliance();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        $model = GoodsSkuAllianceService::getGoodsInfo($id,$alliance['id'],$alliance['company_id']);
        GoodsSkuAllianceService::completeStock($model);
        return RestfulResponse::success($model);
    }

    public function actionModify(){
        $alliance = AllianceCommon::requiredAlliance();
        $modelId = AllianceCommon::getModelValueFromFormData('GoodsSkuAlliance');
        if (!StringUtils::isBlank($modelId)){
            $model = GoodsSkuAllianceService::getGoodsInfo($modelId,$alliance['id'],$alliance['company_id'],true);
            ExceptionAssert::assertTrue($model->audit_status==GoodsSkuAlliance::AUDIT_STATUS_EDIT,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"商品已不支持编辑"));
            $model->restoreForm();
        }
        else{
            $model = new GoodsSkuAlliance();
            $model->goods_type = GoodsConstantEnum::TYPE_OBJECT;
        }
        $model->setScenario("alliance");
        $load = $model->load(\Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"数据格式错误"));
        $model->company_id = $alliance['company_id'];
        $model->audit_status = GoodsSkuAlliance::AUDIT_STATUS_EDIT;
        $model->display_channel = GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_OUTER;
        $model->goods_owner_type = GoodsConstantEnum::OWNER_HA;
        $model->goods_owner_id = $alliance['id'];
        $model->sku_img = $model->goods_img;
        $model->sku_unit = "份";
        $model->sku_status = GoodsConstantEnum::STATUS_DOWN;
        $model->storeForm();
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,AllianceCommon::getModelErrors($model)));
        return RestfulResponse::success($model->id);
    }


    public function actionAudit(){
        $alliance = AllianceCommon::requiredAlliance();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::submitAudit($id,$alliance['id'],$alliance['company_id']);
        return RestfulResponse::success(true);
    }


    public function actionWithdraw(){
        $alliance = AllianceCommon::requiredAlliance();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::withdraw($id,$alliance['id'],$alliance['company_id']);
        return RestfulResponse::success(true);
    }

    public function actionPublish(){
        $alliance = AllianceCommon::requiredAlliance();
        $id = Yii::$app->request->get("id");
        ExceptionAssert::assertNotNull($id,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'id'));
        GoodsSkuAllianceService::publish($id,$alliance,$alliance['company_id']);
        return RestfulResponse::success(true);
    }

}