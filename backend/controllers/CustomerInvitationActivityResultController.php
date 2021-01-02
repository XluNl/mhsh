<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\CustomerInvitationActivityResultSearch;
use yii;

/**
 * CustomerInvitationActivityResult   controller
 */
class CustomerInvitationActivityResultController extends BaseController {

    public function actionIndex(){
        $searchModel = new CustomerInvitationActivityResultSearch();
        BackendCommon::addCompanyIdToParams('CustomerInvitationActivityResultSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


}
