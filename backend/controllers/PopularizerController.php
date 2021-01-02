<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\PopularizerSearch;
use backend\services\PopularizerService;
use backend\services\RegionService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Popularizer;
use Yii;

class PopularizerController extends BaseController {


    public function actionIndex(){
        $searchModel = new PopularizerSearch();
        BackendCommon::addCompanyIdToParams('PopularizerSearch');
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
            $model = new Popularizer();
            $model->loadDefaultValues();
        } else {
            $model = PopularizerService::requireActiveModel($id,$company_id,RedirectParams::create("记录不存在",['goods-sort/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['popularizer/index']);
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
        PopularizerService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

}