<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\BannerSearch;
use backend\services\BannerService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Banner;
use common\models\GoodsSchedule;
use backend\models\searches\GoodsScheduleSearch;
use Yii;

class BannerController extends BaseController
{

    public function actionIndex()
    {
        $searchModel = new BannerSearch();
        BackendCommon::addCompanyIdToParams('BannerSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify()
    {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new Banner();
            $model->loadDefaultValues();
        } else {
            $model = BannerService::requireActiveModel($id, $company_id, RedirectParams::create("记录不存在", ['banner/index']), true);
        }
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                $model->operator_id = $userId;
                $model->operator_name = $userName;
                $model->storeForm();
                BExceptionAssert::assertTrue($model->save(), RenderParams::create("保存失败" . json_encode($model->errors, JSON_UNESCAPED_UNICODE), $this, 'modify', [
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['banner/index']);
            }
        }

        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionScheduleGoodsList()
    {
        $company_id = BackendCommon::getFCompanyId();
        BackendCommon::addCompanyIdToParams('GoodsScheduleSearch');
        BackendCommon::addValueIdToParams('banner_link', true, 'GoodsScheduleSearch');
        BackendCommon::addValueIdToParams('schedule_status', [GoodsSchedule::DISPLAY_STATUS_WAITING, GoodsSchedule::DISPLAY_STATUS_IN_SALE], 'GoodsScheduleSearch');
        BackendCommon::addValueIdToParams('schedule_date', date("Y-m-d H:i:s"), 'GoodsScheduleSearch');

        $searchModel = new GoodsScheduleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('/group-active/goods', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    public function actionOperation()
    {
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id, RedirectParams::create("id不存在", Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander, RedirectParams::create("操作命令不存在", Yii::$app->request->referrer));
        BannerService::operateStatus($id, $commander, $company_id, RedirectParams::create("操作失败", Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


}
