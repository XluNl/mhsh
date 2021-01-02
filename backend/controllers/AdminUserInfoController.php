<?php


namespace backend\controllers;


use backend\models\BackendCommon;
use backend\models\forms\ChangePasswordForm;
use backend\services\AdminUserInfoService;
use backend\services\AdminUserService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\AdminUser;
use common\models\AdminUserInfo;
use Yii;

class AdminUserInfoController extends BaseController
{
    /**
     * @return string|\yii\web\Response
     */
    public function actionIndex()
    {
        $userId = BackendCommon::getUserId();
        $adminUserModel = AdminUserService::requireModel($userId,null,RedirectParams::create("记录不存在",['site/index']),true);
        $model = AdminUserInfoService::getModelByUserId($userId,true);
        if (empty($model)){
            $model = new AdminUserInfo();
        }
        $model->nickname = $adminUserModel->nickname;
        if (Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                AdminUser::updateAll(['nickname'=>$adminUserModel['nickname']],['id'=>$userId]);
                $model->user_id = $userId;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'index',['model'=>$model]) );
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['index']);
            }
        }
        return $this->render("index", [
            'model' => $model,
        ]);
    }


    public function actionChangePassword()
    {
        $model = new ChangePasswordForm();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->change()) {
            return $this->goHome();
        }
        return $this->render('change-password', [
            'model' => $model,
        ]);
    }
}