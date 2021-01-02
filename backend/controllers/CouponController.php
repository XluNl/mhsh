<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\CouponSearch;
use backend\services\CouponService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use Yii;

/**
 * Coupon controller
 */
class CouponController extends BaseController {

    public function actionIndex(){
        $searchModel = new CouponSearch();
        BackendCommon::addCompanyIdToParams('CouponSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = CouponService::completeUsedInfo($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionDiscard(){
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        CouponService::discard($id,$company_id,$userId,$userName,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }
}
