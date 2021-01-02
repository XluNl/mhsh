<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\BusinessApplySearch;
use backend\services\BusinessApplyService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use Yii;

class BusinessApplyController extends BaseController {

    public function actionIndex(){
        $searchModel = new BusinessApplySearch();
        BackendCommon::addCompanyIdToParams('BusinessApplySearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = BusinessApplyService::renameImages($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        BusinessApplyService::operate($id,$commander,$company_id,RedirectParams::create("审核操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("审核操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


}
