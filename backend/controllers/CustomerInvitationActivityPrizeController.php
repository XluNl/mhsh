<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\CustomerInvitationActivityPrizeSearch;
use backend\services\CustomerInvitationActivityPrizeService;
use backend\services\CustomerInvitationActivityService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\CommonStatus;
use common\models\CustomerInvitationActivityPrize;
use common\utils\ArrayUtils;
use yii;

/**
 * CustomerInvitationActivityPrize  controller
 */
class CustomerInvitationActivityPrizeController extends BaseController {

    public function actionIndex(){
        $company_id = BackendCommon::getFCompanyId();
        $queryParams = Yii::$app->request->getQueryParam('CustomerInvitationActivityPrizeSearch',[]);
        $activityId = ArrayUtils::getArrayValue("activity_id",$queryParams);
        $activityModel = CustomerInvitationActivityService::requireModel($activityId,$company_id,RedirectParams::create("记录不存在",['/customer-invitation-activity/index']),false);
        $searchModel = new CustomerInvitationActivityPrizeSearch();
        BackendCommon::addCompanyIdToParams('CustomerInvitationActivityPrizeSearch');
        BackendCommon::addValueIdToParams('activity_id',$activityId,'CustomerInvitationActivityPrizeSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'activityModel'=>$activityModel,
        ]);
    }

    public function actionOperation(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $activityId = Yii::$app->request->get("activity_id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($activityId,RedirectParams::create("activity_id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        CustomerInvitationActivityPrizeService::operate($id,$commander,$activityId,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        $activityId = Yii::$app->request->get("activity_id");
        $activityModel = CustomerInvitationActivityService::requireModel($activityId,$company_id,RedirectParams::create("活动不存在",['customer-invitation-activity/index']),true);
        if (empty($id)) {
            $model = new CustomerInvitationActivityPrize();
            $model->activity_id = $activityId;
            $model->status = CommonStatus::STATUS_ACTIVE;
            $model->company_id = $company_id;
        } else {
            $model = CustomerInvitationActivityPrizeService::requireModel($id,$activityId,$company_id,RedirectParams::create("活动奖品不存在",['customer-invitation-activity-prize/index','CustomerInvitationActivityPrizeSearch[activity_id]'=>$activityId]),true);
            $model->restoreForm();
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                $model->formatForm();
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model->restoreForm(),
                    'activityModel'=>$activityModel
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['customer-invitation-activity-prize/index','CustomerInvitationActivityPrizeSearch[activity_id]'=>$activityId]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'activityModel'=>$activityModel
        ]);
    }

}
