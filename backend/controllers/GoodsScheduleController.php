<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\GoodsScheduleSearch;
use backend\services\CouponBatchService;
use backend\services\GoodsScheduleCollectionService;
use backend\services\GoodsScheduleService;
use backend\services\GoodsService;
use backend\services\GoodsSkuService;
use backend\services\ScheduleOutStockBatchService;
use backend\services\StorageSkuMappingService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use Yii;

class GoodsScheduleController extends BaseController {

    public function actionIndex(){
        $searchModel = new GoodsScheduleSearch();
        BackendCommon::addCompanyIdToParams('GoodsScheduleSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = GoodsScheduleService::completeCouponBatchInfo($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $collectionId = Yii::$app->request->get("collection_id");
        BExceptionAssert::assertNotBlank($collectionId,RedirectParams::create("collection_id不能为空",Yii::$app->request->referrer));
        $goodsScheduleCollectionModel = GoodsScheduleCollectionService::requireActiveModel($collectionId,$company_id,RedirectParams::create("记录不存在",['/goods-schedule-collection/index']),false);
        $scheduleId = Yii::$app->request->get("schedule_id");
        $srcScheduleId = Yii::$app->request->get("src_schedule_id");
        if (StringUtils::isBlank($scheduleId)) {
            $model = new GoodsSchedule();
            //$model->loadDefaultValues();
            $model->collection_id = $collectionId;
            $model->display_start = $goodsScheduleCollectionModel['display_start'];
            $model->display_end = $goodsScheduleCollectionModel['display_end'];
            $model->offline_time = $goodsScheduleCollectionModel['offline_time'];
            $model->online_time = $goodsScheduleCollectionModel['online_time'];
            $model->owner_type = $goodsScheduleCollectionModel['owner_type'];
            $model->owner_id = $goodsScheduleCollectionModel['owner_id'];
            GoodsScheduleService::copyTime($model,$srcScheduleId,$company_id);
            list($goodsArr,$goodsSkuArr,$scheduleDisplayChannelArr)=GoodsScheduleService::generateGoodsScheduleFormOptionsByGoodsOwner($goodsScheduleCollectionModel['owner_type'],$company_id);
        } else {
            $model = GoodsScheduleService::requireActiveGoodsSchedule($scheduleId,$company_id,RedirectParams::create("商品排期不存在",['goods-schedule/index']),true);
            $model->price = Common::showAmount($model->price);
            list($goodsArr,$goodsSkuArr,$scheduleDisplayChannelArr)=GoodsScheduleService::generateGoodsScheduleFormOptionsByGoodsId($model->goods_id,$company_id);
        }
        $model->setScenario("backend");
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $goodsSku = GoodsSkuService::getSkuInfo($model->sku_id,$model->goods_id,$company_id);
                if (!empty($goodsSku)){

                    //绑定关系和比例一次成型
                    if (StringUtils::isBlank($model->id)){
                        $storageSkuMapping = StorageSkuMappingService::getModel($model->sku_id,$company_id);
                        if (!empty($storageSkuMapping)){
                            $model->storage_sku_id = $storageSkuMapping['storage_sku_id'];
                            $model->storage_sku_num = $storageSkuMapping['storage_sku_num'];
                        }
                    }

                    $model->price = Common::setAmount($model->price);
                    $model->company_id = $company_id;
                    $model->collection_id = $collectionId;
                    BExceptionAssert::assertTrue($model->save(),RenderParams::create("排期保存失败",$this,'modify',[
                        'model' => $model->restoreForm(),
                        'goodsArr' => $goodsArr,
                        'goodsSkuArr'=>$goodsSkuArr,
                        'scheduleDisplayChannelArr'=>$scheduleDisplayChannelArr])
                    );
                    BackendCommon::showSuccessInfo("排期保存成功");
                    return $this->redirect(['goods-schedule/index', 'GoodsScheduleSearch[schedule_display_channel]' => $model->schedule_display_channel, 'GoodsScheduleSearch[collection_id]' => $model->collection_id]);
                }
                else{
                    BackendCommon::showErrorInfo("商品属性和商品未匹配，请刷新重试");
                }
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'goodsArr' => $goodsArr,
            'goodsSkuArr'=>$goodsSkuArr,
            'scheduleDisplayChannelArr'=>$scheduleDisplayChannelArr
        ]);
    }

    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $scheduleId = Yii::$app->request->get("schedule_id");
        BExceptionAssert::assertNotBlank($scheduleId,RedirectParams::create("schedule_id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        GoodsScheduleService::operate($scheduleId,$commander,$company_id,RedirectParams::create("商品操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("商品操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * 排期纬度发货
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDeliveryOut(){
        $scheduleId = Yii::$app->request->get("schedule_id");
        BExceptionAssert::assertNotBlank($scheduleId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'schedule_id'));
        $company_id = BackendCommon::getFCompanyId();
        $userId = BackendCommon::getUserId();
        $userName = BackendCommon::getUserName();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $count = ScheduleOutStockBatchService::deliveryOutB($scheduleId,null,null,$company_id,$userId,$userName,BStatusCode::createExpWithParams(BStatusCode::DELIVERY_OUT_ERROR,'排期发货失败'));
            $transaction->commit();
            BackendCommon::showSuccessInfo("本次排期发货：{$count}件");
            return $this->redirect(Yii::$app->request->referrer);
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e->getMessage());
            BExceptionAssert::assertTrue(false,RedirectParams::create("本次发货失败:".$e->getMessage(),Yii::$app->request->referrer));
        }
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


    /**
     * 获取排期商品
     * @return string
     */
    public function actionScheduleGoodsSelectModal()
    {
        $searchModel = new GoodsScheduleSearch();
        BackendCommon::addCompanyIdToParams('GoodsScheduleSearch');
        BackendCommon::addValueIdToParams('schedule_date',date("Y-m-d H:i:s"),'GoodsScheduleSearch');
        if (StringUtils::isNotBlank($searchModel->owner_type)&&StringUtils::isBlank($searchModel->schedule_display_channel)){
            BackendCommon::addValueIdToParams('schedule_display_channel',ArrayUtils::getArrayValue($searchModel->owner_type,GoodsConstantEnum::$scheduleSearchDisplayChannelMap),'GoodsScheduleSearch');
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('scheduleGoodsSelectModal',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }


    public function actionDetail(){
        $schedule_id = Yii::$app->request->get("schedule_id");
        BExceptionAssert::assertNotBlank($schedule_id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'schedule_id'));
        $companyId = BackendCommon::getFCompanyId();
        $goodsSchedule = GoodsScheduleService::getActiveGoodsSchedule($schedule_id,$companyId);
        return BRestfulResponse::success($goodsSchedule);
    }


    public function actionRecommendOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        try{
            BExceptionAssert::assertNotBlank($id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'id'));
            BExceptionAssert::assertNotBlank($commander,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'commander'));
            GoodsScheduleService::recommendOperate($id,$commander,$company_id,BBusinessException::create("操作失败"));
            return BRestfulResponse::success(true);
        }
        catch (\Exception $e){
            return BRestfulResponse::error($e);
        }
    }

}
