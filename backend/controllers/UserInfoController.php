<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\UserInfoSearch;
use backend\services\RegionService;
use backend\services\UserInfoService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\UserInfo;
use Yii;

class UserInfoController extends BaseController {

    public function actionIndex(){
        $searchModel = new UserInfoSearch();
        BackendCommon::addCompanyIdToParams('DeliverySearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        UserInfoService::completeCustomerInfo($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new UserInfo();
            $model->loadDefaultValues();
        } else {
            $model = UserInfoService::requireModel($id,RedirectParams::create("记录不存在",['user-info/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['user-info/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }


    public function actionOperation(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        UserInfoService::operateStatus($id,$commander,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionDelete(){
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'id'));
        UserInfoService::deleteData($id,RedirectParams::create("平台取消订单失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("删除成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
}
