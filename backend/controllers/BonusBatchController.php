<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\BonusBatchSearch;
use backend\services\BonusBatchService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\BonusBatch;
use common\models\Common;
use yii;

/**
 * BonusBatch  controller
 */
class BonusBatchController extends BaseController {

    public function actionIndex(){
        $searchModel = new BonusBatchSearch();
        BackendCommon::addCompanyIdToParams('BonusBatchSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
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
        BonusBatchService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }



    public function actionModify() {
        $company_id = BackendCommon::getInitCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new BonusBatch();
            $model = $model->loadDefaultValues();
        } else {
            $model = BonusBatchService::requireDisplayModel($id,RedirectParams::create("活动不存在",['bonus-batch/index']),true);
            $model->amount = Common::showAmount($model->amount);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                $model->amount = Common::setAmount($model->amount);
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model->restoreForm(),
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['bonus-batch/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

}
