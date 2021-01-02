<?php

/**
 * Created by PhpStorm.
 * User: JackGe
 * Date: 2016/5/25
 * Time: 16:07
 */

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\RouteSearch;
use backend\services\RouteService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Route;
use Yii;


class RouteController extends BaseController{


    public function actionIndex(){
        $searchModel = new RouteSearch();
        BackendCommon::addCompanyIdToParams('RouteSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
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
        RouteService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new Route();
            $model->status = Route::STATUS_ACTIVE;
        } else {
            $model = RouteService::requireModel($id,$company_id,RedirectParams::create("活动不存在",['coupon-batch/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['route/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionRouteMap(){
        return $this->render("route-map");
    }


    public function actionRouteList(){
        $company_id = BackendCommon::getFCompanyId();
        $routes = RouteService::getRouteList($company_id);
        return $this->renderPartial('route-list',['routes'=>$routes]);
    }

    public function actionDeliveryList(){
        $routeId = Yii::$app->request->get("route_id",-1);
        $company_id = BackendCommon::getFCompanyId();
        $deliveryModels = RouteService::getDeliveryByRouteId($routeId,$company_id);
        return BRestfulResponse::success($deliveryModels);
    }

    public function actionUpdateRouteDelivery(){
        $routeId = Yii::$app->request->get("route_id");
        $deliveryId = Yii::$app->request->get("delivery_id");
        $company_id = BackendCommon::getFCompanyId();
        RouteService::updateRouteDelivery($routeId,$deliveryId,$company_id);
        return BRestfulResponse::success(true);
    }

}