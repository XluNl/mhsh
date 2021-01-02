<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\OrderSearch;
use backend\services\AllianceService;
use backend\services\DeliveryService;
use backend\services\OrderDownloadService;
use backend\services\OrderService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\params\RedirectParams;
use common\utils\ArrayUtils;
use Exception;
use Yii;

class OrderController extends BaseController {

    public function actionIndex(){
        $company_id = BackendCommon::getFCompanyId();
        $searchModel = new OrderSearch();
        BackendCommon::addCompanyIdToParams('OrderSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $deliveryNames = DeliveryService::getAllDelivery($company_id);
        $deliveryNames = ArrayUtils::map($deliveryNames,'id','nickname','phone');
        $allianceNames = AllianceService::getAllAlliance($company_id);
        $allianceNames = ArrayUtils::map($allianceNames,'id','nickname','phone');
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'deliveryNames'=> $deliveryNames,
            'allianceNames'=>$allianceNames,
        ]);
    }

    public function actionDetail() {
        $company_id = BackendCommon::getFCompanyId();
        $order_no = Yii::$app->request->get("order_no");
        BExceptionAssert::assertNotBlank($order_no,RedirectParams::create("订单编号参数缺失",['order/index']));
        $model = OrderService::getOrderDetail($order_no,$company_id,true,RedirectParams::create("订单编号参数缺失",['order/index']));
        $orderStatusFlow = OrderService::getViewFlow($model['order_status'],$model['accept_delivery_type']);
        $orderItems = OrderService::getOrderDetailItem($model);
        return $this->render('detail',[
            'model' => $model,
            'orderStatusFlow' => $orderStatusFlow,
            'orderItems'=>$orderItems,
        ]);
    }



    public function actionAdminNote(){
        $adminNote = Yii::$app->request->post("admin_note");
        $order_no = Yii::$app->request->post("order_no");
        $transaction = Yii::$app->db->beginTransaction();
        try{
            BExceptionAssert::assertNotBlank($order_no,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
            $company_id = BackendCommon::getFCompanyId();
            $model = OrderService::getOrderDetail($order_no,$company_id,true,BStatusCode::createExp(BStatusCode::ORDER_NOT_EXIST));
            OrderService::addAdminNote($model,$adminNote);
            $transaction->commit();
        }
        catch (Exception $e){
            $transaction->rollBack();
            return BRestfulResponse::error($e);
        }
        return BRestfulResponse::success("备注更新成功");
    }


    public function actionComplete(){
        $order_no = Yii::$app->request->get("order_no");
        BExceptionAssert::assertNotBlank($order_no,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
        $company_id = BackendCommon::getFCompanyId();
        $model = OrderService::requireOrder($order_no,$company_id,false,RedirectParams::create("订单不存在",['order/index']));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        OrderService::complete($model,RedirectParams::create("平台确认收货失败",Yii::$app->request->referrer),$userId,$userName);
        BackendCommon::showSuccessInfo("平台确认收货成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionCancel(){
        $order_no = Yii::$app->request->get("order_no");
        BExceptionAssert::assertNotBlank($order_no,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
        $company_id = BackendCommon::getFCompanyId();
        $model = OrderService::requireOrder($order_no,$company_id,false,RedirectParams::create("订单不存在",['order/index']));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        OrderService::cancelOrder($model,RedirectParams::create("平台取消订单失败",Yii::$app->request->referrer),$userId,$userName);
        BackendCommon::showSuccessInfo("平台取消订单成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionDeliveryOut(){
        $order_no = Yii::$app->request->get("order_no");
        BExceptionAssert::assertNotBlank($order_no,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
        $company_id = BackendCommon::getFCompanyId();
        $model = OrderService::requireOrder($order_no,$company_id,false,RedirectParams::create("订单不存在",['order/index']));
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();

        $transaction = Yii::$app->db->beginTransaction();
        try{
            OrderService::deliveryOut($model,$userId,$userName);
            $transaction->commit();
        }
        catch (Exception $e){
            $transaction->rollBack();
            BackendCommon::showErrorInfo($e->getMessage());
            return $this->redirect(Yii::$app->request->referrer);
        }
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionUploadWeight(){
        $orderNo = Yii::$app->request->post("order_no");
        $orderGoodsId = Yii::$app->request->post("order_goods_id");
        $numAc = Yii::$app->request->post("num_ac");
        BExceptionAssert::assertNotBlank($orderNo,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_no'));
        BExceptionAssert::assertNotBlank($orderGoodsId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'order_goods_id'));
        BExceptionAssert::assertNotBlank($numAc,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'num_ac'));
        BExceptionAssert::assertTrue($numAc>=0,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'num_ac必须大于等于0'));
        $company_id = BackendCommon::getFCompanyId();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $transaction = Yii::$app->db->beginTransaction();
        try{
            if ($numAc==0){
                OrderService::unUploadWeight($company_id,$orderNo,$orderGoodsId,$userId,$userName);
            }
            else{
                OrderService::uploadWeight($company_id,$orderNo,$orderGoodsId,$numAc,$userId,$userName);
            }
            $transaction->commit();
        }
        catch (Exception $e){
            $transaction->rollBack();
            return BRestfulResponse::error($e);
        }
        return BRestfulResponse::success("操作成功");
    }


    public function actionExport(){
        $company_id = BackendCommon::getFCompanyId();
        $searchModel = new OrderSearch();
        BackendCommon::addCompanyIdToParams('OrderSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        OrderDownloadService::exportOrder($dataProvider->query,$company_id);
        return;
    }

}
