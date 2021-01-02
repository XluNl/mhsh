<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\WithdrawApplySearch;
use backend\services\WithdrawApplyService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use Yii;

class WithdrawApplyController extends BaseController {

    public function actionIndex(){
        $searchModel = new WithdrawApplySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAuditOperation(){
        $commander = Yii::$app->request->post('commander');
        $id = Yii::$app->request->post("id");
        $auditRemark = Yii::$app->request->post("audit_note");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        WithdrawApplyService::auditOperation($id,$commander,$auditRemark,$userId,$userName,RedirectParams::create("审核操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("审核操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionProcess(){
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        WithdrawApplyService::processDeal($id,$userId,$userName,RedirectParams::create("打款操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("打款操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRefund(){
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        WithdrawApplyService::manualRefund($id,$userId,$userName,RedirectParams::create("手动退回余额操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("手动退回余额操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
}
