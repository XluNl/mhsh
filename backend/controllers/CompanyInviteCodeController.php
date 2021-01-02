<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\CompanyInviteCodeService;
use backend\services\GoodsDisplayDomainService;
use backend\utils\params\RedirectParams;
use Yii;
use yii\web\Controller;

/**
 * CompanyInviteCodeController implements the CRUD actions for CompanyInviteCode model.
 */
class CompanyInviteCodeController extends Controller
{
    /**
     * Lists all CompanyInviteCode models.
     * @return mixed
     */
    public function actionIndex()
    {
        $companyId = BackendCommon::getFCompanyId();
        $model = CompanyInviteCodeService::getShowModel($companyId);
        GoodsDisplayDomainService::renameImageUrl($model,'business_invite_image');
        GoodsDisplayDomainService::renameImageUrl($model,'alliance_invite_image');
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * 生成团长邀请码
     * @return \yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \yii\base\Exception
     */
    public function actionRefreshBusiness(){
        $companyId = BackendCommon::getFCompanyId();
        CompanyInviteCodeService::refreshBusinessCode($companyId,RedirectParams::create("更新失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("生成成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


    /**
     * 生成联盟邀请码
     * @return \yii\web\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \yii\base\Exception
     */
    public function actionRefreshAlliance(){
        $companyId = BackendCommon::getFCompanyId();
        CompanyInviteCodeService::refreshAllianceCode($companyId,RedirectParams::create("更新失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("生成成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
}
