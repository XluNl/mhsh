<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\CustomerSearch;
use backend\services\RegionService;
use common\models\Customer;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * CustomerController implements the CRUD actions for Customer model.
 */
class CustomerController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Customer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    /**
     * Updates an existing Customer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionUpdate()
    {
        $id = Yii::$app->request->get("id");
        $model = new Customer();
        if (!empty($id)){
            $model = Customer::find()->where(['id'=>$id])->one();
            if (empty($model)){
                Yii::$app->session->setFlash('error', '此ID不存在');
                return $this->redirect(['index']);
            }
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()){
                Yii::$app->session->setFlash('success', '保存成功');
                return $this->redirect(['index']);
            }
            else{
                Yii::$app->session->setFlash('error', '保存失败');
            }
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Customer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $companyId = BackendCommon::getFCompanyId();
        Customer::deleteAll(['id'=>$id,'company_id'=>$companyId]);
        BackendCommon::showSuccessInfo('删除成功');
        return $this->redirect(['index']);
    }

}
