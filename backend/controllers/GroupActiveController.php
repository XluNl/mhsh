<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\GroupActiveSearch;
use backend\services\GoodsService;
use backend\services\GroupActiveService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\GroupActive;
use common\utils\StringUtils;
use Yii;

class GroupActiveController extends BaseController
{   
    public function actionIndex()
    {	
        $searchModel = new GroupActiveSearch();
        BackendCommon::addCompanyIdToParams('GroupActiveSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        if (StringUtils::isNotBlank($searchModel->owner_type)){
            $searchModel->goodsOptions = GoodsService::getListByGoodsOwnerOptionsNoErr($companyId,$searchModel->owner_type);
        }
    	return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionModify(){
        $companyId = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("active_id");
        if (empty($id)) {
            $model = new GroupActive();
            $model->loadDefaultValues();
        } else {
            $model = GroupActiveService::requireActiveModel($id,$companyId,RedirectParams::create("记录不存在",['group-active/index']));
            $model->restoreForm();
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $companyId;
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                $model->storeForm();
                BExceptionAssert::assertTrue(GroupActiveService::validateOwnerTypeAndSave($model),RenderParams::createModelError($model,$this,'modify',[
                    'model' => $model->restoreForm(),
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['group-active/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }


    public function actionDetail(){
        $active_id = Yii::$app->request->get("active_id");
        BExceptionAssert::assertNotBlank($active_id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'active_id'));
        $company_id = BackendCommon::getFCompanyId();
        $activeM = GroupActiveService::requireActiveModel($active_id,$company_id,BBusinessException::create("记录不存在"));
        $activeM->restoreForm();
        return $this->render('detail',['model'=>$activeM]);
    }


    public function actionOperation(){
        $companyId = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $activeId = Yii::$app->request->get("active_id");
        BExceptionAssert::assertNotBlank($activeId,RedirectParams::create("activeId不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        GroupActiveService::operate($activeId,$commander,$companyId,RedirectParams::create("拼团活动操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("拼团活动操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

}
