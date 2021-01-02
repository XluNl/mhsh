<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \mdm\admin\models\form\ResetPassword */

$this->title = '重置密码';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .login-page{
        background: #65cea7 url('<?php echo Url::toRoute("/login-bg.jpg")?>') no-repeat fixed;
        background-size: cover;
        width: 100%;
        height: 100%;
        margin-top: 60px;
    }
    .login{
        width: 24%;
        text-align: center;
        background: #fff;
        -webkit-border-radius:5px;
        margin:0 auto;
        padding-bottom: 20px;
    }
    .login-body{
        margin-left:30px;
        margin-right: 30px;
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
</style>
<div class="site-login">
    <h3 style="text-align: center;color: #fff"><?= Html::encode($this->title) ?></h3>
    <div class="login">
        <div class="login-header">
            <img src=<?php echo Url::toRoute("/logo.png")?> alt="">
        </div>
        <div class="login-body">
            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'retypePassword')->passwordInput() ?>
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('rbac-admin', '保存'), ['class' => 'btn btn-primary']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

