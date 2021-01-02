<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\forms\GoodsScheduleImportForm;
use backend\models\searches\GoodsScheduleCollectionSearch;
use backend\services\GoodsScheduleCollectionService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\GoodsScheduleCollection;
use common\services\OwnerTypeService;
use common\utils\DateTimeUtils;
use yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * GoodsScheduleCollection controller
 */
class GoodsScheduleCollectionController extends BaseController {

    public function actionIndex(){
        $searchModel = new GoodsScheduleCollectionSearch();
        BackendCommon::addCompanyIdToParams('GoodsScheduleCollectionSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $companyId = BackendCommon::getFCompanyId();
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($searchModel->owner_type,$companyId);
        $searchModel->ownerTypeOptions = $ownerTypeOptions;
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
        GoodsScheduleCollectionService::operate($id,$commander,$company_id,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionModify() {
        $companyId = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        if (empty($id)) {
            $model = new GoodsScheduleCollection();
            $model->loadDefaultValues();
            $model->online_time = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong(time(),false));
            $model->offline_time = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong(time(),false));
            $model->display_start = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong(time(),false));
            $model->display_end = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong(time(),false));
        } else {
            $model = GoodsScheduleCollectionService::requireActiveModel($id,$companyId,RedirectParams::create("记录不存在",['delivery/index']),true);
        }
        $ownerTypeOptions = OwnerTypeService::getOptionsByOwnerType($model->owner_type,$companyId);
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $companyId;
                $model->operation_id = BackendCommon::getUserId();
                $model->operation_name = BackendCommon::getUserName();
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败".yii\helpers\Json::htmlEncode($model->errors),$this,'modify',[
                    'model' => $model,
                    'ownerTypeOptions'=>$ownerTypeOptions,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['goods-schedule-collection/index']);
            }
        }
        return $this->render("modify", [
            'model' => $model,
            'ownerTypeOptions'=>$ownerTypeOptions,
        ]);
    }

    public function actionImport(){
        $company_id = BackendCommon::getFCompanyId();
        $operatorId = BackendCommon::getUserId();
        $operatorName = BackendCommon::getUserName();
        $collectionId = Yii::$app->request->get("collection_id");
        BExceptionAssert::assertNotBlank($collectionId,RedirectParams::create("collection_id不能为空",Yii::$app->request->referrer));
        $goodsScheduleCollectionModel =  GoodsScheduleCollectionService::requireActiveModel($collectionId,$company_id,RedirectParams::create("记录不存在",['/goods-schedule-collection/index']),false);
        $model = new GoodsScheduleImportForm();
        if (Yii::$app->request->isPost){
            $model->load(Yii::$app->request->post());
            $model->collection_id = $collectionId;
            $model->file = UploadedFile::getInstance($model, 'file');
            if (!$model->validate()){
                return $this->render('import',['model' => $model,'goodsScheduleCollectionModel'=>$goodsScheduleCollectionModel]);
            }
            $file_path =  dirname(dirname(__FILE__)).'/web/uploads/excel/';
            if (!file_exists($file_path)){
                FileHelper::createDirectory($file_path);
            }
            $excel_file = $file_path.'schedule-'.$company_id.'-'.time().'.'.$model->file->getExtension();
            $model->file->saveAs($excel_file);
            $errorData = GoodsScheduleCollectionService::import($goodsScheduleCollectionModel,$model,$excel_file,$company_id,$operatorId,$operatorName);
            if (empty($errorData)){
                BackendCommon::showSuccessInfo('导入成功');
                return $this->redirect(['/goods-schedule/index','GoodsScheduleSearch[collection_id]'=>$model->collection_id]);
            }
            return $this->render('import-result',['errorData' => $errorData]);
        }
        return $this->render('import',['model' => $model,'goodsScheduleCollectionModel'=>$goodsScheduleCollectionModel]);
    }


    public function actionScheduleOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $ids = Yii::$app->request->get('ids')??[];
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        GoodsScheduleCollectionService::scheduleOperate($id,$commander,$company_id,$ids,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionScheduleText(){
        $company_id = BackendCommon::getFCompanyId();
        $id = Yii::$app->request->get("id");
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        $goodsScheduleCollectionModel =  GoodsScheduleCollectionService::requireActiveModel($id,$company_id,RedirectParams::create("记录不存在",['/goods-schedule-collection/index']),false);
        $scheduleGoodsText = GoodsScheduleCollectionService::getScheduleGoodsText($id,$company_id);
        return $this->render('schedule-text',[
            'goodsScheduleCollectionModel'=>$goodsScheduleCollectionModel,
            'scheduleGoodsText' => $scheduleGoodsText
        ]);
    }
}
