<?php

use backend\models\BackendCommon;
use backend\models\forms\LoginForm;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\captcha\Captcha;
backend\assets\AppAsset::register($this);
use backend\assets\ICheckAsset;
ICheckAsset::register($this);
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model LoginForm */

?>
<style>
    .login-page{
        background: #65cea7 url('<?php echo BackendCommon::generateAbsoluteUrl("/backend-login.png")?>') no-repeat fixed;
        background-size: cover;
        width: 100%;
        height: 100%;
        margin-top: 60px;
    }
    .login{
        height: 456px;
        text-align: center;
        background: #fff;
        -webkit-border-radius:5px;
        padding-top: 40px;
        padding-bottom: 20px;
        background: rgba(251, 251, 251, 0.6);
        position: fixed;
        top: 20%;
    }
    .login-header{
        /*display: inline-block;*/
        height: 180px;
        padding: 3px;
    }
    .remember{
        color:#999;
        margin:1em 0;
    }
    #loginform-verifycode-image{
        position: absolute;
        z-index: 9;
        height: 34px;
        right: 15px;
    }
    #loginform-verifycode{
        position: relative;
    }
    .btn{
        width: 80%;
        margin-left: 10%;
        margin-right: 10%;
    }
    h1{
        position: fixed;
        width: 100%;
        text-align: center;
        bottom: 50px;
        letter-spacing: 12px;
        color: #fff;
    }
</style>
<div class="site-login">    
    <div class="login col-lg-4 col-lg-offset-4 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 col-xs-12">
        <div class="login-body">
            <?php $form = ActiveForm::begin(['id' => 'login-form',
                'options'=>['enctype'=>'multipart/form-data','class' => 'form-horizontal'],
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                    'horizontalCssClasses' => [
                        'label' => 'col-lg-2',
                        'offset' => 'col-lg-offset-2',
                        'wrapper' => 'col-lg-9',
                        'error' => '',
                        'hint' => '',
                    ],
                ],
                
            ]); ?>
            <?= $form->field($model, 'username') ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'verifyCode')->widget(
                    Captcha::className(),
                    [
                        'captchaAction'=>'/admin/user/captcha',
                        'imageOptions'=>['alt'=>'点击换图','title'=>'点击换图', 'style'=>'cursor:pointer']
                    ]
            ); ?>
            <?= $form->field($model,'rememberMe')->checkbox([
                'id'=>'remember-me',
                'template'=>'<div class="remember">{input}<label for="remember-me"> &nbsp;7天内自动登录(公共场合请勿勾选)</label></div>'
            ])?>
            <div style="color:#474747;margin:1em 0">
                忘记密码？ <?= Html::a('重置', ['user/request-password-reset']) ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton('登录', ['class' => 'btn btn-info btn-block', 'name' => 'login-button']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <h1><?php echo Yii::$app->name?></h1>
</div>
<script type="text/javascript">
$(document).ready(function(){
$('input[type=checkbox]').iCheck({
    checkboxClass: 'icheckbox_flat-blue',
    radioClass: 'iradio_flat-blue',
    increaseArea: '10%' // optional
  });
});
</script>