<?php
namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\CompanyService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use Yii;

/**
 * Selfcompany controller
 */
class SelfcompanyController extends BaseController
{
    
 

    public function actionIndex()
    {
        return $this->redirect('modify');
    }
    
 
    
    public function actionModify()
    {
        $company_id = \Yii::$app->user->identity->company_id;
        $model = CompanyService::getActiveModel($company_id,true);
        BExceptionAssert::assertNotNull($model,RedirectParams::create("公司信息不存在，请联系客服",['/']));
        if (Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())) {
                BExceptionAssert::assertTrue($model->save(),RenderParams::create("保存失败",$this,'modify',[
                    'model' => $model,
                ]));
                BackendCommon::showSuccessInfo("保存成功");
                return $this->redirect(['selfcompany/modify']);
            }
        }
        return $this->render('modify', [
            'model' => $model 
        ]);
    }
 
}
