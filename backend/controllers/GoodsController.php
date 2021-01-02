<?php 

namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\GoodsImportForm;
use backend\models\forms\GoodsSoldChannelForm;
use backend\models\searches\GoodsSearch;
use backend\services\DeliveryService;
use backend\services\GoodsDetailService;
use backend\services\GoodsDisplayDomainService;
use backend\services\GoodsDownloadService;
use backend\services\GoodsService;
use backend\services\GoodsSoldChannelService;
use backend\services\GoodsSortService;
use backend\utils\BExceptionAssert;
use backend\utils\BootstrapFileInputConfigUtil;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\components\BootstrapFileUpload;
use common\models\Goods;
use common\models\GoodsDetail;
use common\models\GoodsSort;
use common\services\OwnerTypeService;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;


class GoodsController extends BaseController{

    public function actionIndex(){
        $companyId = BackendCommon::getFCompanyId();
        $searchModel = new GoodsSearch();
        BackendCommon::addCompanyIdToParams('GoodsSearch');
        $goodsOwner = BackendCommon::getSearchParamsByName('GoodsSearch','goods_owner',null);
        $bigSortId = BackendCommon::getSearchParamsByName('GoodsSearch','sort_1',null);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        GoodsDisplayDomainService::assembleGoodsList($dataProvider);
        GoodsDisplayDomainService::completeStorageSkuName($dataProvider);
        $bigSortArr = GoodsSortService::getGoodsSortOptions($companyId,$goodsOwner,0);
        $smallSortArr = GoodsSortService::getGoodsSortOptions($companyId,$goodsOwner,$bigSortId);
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($searchModel->goods_owner,$companyId);
        $searchModel->ownerTypeOptions = $ownerTypeOptions;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'bigSortArr'=>$bigSortArr,
            'smallSortArr'=>$smallSortArr,
        ]);
    }

    public function actionModify() {
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id", null);
        if (empty($goodsId)) {
            $model = new Goods();
            $model->loadDefaultValues();
        } else {
            $model = GoodsService::requireActiveGoods($goodsId,$company_id,RedirectParams::create("商品不存在",['goods/index']),true);
        }
        $bigSortArr = GoodsSortService::getGoodsSortOptions($company_id,$model->goods_owner,0);
        $parent_id = (!empty($model->sort_1)) ? $model->sort_1 : ArrayUtils::getFirstKeyFromArray($bigSortArr,null);
        $smallSortArr = GoodsSortService::getGoodsSortOptions($company_id,$model->goods_owner,$parent_id);
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                is_array($model->goods_images) && $model->goods_images && $model->goods_images = implode(',', $model->goods_images);
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("商品保存失败",$this,'modify',[
                    'model' => $model,
                    'bigSortArr' => $bigSortArr,
                    'smallSortArr' => $smallSortArr,])
                );
                BackendCommon::showSuccessInfo("属性保存成功");
                return $this->redirect(['goods/index', 'GoodsSearch[sort_1]' => $model->sort_1, 'GoodsSearch[sort_2]' => $model->sort_2, 'GoodsSearch[goods_name]' => $model->goods_name,'GoodsSearch[goods_owner]' => $model->goods_owner,]);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'bigSortArr' => $bigSortArr,
            'smallSortArr' => $smallSortArr
        ]);
    }


    public function actionVideo() {
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id", null);
        BExceptionAssert::assertNotBlank($goodsId,RedirectParams::create("商品不存在",['goods/index']));
        $model = GoodsService::requireActiveGoods($goodsId,$company_id,RedirectParams::create("商品不存在",['goods/index']),true);
        return $this->render("video", [
            'model' => $model,
        ]);
    }

    public function actionVideoFileUpload()
    {
        $request = Yii::$app->request;
        $model = new BootstrapFileUpload();
        if ($request->isAjax && $request->isPost) {
            $goodsId = Yii::$app->request->post("goods_id", null);
            if (StringUtils::isBlank($goodsId)){
                return Json::encode(BootstrapFileInputConfigUtil::createFailedResultConfig("goods_id缺失"));
            }
            $companyId = BackendCommon::getFCompanyId();
            $model->load(Yii::$app->request->post());
            $config = GoodsService::addVideo($goodsId,$companyId,$model);
            return Json::encode($config);
        }
        return Json::encode([]);
    }

    public function actionVideoFileRemove()
    {
        $request = Yii::$app->request;
        if ($request->isAjax && $request->isPost) {
            $goodsId = Yii::$app->request->post("goods_id", null);
            $fileKey = Yii::$app->request->post("key", null);
            if (StringUtils::isBlank($goodsId)){
                return Json::encode(BootstrapFileInputConfigUtil::createFailedResultConfig("goods_id缺失"));
            }
            if (StringUtils::isBlank($fileKey)){
                return Json::encode(BootstrapFileInputConfigUtil::createFailedResultConfig("key缺失"));
            }
            $companyId = BackendCommon::getFCompanyId();
            $config = GoodsService::removeVideo($goodsId,$companyId,$fileKey);
            return Json::encode($config);
        }
        return Json::encode([]);
    }

    public function actionDetail(){
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get('goods_id');
        BExceptionAssert::assertNotBlank($goodsId,RedirectParams::create("goods_id不存在",['goods/index']));
        $model = GoodsDetailService::getById($goodsId,$company_id,true);
        if (empty($model)){
            $model = new GoodsDetail();
            $model->goods_id = $goodsId;
        }
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $company_id;
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("详情页保存失败",$this,'detail',['model'=>$model]) );
                BackendCommon::showSuccessInfo("详情页保存成功");
                return $this->redirect(['goods/index']);
            }
        }
        return $this->render("detail", ['model'=>$model]);

    }


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $goodsId = Yii::$app->request->get("goods_id");
        BExceptionAssert::assertNotBlank($goodsId,RedirectParams::create("goodsId不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        GoodsService::operate($goodsId,$commander,$company_id,RedirectParams::create("商品操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("商品操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }



    public function actionGoodsOptionsByOwner(){
        $company_id = BackendCommon::getFCompanyId();
        $goodsOwner = Yii::$app->request->get("goods_owner",null);
        try{
            BExceptionAssert::assertNotBlank($goodsOwner,BBusinessException::create("goods_owner不能为空"));
            $goodsArr = GoodsService::getListByGoodsOwnerOptions($company_id,$goodsOwner,BBusinessException::create("根据goodsOwner获取商品列表失败"));
            return BackendCommon::parseOptions($goodsArr);
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return "";
        }
    }

    public function actionGoodsOptionsByBigSort(){
        $company_id = BackendCommon::getFCompanyId();
        $bigSort = Yii::$app->request->get("big_sort",null);
        try{
            BExceptionAssert::assertNotBlank($bigSort,BBusinessException::create("big_sort不能为空"));
            $goodsArr = GoodsService::getListByBigSort($company_id,$bigSort);
            $goodsArr = ArrayHelper::map($goodsArr,'id','goods_name');
            return BackendCommon::parseOptions($goodsArr);
        }
        catch (\Exception $e){
            Yii::error($e->getMessage());
            return "";
        }
    }


	public function actionExport(){
        $company_id = BackendCommon::getFCompanyId();
        $searchModel = new GoodsSearch();
        BackendCommon::addCompanyIdToParams('GoodsSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        GoodsDownloadService::exportGoods($dataProvider->query,$company_id);
        return;
    }

    public function actionImport(){
        $company_id = BackendCommon::getFCompanyId();
        $model = new GoodsImportForm();
        if (Yii::$app->request->isPost){
            $model->load(Yii::$app->request->post());
            $model->file = UploadedFile::getInstance($model, 'file');
            if (!$model->validate()){
                return $this->render('import',['model' => $model]);
            }
            $file_path =  dirname(dirname(__FILE__)).'/web/uploads/excel/';
            if (!file_exists($file_path)){
                FileHelper::createDirectory($file_path);
            }
            $excel_file = $file_path.'goods-'.$company_id.'-'.time().'.'.$model->file->getExtension();
            $model->file->saveAs($excel_file);
            $errorData = GoodsDownloadService::importGoods($excel_file,$company_id);
            if (empty($errorData)){
                BackendCommon::showSuccessInfo('导入更新成功');
                return $this->redirect(['/goods/index']);
            }
            return $this->render('import-result',['errorData' => $errorData]);
        }
        return $this->render('import',['model' => $model]);
    }


    public function actionSoldChannel() {
        $company_id = BackendCommon::getFCompanyId();
        $goodsId = Yii::$app->request->get("goods_id", null);
        $goodsModel = GoodsService::requireActiveGoods($goodsId,$company_id,RedirectParams::create("商品不存在",['goods/index']),true);
        if (empty($goodsId)) {
            $model = new GoodsSoldChannelForm();
            $model->sold_channel_type = $goodsModel->goods_sold_channel_type;
        } else {
            $model = GoodsSoldChannelService::getSoldChannelForm($goodsId,$company_id);
            $model->sold_channel_type = $goodsModel->goods_sold_channel_type;
        }
        $goodsModel = GoodsService::requireActiveGoods($goodsId,$company_id,RedirectParams::create("商品不存在",['goods/index']),true);
        $deliveryNames = DeliveryService::getAllActiveModel(null,$company_id);
        $deliveryNames = ArrayUtils::map($deliveryNames,'id','nickname','phone');
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                list($res,$error) = GoodsSoldChannelService::addSoldChannel($model,$goodsId,$company_id);
                BExceptionAssert::assertTrue($res,RenderParams::create($error,$this,'sold-channel',[
                    'model' => $model,
                    'deliveryNames' => $deliveryNames,
                    'goodsModel'=>$goodsModel
                ])
                );
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['goods/index', 'GoodsSearch[sort_1]' => $goodsModel->sort_1, 'GoodsSearch[sort_2]' => $goodsModel->sort_2, 'GoodsSearch[goods_name]' => $goodsModel->goods_name,'GoodsSearch[goods_owner]' => $goodsModel->goods_owner]);
            }
        }
        return $this->render("sold-channel", [
            'model' => $model,
            'deliveryNames' => $deliveryNames,
            'goodsModel'=>$goodsModel
        ]);
    }
	
	public function actionSort() {
	    $company_id = \Yii::$app->user->identity->company_id;
	    $parent_id = Yii::$app->request->get('parent_id', 0);
	    $condition = array('sort_status' => 1,'company_id'=>$company_id);
	    if (!empty($parent_id)) {
	        $condition["parent_id"] = $parent_id;
	    }
	    $sorts = GoodsSort::find()->orderBy("sort_order")
	    ->select('id,sort_name,profit_rate')
	    ->where($condition)
	    ->asArray()
	    ->all();
	    exit(Json::encode($sorts));
	
	}

}