<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\ClaimBalanceForm;
use backend\models\forms\WithdrawForm;
use backend\models\searches\DistributeBalanceSearch;
use backend\services\BizTypeService;
use backend\services\DistributeBalanceService;
use backend\services\WithdrawApplyService;
use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\BizTypeEnum;
use common\models\Common;
use common\models\WithdrawApply;
use Yii;

class DistributeBalanceController extends BaseController {

    public function actionIndex(){
        $searchModel = new DistributeBalanceSearch();
        BackendCommon::addCompanyIdToParams('DistributeBalanceSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionClaim() {
        $company_id = BackendCommon::getFCompanyId();
        $model = new ClaimBalanceForm();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $bizOptions = [];
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if (key_exists($model->biz_type,BizTypeEnum::getBizTypeOperaArr($company_id))){
                    if ($model->validate()){
                        $model->num = Common::setAmount($model->num);
                        $transaction = \Yii::$app->db->beginTransaction();
                        try{
                            BExceptionAssert::assertTrue($model->validate(),BStatusCode::createExpWithParams(BStatusCode::CLAIM_BALANCE_ERROR,'参数错误'));
                            list($result,$errorMsg) = DistributeBalanceService::claim($company_id,$model->biz_type,$model->biz_id,$model->type,$model->num,$userId,$userName,$model->remark);
                            BExceptionAssert::assertTrue($result,BBusinessException::create($errorMsg));
                            $transaction->commit();
                        }
                        catch (\Exception $e){
                            $transaction->rollBack();
                            BExceptionAssert::assertTrue(false,RenderParams::create($e->getMessage(),$this,'claim',[
                                'model' => $model->restoreForm(),
                                'bizOptions'=>$bizOptions,
                            ]));
                        }
                        BackendCommon::showSuccessInfo("扣款成功");
                        return $this->redirect(['/distribute-balance/index','DistributeBalanceSearch[biz_type]'=>$model->biz_type,'DistributeBalanceSearch[biz_id]'=>$model->biz_id]);
                    }
                }
                else{
                    $model->addError('biz_type','不支持的账户类型');
                }
                $bizOptions = BizTypeService::getOptionsByBizType($model->biz_type);
            }
        }
        return $this->render("claim", [
            'model' => $model->restoreForm(),
            'bizOptions'=>$bizOptions,
        ]);
    }


    public function actionWithdraw() {
        $companyId = BackendCommon::getFCompanyId();
        $model = new WithdrawForm();
        $operatorId = BackendCommon::getUserId();
        $operatorName = BackendCommon::getUserName();
        $distributeBalance = null;
        if (BackendCommon::isSuperCompany($companyId)){
            $distributeBalance = DistributeBalanceService::getModelByBiz($companyId,BizTypeEnum::BIZ_TYPE_COMPANY,$companyId);
        }
        else{
            $distributeBalance = DistributeBalanceService::getModelByBiz($companyId,BizTypeEnum::BIZ_TYPE_AGENT,$companyId);
        }
        if ($distributeBalance!=null){
            $model->available_amount = Common::showAmount($distributeBalance['amount']);
        }
        else{
            $model->available_amount = 0;
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()){
                    $model->storeForm();
                    $transaction = \Yii::$app->db->beginTransaction();
                    try{
                        WithdrawApplyService::createCustomerBalanceWithdrawApplyB($companyId, $model->withdraw_amount, WithdrawApply::TYPE_OFFLINE,$operatorId,$operatorName);
                        $transaction->commit();
                    }
                    catch (\Exception $e){
                        $transaction->rollBack();
                        BExceptionAssert::assertTrue(false,RenderParams::create($e->getMessage(),$this,'withdraw',[
                            'model' => $model->restoreForm(),
                        ]));
                    }
                    BackendCommon::showSuccessInfo("提现申请成功");
                    return $this->redirect(['/distribute-balance-item/index','DistributeBalanceItemSearch[distribute_balance_id]'=>$distributeBalance['id']]);
                }
            }
        }
        return $this->render("withdraw", [
            'model' => $model,
        ]);
    }


    public function actionBalanceWithdraw(){
        $amount = Yii::$app->request->get("amount",0);
        $amount = intval(Common::setAmount($amount));
        BExceptionAssert::assertTrue($amount>10,RedirectParams::create("金额必须大于0.01",Yii::$app->request->referrer));
        $operatorId = BackendCommon::getUserId();
        $operatorName = BackendCommon::getUserName();
        $companyId= BackendCommon::getFCompanyId();
        $distributeBalance = null;
        $transaction = \Yii::$app->db->beginTransaction();
        try{
            $distributeBalance = WithdrawApplyService::createCustomerBalanceWithdrawApplyB($companyId, $amount, WithdrawApply::TYPE_OFFLINE,$operatorId,$operatorName);
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            BExceptionAssert::assertTrue(false,RedirectParams::create($e->getMessage(),Yii::$app->request->referrer));
        }
        BackendCommon::showSuccessInfo("提现申请成功");
        return $this->redirect(['/distribute-balance-item/index','DistributeBalanceItemSearch[distribute_balance_id]'=>$distributeBalance['id']]);
    }
}
