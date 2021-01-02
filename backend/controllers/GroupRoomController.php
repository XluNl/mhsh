<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use Yii;
use backend\models\searches\GroupRoomSearch;
use yii\web\Controller;

/**
 * GroupRoomController implements the CRUD actions for GroupRoom model.
 */
class GroupRoomController extends Controller
{
    /**
     * Lists all GroupRoom models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new GroupRoomSearch();
        BackendCommon::addCompanyIdToParams('GroupRoomSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
