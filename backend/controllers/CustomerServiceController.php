<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\OrderCustomerServiceSearch;
use backend\services\DeliveryService;
use backend\services\OrderCustomerServiceService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use Yii;

class CustomerServiceController extends BaseController {


    public function actionIndex(){
        $company_id = BackendCommon::getFCompanyId();
        $searchModel = new OrderCustomerServiceSearch();
        BackendCommon::addCompanyIdToParams('OrderCustomerServiceSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = OrderCustomerServiceService::renameImages($dataProvider);
        $deliveryModel = DeliveryService::getAllDelivery($company_id);
        $deliveryOptions = DeliveryService::generateOptions($deliveryModel);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'deliveryOptions' => $deliveryOptions,
        ]);
    }

    public function actionDetail() {
        $company_id = BackendCommon::getFCompanyId();
        $customerServiceId = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($customerServiceId,RedirectParams::create("customer_service_id参数缺失",['customer-service/index']));
        $model = OrderCustomerServiceService::getCustomerServiceDetail($customerServiceId,$company_id,RedirectParams::create("售后单不存在",['customer-service/index']));
        list($model,$order,$displayVO,$orderGoodsProvider,$customerServiceLogProvider) = OrderCustomerServiceService::generateVO($model);
        return $this->render('detail',[
            'model' => $model,
            'order' => $order,
            'displayVO'=>$displayVO,
            'orderGoodsProvider'=>$orderGoodsProvider,
            'customerServiceLogProvider'=>$customerServiceLogProvider,
        ]);
    }


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->post('commander');
        $id = Yii::$app->request->post("id");
        $auditRemark = Yii::$app->request->post("audit_remark");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        $userId = BackendCommon::getUserId();
        $username = BackendCommon::getUserName();
        OrderCustomerServiceService::operate($id,$commander,$company_id,$userId,$username,$auditRemark,RedirectParams::create("审核操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

}