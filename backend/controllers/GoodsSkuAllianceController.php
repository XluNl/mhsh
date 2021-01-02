<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\GoodsSkuAllianceSearch;
use backend\services\GoodsSkuAllianceService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use common\services\OwnerTypeService;
use Yii;

/**
 * GoodsSkuAllianceController implements the CRUD actions for GoodsSkuAlliance model.
 */
class GoodsSkuAllianceController extends BaseController
{
    public function actionIndex(){
        $searchModel = new GoodsSkuAllianceSearch();
        BackendCommon::addCompanyIdToParams('GoodsSkuAllianceSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($searchModel->goods_owner_type,$companyId);
        $searchModel->ownerTypeOptions = $ownerTypeOptions;
        GoodsSkuAllianceService::completeInfos($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOperation(){
        $companyId = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->post('commander');
        $id = Yii::$app->request->post("id");
        $auditNote = Yii::$app->request->post("audit_note");
        BExceptionAssert::assertNotBlank($id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"id不存在"));
        BExceptionAssert::assertNotBlank($auditNote,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"audit_note不存在"));
        BExceptionAssert::assertNotBlank($commander,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,"commander不存在"));
        $operatorId = BackendCommon::getUserId();
        $operatorName = BackendCommon::getUserName();
        GoodsSkuAllianceService::operate($id,$commander,$auditNote,$companyId,$operatorId,$operatorName,BStatusCode::createExpWithParams(BStatusCode::GOODS_SKU_ALLIANCE_AUDIT_ERROR,"保存失败"));
        return BRestfulResponse::success("操作成功");
    }
}
