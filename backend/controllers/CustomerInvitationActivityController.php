<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\CustomerInvitationActivitySearch;
use backend\services\CustomerInvitationActivityService;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\CommonStatus;
use common\models\CustomerInvitationActivity;
use yii;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;

/**
 * CustomerInvitationActivity  controller
 */
class CustomerInvitationActivityController extends BaseController {

    public function actionIndex(){
        $searchModel = new CustomerInvitationActivitySearch();
        BackendCommon::addCompanyIdToParams('CustomerInvitationActivitySearch');
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
        CustomerInvitationActivityService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功，只允许同时进行一个活动，其他活动已停用");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new CustomerInvitationActivity();
            $model = $model->loadDefaultValues();
            $model->version = 0;
            $model->settle_status = CustomerInvitationActivity::SETTLE_STATUS_UN_DEAL;
            $model->status = CommonStatus::STATUS_ACTIVE;
            $model->company_id = $company_id;
        } else {
            $model = CustomerInvitationActivityService::requireModel($id,$company_id,RedirectParams::create("活动不存在",['customer-invitation-activity/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['customer-invitation-activity/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionPreStatistic(){
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        $data = CustomerInvitationActivityService::preStatisticData($id,$company_id,RedirectParams::create("统计失败",Yii::$app->request->referrer));
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data['invitationModels'],
            'pagination' => new Pagination(['pageSize' => 1000]),
        ]);
        $sumPrizesDataProvider = new ArrayDataProvider([
            'allModels' => $data['sumPrizes'],
            'pagination' => new Pagination(['pageSize' => 1000]),
        ]);

        return $this->render("pre-statistic", [
            'dataProvider' => $dataProvider,
            'sumPrizesDataProvider'=>$sumPrizesDataProvider,
        ]);
    }

    public function actionTrySettle(){
        $activityId = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($activityId,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $data = CustomerInvitationActivityService::settleActivityResult(true,$activityId,$company_id,$userId,$userName,BBusinessException::create("结算失败"),"尝试结算");
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data['invitationModels'],
            'pagination' => new Pagination(['pageSize' => 1000]),
        ]);
        $successPrizesDataProvider = new ArrayDataProvider([
            'allModels' => $data['successPrizes'],
            'pagination' => new Pagination(['pageSize' => 1000]),
        ]);
        $failedPrizesDataProvider = new ArrayDataProvider([
            'allModels' => $data['failedPrizes'],
            'pagination' => new Pagination(['pageSize' => 1000]),
        ]);
        $activityModel = $data['activityModel'];
        return $this->render("try-settle", [
            'activityModel'=>$activityModel,
            'dataProvider' => $dataProvider,
            'successPrizesDataProvider'=>$successPrizesDataProvider,
            'failedPrizesDataProvider'=>$failedPrizesDataProvider,
        ]);
    }


    public function actionRealSettle(){
        $activityId = Yii::$app->request->post("activity_id");
        BExceptionAssert::assertNotBlank($activityId,RedirectParams::create("id不能为空",Yii::$app->request->referrer));
        $remark = Yii::$app->request->post("remark");
        BExceptionAssert::assertNotBlank($remark,RedirectParams::create("remark不能为空",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $data = CustomerInvitationActivityService::settleActivityResult(false,$activityId,$company_id,$userId,$userName,BBusinessException::create("结算失败"),$remark);
        BackendCommon::showSuccessInfo("结算成功");
        return $this->redirect(['customer-invitation-activity-result/index','CustomerInvitationActivityResultSearch[activity_id]'=>$activityId]);
    }


}
