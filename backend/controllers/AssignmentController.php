<?php

namespace backend\controllers;

use backend\models\BackendCommon;
use backend\services\AdminUserService;
use backend\utils\params\RedirectParams;
use mdm\admin\models\Assignment;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * AssignmentController implements the CRUD actions for Assignment model.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AssignmentController extends BaseController
{
    public $userClassName;
    public $idField = 'id';
    public $usernameField = 'username';
    public $fullnameField;
    public $searchClass;
    public $extraColumns = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->userClassName === null) {
            $this->userClassName = Yii::$app->getUser()->identityClass;
            $this->userClassName = $this->userClassName ? : 'mdm\admin\models\User';
        }
    }

    /**
     * Lists all Assignment models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Displays a single Assignment model.
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $companyId = BackendCommon::getFCompanyId();
        $user = AdminUserService::requireModel($id,$companyId,RedirectParams::create("用户不存在",Yii::$app->request->referrer),true);
        $model = $this->findModel($id);
        $items = $model->getItems();
        $assign = array_keys($items['assigned']);
        $available = array_keys($items['available']);
        $all = ArrayHelper::merge($assign, $available);
        $exclude = Yii::$app->params['role.exclude'];
        foreach ($all as $key=>$val){
            if (in_array($val, $exclude)) {
                unset($all[$key]);
            }
        }
        return $this->render('view', [
                'assign' => $assign,
                'all' => $all,
                'id' => $model->{$this->idField},
                'name' => $model->{$this->usernameField},
        ]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->assign($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems()['assigned'], ['success' => $success]);
        //return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionRevoke($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = new Assignment($id);
        $success = $model->revoke($items);
        Yii::$app->getResponse()->format = 'json';
        return array_merge($model->getItems()['assigned'], ['success' => $success]);
        //return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Finds the Assignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer $id
     * @return Assignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $class = $this->userClassName;
        if (($user = $class::findIdentity($id)) !== null) {
            return new Assignment($id, $user);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
