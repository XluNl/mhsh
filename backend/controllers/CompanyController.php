<?php
namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\CompanySearch;
use backend\services\CompanyService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Company;
use Yii;

/**
 * Company controller
 */
class CompanyController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actionIndex(){
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new Company();
            $model->loadDefaultValues();
        } else {
            $model = CompanyService::requireActiveModel($id,RedirectParams::create("记录不存在",['company/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['company/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionStatus(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        CompanyService::operateStatus($id,$commander,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

}
