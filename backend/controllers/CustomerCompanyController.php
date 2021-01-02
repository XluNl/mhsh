<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\models\searches\CustomerCompanySearch;
use backend\services\RegionService;
use backend\services\UserInfoService;
use common\models\CustomerCompany;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CustomerCompanyController implements the CRUD actions for CustomerCompany model.
 */
class CustomerCompanyController extends Controller
{
    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        return [
//            'verbs' => [
//                'class' => VerbFilter::className(),
//                'actions' => [
//                    'delete' => ['POST'],
//                ],
//            ],
//        ];
//    }

    /**
     * Lists all CustomerCompany models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CustomerCompanySearch();
        BackendCommon::addCompanyIdToParams('CustomerCompanySearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
        UserInfoService::completeCustomerInfo($dataProvider);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionLog()
    {
        $searchModel = new CustomerCompanySearch();
//        BackendCommon::addCompanyIdToParams('CustomerCompanySearch');
        $dataProvider = $searchModel->logSearch(Yii::$app->request->queryParams);
//        RegionService::batchSetProvinceAndCityAndCountyForDataProvider($dataProvider);
//        UserInfoService::completeCustomerInfo($dataProvider);
        return $this->render('log', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CustomerCompany model.
     * @param integer $id
     * @return mixed
     */
//    public function actionView($id)
//    {
//        return $this->render('view', [
//            'model' => $this->findModel($id),
//        ]);
//    }

    /**
     * Creates a new CustomerCompany model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
//    public function actionCreate()
//    {
//        $model = new CustomerCompany();
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('create', [
//                'model' => $model,
//            ]);
//        }
//    }

    /**
     * Updates an existing CustomerCompany model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionUpdate($id)
//    {
//        $model = $this->findModel($id);
//
//        if ($model->load(Yii::$app->request->post()) && $model->save()) {
//            return $this->redirect(['view', 'id' => $model->id]);
//        } else {
//            return $this->render('update', [
//                'model' => $model,
//            ]);
//        }
//    }

    /**
     * Deletes an existing CustomerCompany model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the CustomerCompany model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CustomerCompany the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CustomerCompany::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
