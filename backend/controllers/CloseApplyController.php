<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\CloseApplySearch;
use backend\services\CloseApplyService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\params\RedirectParams;
use Yii;
use yii\web\Controller;

/**
 * CloseApplyController implements the CRUD actions for CloseApply model.
 */
class CloseApplyController extends Controller
{
    public function actionIndex(){
        $searchModel = new CloseApplySearch();
        BackendCommon::addCompanyIdToParams('CloseApplySearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = CloseApplyService::renameImages($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $operatorId = BackendCommon::getUserId();
        $operatorName = BackendCommon::getUserName();
        $commander = Yii::$app->request->post('commander');
        $id = Yii::$app->request->post("id");
        $auditNote = Yii::$app->request->post("audit_note");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        CloseApplyService::operate($id,$commander,$company_id,$operatorId,$operatorName,$auditNote,RedirectParams::create("审核操作失败",Yii::$app->request->referrer));
        return BRestfulResponse::success("审核操作成功");
    }
}
