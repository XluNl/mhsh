<?php 

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\services\GoodsService;
use backend\services\GoodsSkuService;
use backend\utils\BExceptionAssert;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\GoodsSku;
use Yii;

class GoodsSkuController extends BaseController{

	public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id");
        $goodsSkuId = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($goodsId,RedirectParams::create("商品ID参数不能为空",['goods/index']));
        $goodsModel = GoodsService::requireActiveGoods($goodsId,$company_id,RedirectParams::create("商品不存在",['goods/index']),true);
        if (empty($goodsSkuId)) {
            $model = new GoodsSku();
            $model->loadDefaultValues();
        } else {
            $model = GoodsSkuService::requireActiveGoodsSku($goodsSkuId,$goodsId,$company_id,RedirectParams::create("商品属性不存在",['goods/index']),true);
            $model->restoreForm();
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->storeForm();
                $model->goods_id = $goodsId;
                $model->company_id = $company_id;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("商品保存失败",$this,'modify',[
                    'model' => $model->restoreForm(),
                    'goodsModel' => $goodsModel])
                );
                BackendCommon::showSuccessInfo("属性保存成功");
                return $this->redirect([
                    'goods/index',
                    'GoodsSearch[sort_1]' => $goodsModel->sort_1,
                    'GoodsSearch[sort_2]' => $goodsModel->sort_2,
                    'GoodsSearch[goods_name]' => $goodsModel->goods_name,
                    'GoodsSearch[goods_owner]' => $goodsModel->goods_owner
                ]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            "goodsModel"=>$goodsModel
        ]);
	}


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $goodsId = Yii::$app->request->get("goods_id");
        $goodsSkuId = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($goodsId,RedirectParams::create("goodsId不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($goodsSkuId,RedirectParams::create("goodsSkuId不存在",Yii::$app->request->referrer));
        GoodsSkuService::operate($goodsSkuId,$goodsId,$commander,$company_id,RedirectParams::create("商品属性操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("商品属性操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGoodsSkuOptions(){
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id",null);
        try{
            BExceptionAssert::assertNotBlank($goodsId,BBusinessException::create("goods_id不能为空"));
            $goodsSkuArr = GoodsSkuService::getSkuListByGoodsIdOptions($goodsId,$company_id);
            return BackendCommon::parseOptions($goodsSkuArr);
        }
        catch (BBusinessException $e){
            Yii::error($e->getMessage());
            return "";
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return "";
        }
    }


}