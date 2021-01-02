<?php

namespace backend\controllers;
use common\models\AdminUserLog;
use yii\web\Response;
use Yii;
use yii\web\Controller;
/**
 * Base controller
 */
class BaseController extends Controller
{
    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            if (!\Yii::$app->getUser()->isGuest) {
                return AdminUserLog::saveLog();
            } else {
                if (\Yii::$app->controller->module->id=='admin'&&\Yii::$app->controller->id=='user'&&in_array(\Yii::$app->controller->action->id,['login','request-password-reset','reset-password','captcha'])){
                    return true;
                }
                $this->redirect(['/admin/user/login']);
                return false;
            }
        }
    }
}
