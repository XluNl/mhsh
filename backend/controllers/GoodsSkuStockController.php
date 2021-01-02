<?php 

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\GoodsSkuStockSearch;
use backend\services\GoodsScheduleService;
use backend\services\GoodsSkuStockDownloadService;
use backend\services\GoodsSkuStockService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RenderParams;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuStock;
use common\models\RoleEnum;
use Yii;

class GoodsSkuStockController extends BaseController{


    public function actionIndex(){
        $searchModel = new GoodsSkuStockSearch();
        BackendCommon::addCompanyIdToParams('GoodsSkuStockSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }



	public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $model = new GoodsSkuStock();
        $model->loadDefaultValues();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        list($goodsArr,$goodsSkuArr,$scheduleDisplayChannelArr)=GoodsScheduleService::generateGoodsScheduleFormOptionsByGoodsOwner(GoodsConstantEnum::OWNER_SELF,$company_id);
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->schedule_id = 0;
                $model->company_id = $company_id;
                $model->operator_role = RoleEnum::ROLE_AGENT;
                $model->operator_id = $userId;
                $model->operator_name = $userName;
                BExceptionAssert::assertTrue(GoodsSkuStockService::operateGoodsSkuStock($model),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                    'goodsArr' => $goodsArr,
                    'goodsSkuArr'=>$goodsSkuArr
                ])
                );
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['goods-sku-stock/modify']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'goodsArr' => $goodsArr,
            'goodsSkuArr'=>$goodsSkuArr,
        ]);
	}


    public function actionExportLog(){
        $company_id = BackendCommon::getFCompanyId();
        $searchModel = new GoodsSkuStockSearch();
        BackendCommon::addCompanyIdToParams('GoodsSkuStockSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        GoodsSkuStockDownloadService::exportLog($dataProvider->query,$company_id);
        return;
    }


    public function actionExportStock(){
        $company_id = BackendCommon::getFCompanyId();
        GoodsSkuStockDownloadService::exportGoodsStock($company_id);
        return;
    }

}