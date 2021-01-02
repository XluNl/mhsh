<?php

namespace mdm\admin\controllers;

use backend\controllers\BaseController;
use backend\models\forms\LoginForm;
use backend\models\forms\PasswordResetRequestForm;
use business\models\ResetPasswordForm;
use common\models\AdminUser;
use Yii;
use yii\base\InvalidParamException;
use yii\mail\BaseMailer;
use yii\web\BadRequestHttpException;
/**
 * User controller
 */
class UserController extends BaseController
{
    private $_oldMailPath;

    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'backColor'=>0x00c0ef,//背景颜色
                'maxLength' => 5, //最大显示个数
                'minLength' => 5,//最少显示个数
                'padding' => 5,//间距
                'height'=>40,//高度
                'width' => 100,  //宽度
                'foreColor'=>0xffffff,     //字体颜色
                'offset'=>4,        //设置字符偏移量 有效果
                //'controller'=>'/admin/user',        //拥有这个动作的controller
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {

        if (parent::beforeAction($action)) {
            if (Yii::$app->has('mailer') && ($mailer = Yii::$app->getMailer()) instanceof BaseMailer) {
                /* @var $mailer BaseMailer */
                $this->_oldMailPath = $mailer->getViewPath();
                $mailer->setViewPath('@mdm/admin/mail');
            }
            return true;

        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        if ($this->_oldMailPath !== null) {
            Yii::$app->getMailer()->setViewPath($this->_oldMailPath);
        }
        return parent::afterAction($action, $result);
    }

    /**
     * Login
     * @return string
     */
    public function actionLogin()
    {
        $this->setReferer();

        if (!Yii::$app->getUser()->isGuest) {
            if (($type = Yii::$app->getRequest()->get('type')) && $type == 'auth') {
                return $this->authCallback();
            } else {
                return $this->goHome();
            }
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->login()) {
            if (($type = Yii::$app->getRequest()->get('type')) && $type == 'auth') {
                return $this->authCallback();
            } else {
                return $this->goHome();
            }
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


    /**
     * 记录来源网址
     */
    private function setReferer()
    {
        $currentRoute = $this->getRoute();
        $referer = Yii::$app->getRequest()->getReferrer();
        if(strripos($referer, $currentRoute) === false){
            Yii::$app->user->setReturnUrl($referer);
        }
    }
    /**
     * 此方法被调用的前台是用户已经是登录状态了
     * 授权后的回调函数
     */
    private function authCallback()
    {

        if ($user = AdminUser::findOne(Yii::$app->user->id)) {
            $returnUrl = Yii::$app->getUser()->getReturnUrl(null);
            $authUrl = Yii::$app->getRequest()->get('r');
            /*$user->generateAuthKey();
            if (!$user->save()) {
                Yii::$app->session->setFlash('login-error','授权登录失败，暂时无法获取ticket');
                return false;
            }*/
            $authUrl .= "?r=".$returnUrl;

            return $this->redirect($authUrl);
        }
        die('<script>alert("授权出错！");window.history.go(-1);</script>');
    }

    /**
     * Logout
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->getUser()->logout();

        return $this->goHome();
    }


    /**
     * Request reset password
     * @return string
     */
    public function actionRequestPasswordReset()
    {
        $this->layout = "@app/views/layouts/main-login";
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', '请前往你的电子邮件查看进一步说明。');
                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', '对不起，我们无法为改电子邮箱重置密码。');
            }
        }

        return $this->render('requestPasswordResetToken', [
                'model' => $model,
        ]);
    }

    /**
     * Reset password
     * @return string
     */
    public function actionResetPassword($token)
    {
        $this->layout = "@app/views/layouts/main-login";
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', '新密码已设置成功');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
                'model' => $model,
        ]);
    }

}
