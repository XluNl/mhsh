<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\DeliveryCommentSearch;
use backend\services\DeliveryCommentService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use Yii;

class DeliveryCommentController extends BaseController {

    public function actionIndex(){
        $searchModel = new DeliveryCommentSearch();
        BackendCommon::addCompanyIdToParams('DeliveryCommentSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = DeliveryCommentService::renameImages($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $userId = BackendCommon::getUserId();
        $username = BackendCommon::getUserName();
        BExceptionAssert::assertNotBlank($id,RedirectParams::create("id不存在",Yii::$app->request->referrer));
        BExceptionAssert::assertNotBlank($commander,RedirectParams::create("操作命令不存在",Yii::$app->request->referrer));
        DeliveryCommentService::operate($id,$commander,$company_id,$userId,$username,RedirectParams::create("操作失败",Yii::$app->request->referrer));
        BackendCommon::showSuccessInfo("操作成功");
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionShowOperation(){
        $company_id = BackendCommon::getFCompanyId();
        $commander = Yii::$app->request->get('commander');
        $id = Yii::$app->request->get("id");
        $userId = BackendCommon::getUserId();
        $username = BackendCommon::getUserName();
        try{
            BExceptionAssert::assertNotBlank($id,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'id'));
            BExceptionAssert::assertNotBlank($commander,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_MISS,'commander'));
            DeliveryCommentService::showOperate($id,$commander,$company_id,$userId,$username,BBusinessException::create("操作失败"));
            return BRestfulResponse::success(true);
        }
        catch (BBusinessException $e){
            return BRestfulResponse::error($e);
        }
        catch (\Exception $e){
            return BRestfulResponse::error($e);
        }
    }


}
