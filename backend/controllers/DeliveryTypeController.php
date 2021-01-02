<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\DeliveryTypeSearch;
use backend\services\DeliveryService;
use backend\services\DeliveryTypeService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Common;
use common\models\DeliveryType;
use Yii;

class DeliveryTypeController extends BaseController {

    public function actionIndex(){
        $deliveryId = Yii::$app->request->get('delivery_id');
        $company_id = BackendCommon::getFCompanyId();
        $deliveryModel = DeliveryService::requireActiveModel($deliveryId,$company_id,RedirectParams::create("记录不存在",['/delivery/index']),false);
        $searchModel = new DeliveryTypeSearch();
        BackendCommon::addCompanyIdToParams('DeliveryTypeSearch');
        BackendCommon::addValueIdToParams('delivery_id',$deliveryId,'DeliveryTypeSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'deliveryModel' => $deliveryModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        $deliveryId = Yii::$app->request->get("delivery_id");
        $deliveryModel = DeliveryService::requireActiveModel($deliveryId,$company_id,RedirectParams::create("记录不存在",['/delivery/index']),false);
        if (empty($id)) {
            $model = new DeliveryType();
            $model->loadDefaultValues();
        } else {
            $model = DeliveryTypeService::requireActiveModel($id,$deliveryId,$company_id,RedirectParams::create("记录不存在",['delivery-type/index','delivery_id'=>$deliveryId]),true);
            $model->params = Common::showAmount($model->params);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                $model->delivery_id = $deliveryId;
                $model->params = Common::setAmount($model->params);
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model->restoreForm(),
                    'deliveryModel'=>$deliveryModel
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['delivery-type/index','delivery_id'=>$deliveryId]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'deliveryModel'=>$deliveryModel
        ]);
    }


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        DeliveryTypeService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


}
