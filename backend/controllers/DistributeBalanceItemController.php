<?php
namespace backend\controllers;
use backend\models\searches\DistributeBalanceItemSearch;
use Yii;

class DistributeBalanceItemController extends BaseController {

    public function actionIndex(){
        $searchModel = new DistributeBalanceItemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
