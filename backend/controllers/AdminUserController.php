<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\forms\SignupForm;
use backend\models\searches\AdminUserSearch;
use backend\services\AdminUserService;
use backend\services\CompanyService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\utils\StringUtils;
use Yii;

/**
 * AdminUserController implements the CRUD actions for AdminUser model.
 */
class AdminUserController extends BaseController
{

    /**
     * Lists all AdminUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AdminUserSearch();
        BackendCommon::addCompanyIdToParams('AdminUserSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $searchModel->companyOptions = CompanyService::getAllCompanyOptions();
        CompanyService::completeCompanyName($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify()
    {
        $id = Yii::$app->request->get("id");
        $companyId = $this->getCompanyId();
        BExceptionAssert::assertNotBlank($companyId,RedirectParams::create("company_id不能为空",Yii::$app->request->referrer));
        $model = new SignupForm();
        $adminUserModel = null;
        if (empty($id)) {
            $model->setScenario("signup");
        }
        else{
            $adminUserModel = AdminUserService::requireModel($id,$companyId,RedirectParams::create("记录不存在",['admin-user/index']),true);
            $model->nickname = $adminUserModel->nickname;
            $model->username = $adminUserModel->username;
            $model->email = $adminUserModel->email;
            $model->id = $id;
        }
        if (Yii::$app->request->isPost) {
            if (empty($id)){
                $model->company_id = $companyId;
                $model->setScenario('signup');
                if ($model->load(Yii::$app->request->post())) {
                    BExceptionAssert::assertNotNull($model->signup(),RenderParams::create("保存失败",$this,'modify',[
                        'model' => $model,
                    ]));
                    BackendCommon::showSuccessInfo("保存成功");
                    return $this->redirect(['admin-user/index','AdminUserSearch[company_id]'=>$companyId]);
                }
            }
            else{
                if ($model->load(Yii::$app->request->post())) {
                    $adminUserModel->nickname = $model->nickname;
                    $adminUserModel->email = $model->email;
                    if (!empty($model->password)){
                        $adminUserModel->setPassword($model->password);
                        $adminUserModel->generateAuthKey();
                    }
                    BExceptionAssert::assertNotNull($adminUserModel->save(),RenderParams::create("保存失败",$this,'modify',[
                        'model' => $model,
                    ]));
                    BackendCommon::showSuccessInfo("保存成功");
                    return $this->redirect(['admin-user/index','AdminUserSearch[company_id]'=>$companyId]);
                }
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionSuperAdmin(){
        $id = Yii::$app->request->get("id");
        $companyId = BackendCommon::getFCompanyId();
        $companyId = BackendCommon::isSuperCompany($companyId)?null:$companyId;
        AdminUserService::requireModel($id,$companyId,RedirectParams::create("记录不存在",['admin-user/index']),false);
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不能为空",Yii::$app->request->referrer));
        AdminUserService::setAdminUser($id,RedirectParams::create("设置超管失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("设置超管成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionStatus(){
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $companyId = BackendCommon::getFCompanyId();
        $companyId = BackendCommon::isSuperCompany($companyId)?null:$companyId;
        AdminUserService::requireModel($id,$companyId,RedirectParams::create("记录不存在",['admin-user/index']),false);
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        AdminUserService::operateStatus($id,$commander,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    private function getCompanyId(){
        $selfCompanyId = BackendCommon::getFCompanyId();
        $companyIdParam = Yii::$app->request->get("company_id");
        if (BackendCommon::isSuperCompany($selfCompanyId)){
            if (StringUtils::isNotBlank($companyIdParam)){
                return $companyIdParam;
            }
            return $selfCompanyId;
        }
        return $selfCompanyId;
    }
}
