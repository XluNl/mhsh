<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\DrawCouponForm;
use backend\models\searches\CouponBatchSearch;
use backend\services\CouponBatchService;
use backend\services\CouponService;
use backend\services\CustomerService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Common;
use common\models\CouponBatch;
use common\services\OwnerTypeService;
use yii;

/**
 * CouponBatch controller
 */
class CouponBatchController extends BaseController {

    public function actionIndex(){
        $searchModel = new CouponBatchSearch();
        BackendCommon::addCompanyIdToParams('CouponBatchSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($searchModel->owner_type,$companyId);
        $searchModel->ownerTypeOptions = $ownerTypeOptions;
        $dataProvider = CouponBatchService::completeUsedInfo($dataProvider);
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
        CouponBatchService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionPopOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        try{
            BExceptionAssert::assertNotBlank($id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'id'));
            BExceptionAssert::assertNotBlank($commander,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'commander'));
            CouponBatchService::popOperate($id,$commander,$company_id,BBusinessException::create("操作失败"));
            return BRestfulResponse::success(true);
        }
        catch (\Exception $e){
            return BRestfulResponse::error($e);
        }
    }



    public function actionModify() {
        $companyId = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new CouponBatch();
            $model->loadDefaultValues();
        } else {
            $model = CouponBatchService::requireDisplayModel($id,$companyId,RedirectParams::create("活动不存在",['coupon-batch/index']),true);
            $model->startup = Common::showAmount($model->startup);
            $model->discount = Common::showAmount($model->discount);
        }
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($model->owner_type,$companyId);
        list($sortArr,$goodsArr,$skusArr,$sortId,$goodId,$skuId) = CouponBatchService::getModifyInitInfo($companyId,$model->use_limit_type,$model->use_limit_type_params);
        $model->big_sort = $sortId;
        $model->goods_id = $goodId;
        $model->sku_id = $skuId;
        $model->decodeUserTimeFeature();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->storeForm();
                $model->company_id = $companyId;
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model->restoreForm(),
                    'sortArr'=>$sortArr,
                    'goodsArr'=>$goodsArr,
                    'skusArr'=>$skusArr,
                    'ownerTypeOptions'=>$ownerTypeOptions
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['coupon-batch/index', 'CouponBatchSearch[use_limit_type]' => $model->use_limit_type]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'sortArr'=>$sortArr,
            'goodsArr'=>$goodsArr,
            'skusArr'=>$skusArr,
            'ownerTypeOptions'=>$ownerTypeOptions
        ]);
    }


    public function actionDrawCoupon() {
        $company_id = BackendCommon::getFCompanyId();
        $model = new DrawCouponForm();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $customerId = Yii::$app->request->get("customer_id");
        BExceptionAssert::assertNotBlank($customerId,RedirectParams::create("customer_id不能为空",Yii::$app->request->referrer));
        $customerModel = CustomerService::getActiveModel($customerId);
        $model->customer_id = $customerId;
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->customer_id = $customerId;
                try{
                    BExceptionAssert::assertTrue($model->validate(),BStatusCode::createExpWithParams(BStatusCode::DRAW_COUPON_ERROR,'参数错误'));
                    CouponBatchService::drawPrivateCoupon($company_id,$model->customer_id,$model->batch_no,$model->num,$userId,$userName,$model->remark);
                }
                catch (\Exception $e){
                    BExceptionAssert::assertTrue(false,RenderParams::create($e->getMessage(),$this,'draw-coupon',[
                        'model' => $model,
                        'customerModel'=>$customerModel,
                    ]));
                }
                BackendCommon::showSuccessInfo("发放成功");
                return $this->redirect(['coupon/index','CouponSearch[customer_id]'=>$customerId]);
            }
        }
        return $this->render("draw-coupon", [
            'model' => $model,
            'customerModel'=>$customerModel,
        ]);
    }

    public function actionDiscardAll(){
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $count = CouponBatchService::discardAll($id,$company_id,$userId,$userName);
        BackendCommon::showSuccessInfo("操作成功,作废{$count}张优惠券");
        return $this->redirect(Yii::$app->request->referrer);
    }

}
