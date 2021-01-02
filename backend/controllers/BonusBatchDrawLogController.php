<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\DrawBonusForm;
use backend\models\searches\BonusBatchDrawLogSearch;
use backend\services\BizTypeService;
use backend\services\BonusBatchService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Common;
use yii;

/**
 * BonusBatchDrawLog  controller
 */
class BonusBatchDrawLogController extends BaseController {

    public function actionIndex(){
        $searchModel = new BonusBatchDrawLogSearch();
        BackendCommon::addCompanyIdToParams('BonusBatchDrawLogSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = BonusBatchService::completeBizName($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionDraw() {
        $company_id = BackendCommon::getFCompanyId();
        $model = new DrawBonusForm();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $batchNo = Yii::$app->request->get("batch_no");
        BExceptionAssert::assertNotBlank($batchNo,RedirectParams::create("batch_no不能为空",Yii::$app->request->referrer));
        $bonusBatchModel = BonusBatchService::getActiveByBatchNo($batchNo,false);
        $model->batch_no = $batchNo;
        $bizOptions = [];
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $bizOptions = BizTypeService::getOptionsByBizType($model->biz_type);
                if ($model->validate()){
                    $model->batch_no = $batchNo;
                    $model->num = Common::setAmount($model->num);
                    try{
                        BExceptionAssert::assertTrue($model->validate(),BStatusCode::createExpWithParams(BStatusCode::DRAW_COUPON_ERROR,'参数错误'));
                        BonusBatchService::drawManualBonus($company_id,$model->biz_type,$model->biz_id,$model->batch_no,$model->num,$userId,$userName,$model->remark);
                    }
                    catch (\Exception $e){

                        BExceptionAssert::assertTrue(false,RenderParams::create($e->getMessage(),$this,'draw',[
                            'model' => $model->restoreForm(),
                            'bonusBatchModel'=>$bonusBatchModel,
                            'bizOptions'=>$bizOptions,
                        ]));
                    }
                    BackendCommon::showSuccessInfo("发放成功");
                    return $this->redirect(['bonus-batch-draw-log/index','BonusBatchDrawLogSearch[batch_id]'=>$bonusBatchModel['id']]);
                }
            }
        }
        return $this->render("draw", [
            'model' => $model->restoreForm(),
            'bonusBatchModel'=>$bonusBatchModel,
            'bizOptions'=>$bizOptions,
        ]);
    }

}
