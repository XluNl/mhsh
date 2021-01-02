<?php

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\GoodsSortSearch;
use backend\services\GoodsSortService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\GoodsSort;
use Yii;
use yii\helpers\ArrayHelper;

class GoodsSortController extends BaseController {

    public function actionIndex(){
        $searchModel = new GoodsSortSearch();
        BackendCommon::addCompanyIdToParams('GoodsSortSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionSelectOptions()
    {
        $company_id = BackendCommon::getFCompanyId();
        $parent_id = Yii::$app->request->get("parent_id", 0);
        $goodsOwner = Yii::$app->request->get("sort_owner", null);
        $sortArr = GoodsSortService::getGoodsSortOptions($company_id,$goodsOwner,$parent_id);
        return BackendCommon::parseOptions($sortArr);
    }




    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $sortId = Yii::$app->request->get("sort_id");
        BExceptionAssert::assertNotBlank($sortId,RedirectParams::create("sortId不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        GoodsSortService::operate($sortId,$commander,$company_id,RedirectParams::create("商品分类操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("商品分类操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionDelete(){
        $company_id = BackendCommon::getFCompanyId();
        $sortId = Yii::$app->request->get("sort_id");
        BExceptionAssert::assertNotBlank($sortId,RedirectParams::create("sortId不存在",Yii::$app->request->referrer));
        GoodsSortService::delete($sortId,$company_id,RedirectParams::create("删除失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("删除成功");
        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $sortId = Yii::$app->request->get("sort_id");
        if (empty($sortId)) {
            $model = new GoodsSort();
            $model->loadDefaultValues();
        } else {
            $model = GoodsSortService::requireActiveGoodsSort($sortId,$company_id,RedirectParams::create("商品分类不存在",['goods-sort/index']),true);
        }
        $bigSortArr =  GoodsSortService::getGoodsSortOptions($company_id,$model->sort_owner,0);
        $bigSortArr = ArrayHelper::merge(['0'=>'新的一级菜单'],$bigSortArr);
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("商品分类保存失败",$this,'modify',[
                    'model' => $model,
                    'bigSortArr' => $bigSortArr
                ]));
                BackendCommon::showSuccessInfo("商品分类保存成功");
                return $this->redirect(['goods-sort/index', 'GoodsSortSearch[sort_owner]' => $model->sort_owner]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'bigSortArr' => $bigSortArr,
        ]);
    }


}