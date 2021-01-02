<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\StorageBindService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use common\models\StorageBind;
use common\utils\ArrayUtils;
use Yii;

/**
 * StorageBindController implements the CRUD actions for StorageBind model.
 */
class StorageBindController extends BaseController
{
    public function actionIndex()
    {
        $companyId = BackendCommon::getFCompanyId();
        $model = StorageBindService::getModel($companyId);
        if ($model==null){
            return $this->redirect(['modify']);
        }
        else{
            return $this->redirect(['view']);
        }
    }

    public function actionModify() {
        $companyId = BackendCommon::getFCompanyId();
        $model = StorageBindService::getModel($companyId,true);
        BExceptionAssert::assertNull($model,RedirectParams::create("您已绑定，不能重复绑定",['storage-bind/view']));
        $model = new StorageBind();
        $model->storageArr = StorageBindService::getStorageSelect(RedirectParams::create("下游失败",['/site/index']));
        if (Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                $model->company_id = $companyId;
                $model->operator_id = BackendCommon::getUserId();
                $model->operator_name = BackendCommon::getUserName();
                BExceptionAssert::assertTrue(key_exists($model->storage_id,$model->storageArr),RenderParams::create('非法仓库',$this,'modify',[
                    'model' => $model,
                ]));
                $model->storage_name = ArrayUtils::getArrayValue($model->storage_id,$model->storageArr);
                list($result,$error) = StorageBindService::bind($model);
                BExceptionAssert::assertTrue($result,RenderParams::create($error,$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("绑定成功");
                return $this->redirect(['view']);
            }
        }
        return $this->render('modify', [
            'model' => $model
        ]);
    }


    public function actionView()
    {
        $companyId = BackendCommon::getFCompanyId();
        $model = StorageBindService::getModel($companyId,true);
        BExceptionAssert::assertNotNull($model,RedirectParams::create("您还没绑定仓库，请先绑定",['storage-bind/modify']));
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
