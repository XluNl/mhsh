<?php
namespace backend\controllers;
use Yii;
use backend\models\searches\ApiLogSearch;
/**
 * 
 */
class ApiLogController extends BaseController
{
	public function actionIndex(){
		$searchModel = new ApiLogSearch();
		$params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'params' => $params
        ]);
	}

	public function actionClear(){
		ApiLogSearch::delExp(Yii::$app->request->queryParams);
		return $this->redirect(Yii::$app->request->referrer);
	}
}