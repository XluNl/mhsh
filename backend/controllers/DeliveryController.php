<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\DeliveryTagForm;
use backend\models\forms\GoodsDeliveryForm;
use backend\models\searches\DeliverySearch;
use backend\services\DeliveryService;
use backend\services\DeliveryStatisticService;
use backend\services\GoodsService;
use backend\services\GoodsSkuService;
use backend\services\RegionService;
use backend\services\TagService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Common;
use common\models\Delivery;
use common\models\Tag;
use common\utils\ArrayUtils;
use Yii;

class DeliveryController extends BaseController {

    public function actionIndex(){
        $searchModel = new DeliverySearch();
        BackendCommon::addCompanyIdToParams('DeliverySearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new Delivery();
            $model->loadDefaultValues();
        } else {
            $model = DeliveryService::requireActiveModel($id,$company_id,RedirectParams::create("记录不存在",['delivery/index']),true);
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                is_array($model->contract_images) && $model->contract_images && $model->contract_images = implode(',', $model->contract_images);
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['delivery/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
        ]);
    }

    public function actionOperationAllowOrder(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        DeliveryService::operateAllowOrder($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        DeliveryService::operateStatus($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionGoodsOptions(){
        $company_id = BackendCommon::getFCompanyId();
        $goodsOwner = Yii::$app->request->get("goods_owner",null);
        try{
            BExceptionAssert::assertNotBlank($goodsOwner,BBusinessException::create("goods_owner不能为空"));
            $goodsArr = GoodsService::getListByGoodsOwnerOptions($company_id,$goodsOwner,BBusinessException::create("根据goodsOwner获取商品列表失败"));
            return BackendCommon::parseOptions($goodsArr);
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

    public function actionScheduleDisplayChannelOptions(){
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id",null);
        try{
            BExceptionAssert::assertNotBlank($goodsId,BBusinessException::create("goods_id不能为空"));
            $scheduleDisplayChannelArr = GoodsSkuService::getScheduleDisplayChannel($goodsId,$company_id,BBusinessException::create("商品不存在"));
            return BackendCommon::parseOptions($scheduleDisplayChannelArr);
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



    public function actionDeliveryMap(){
        return $this->render("delivery-map");
    }

    public function actionDeliveryListMap(){
        $date = Yii::$app->request->get("date");
        BExceptionAssert::assertNotBlank($date,RedirectParams::create("date",Yii::$app->request->referrer));
        $company_id = BackendCommon::getFCompanyId();
        $deliveryOrderList = DeliveryService::getDeliveryMapList($company_id,$date);
        return $this->renderPartial('delivery-list',['deliveryOrderList'=>$deliveryOrderList]);
    }

    public function actionDashboard(){
        $company_id = BackendCommon::getFCompanyId();
        $deliveryModel = DeliveryService::getAllDelivery($company_id);
        $deliveryOptions = DeliveryService::generateOptions($deliveryModel);
        return $this->render("dashboard",[
            'deliveryOptions'=>$deliveryOptions
        ]);
    }

    public function actionDashboardDeliverySummary(){
        $companyId = BackendCommon::getFCompanyId();
        $deliverId = Yii::$app->request->post('delivery_id',null);
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = DeliveryStatisticService::getDeliveryTradeSummary($companyId,$deliverId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionDashboardDeliveryDay(){
        $companyId = BackendCommon::getFCompanyId();
        $deliverId = Yii::$app->request->post('delivery_id',null);
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        $data = DeliveryStatisticService::getDeliveryTradeDay($companyId,$deliverId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }

    public function actionDashboardGoodsSummary(){
        $companyId = BackendCommon::getFCompanyId();
        $deliverId = Yii::$app->request->get('delivery_id',null);
        $startDate = Yii::$app->request->get('start_date');
        $endDate = Yii::$app->request->get('end_date');
        $data = DeliveryStatisticService::getGoodsSummary($companyId,$deliverId,$startDate,$endDate);
        return BRestfulResponse::success($data);
    }


    public function actionGoodsDelivery(){
        $companyId = BackendCommon::getFCompanyId();
        $model = new GoodsDeliveryForm();
        $deliverId = Yii::$app->request->get("delivery_id");
        BExceptionAssert::assertNotBlank($deliverId,RedirectParams::create("delivery_id不能为空",Yii::$app->request->referrer));
        $deliverModel = DeliveryService::requireActiveModel($deliverId,$companyId,RedirectParams::create("配送团点不存在",['delivery/index']),false);
        $model->delivery_id = $deliverId;
        if (Yii::$app->request->isPost) {
            $goodsList = GoodsService::getListByGoodsOwnerOptions($companyId,$model->goods_owner,BBusinessException::create("根据goodsOwner获取商品列表失败"));
            if ($model->load(Yii::$app->request->post())) {
                if ($model->validate()){
                    try{
                        BExceptionAssert::assertTrue($model->validate(),BStatusCode::createExpWithParams(BStatusCode::DELIVERY_GOODS_DELIVERY_CHANNEL_ERROR,'参数错误'));
                        DeliveryService::goodsDeliveryChannel($companyId,$model->delivery_id,$model->goods_ids);
                    }
                    catch (\Exception $e){
                        BExceptionAssert::assertTrue(false,RenderParams::create($e->getMessage(),$this,'goods-delivery',[
                            'model' => $model ,
                            'goodsArr' => $goodsList,
                        ]));
                    }
                    BackendCommon::showSuccessInfo("发放成功");
                    return $this->redirect(['delivery/index','DeliverySearch[id]'=>$deliverId]);
                }
            }
        }
        else{
            $goodsList = GoodsService::getListByGoodsOwnerOptions($companyId,$model->goods_owner,BBusinessException::create("根据goodsOwner获取商品列表失败"));
        }
        return $this->render("goods-delivery", [
            'model' => $model ,
            'goodsArr' => $goodsList,
        ]);
    }


    public function actionPlatformRoyalty() {
        $companyId = BackendCommon::getFCompanyId();
        $deliveryId = Yii::$app->request->get("delivery_id");
        BExceptionAssert::assertNotBlank($deliveryId,RedirectParams::create("delivery_id",Yii::$app->request->referrer));
        $deliveryModel = DeliveryService::requireActiveModel($deliveryId,$companyId,RedirectParams::create("delivery_id",Yii::$app->request->referrer),false);
        $tagModel = TagService::getPlatformRoyaltyTag($companyId,$deliveryId);
        $model = new DeliveryTagForm();
        $model->delivery = $deliveryModel;
        $model->delivery_id = $deliveryId;
        $model->tag_name  = ArrayUtils::getArrayValue(Tag::TAG_DELIVERY_PLATFORM_ROYALTY,Tag::$tagArr);
        if (!empty($tagModel)){
            $model->tag_info_id = $tagModel['id'];
            $model->tag_value = Common::showPercent($tagModel['biz_ext']);
        }
        else{
            $model->tag_value = Common::showPercent(ArrayUtils::getArrayValue(Tag::TAG_DELIVERY_PLATFORM_ROYALTY,Tag::$tagDefaultArr));
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                list($result,$errorMsg) = TagService::setPlatformRoyaltyTag($companyId,$deliveryId,$deliveryModel['nickname'],(int)Common::setPercent($model->tag_value),$model->tag_info_id);
                BExceptionAssert::assertTrue($result,RenderParams::create($errorMsg,$this,'platform-royalty',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['delivery/index','DeliverySearch[id]'=>$deliveryId]);
            }
        }
        return $this->render("platform-royalty", [
            'model' => $model,
        ]);
    }
}
