<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\AllianceSearch;
use backend\services\AllianceService;
use backend\services\RegionService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Alliance;
use Yii;

class AllianceController extends BaseController {

    public function actionIndex(){
        $searchModel = new AllianceSearch();
        BackendCommon::addCompanyIdToParams('AllianceSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new Alliance();
            $model->loadDefaultValues();
        } else {
            $model = AllianceService::requireActiveModel($id,$company_id,RedirectParams::create("记录不存在",['alliance/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                is_array($model->store_images) && $model->store_images && $model->store_images = implode(',', $model->store_images);
                is_array($model->contract_images) && $model->contract_images && $model->contract_images = implode(',', $model->contract_images);
                is_array($model->qualification_images) && $model->qualification_images && $model->qualification_images = implode(',', $model->qualification_images);
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['alliance/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        AllianceService::operateStatus($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


}
