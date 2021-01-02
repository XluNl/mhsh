<?php

use backend\models\forms\ChangePasswordForm;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model ChangePasswordForm */

$title = "密码修改";
$this->params['subtitle'] =$title;
$this->params['breadcrumbs'] = [];
$this->params['breadcrumbs'][] = $title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                </div>
                <div class="box-body">
                    <?php $form = ActiveForm::begin([
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
                    <?= $form->field($model, 'oldPassword')->passwordInput() ?>
                    <?= $form->field($model, 'newPassword')->passwordInput() ?>
                    <?= $form->field($model, 'retypePassword')->passwordInput() ?>
                    <div class="form-group">
                        <?= \yii\bootstrap\Html::submitButton('修改', ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-primary btn-lg ']) ?>
                        <?= Html::a('返回', ['/'], ['class' => 'col-xs-4 btn   btn-warning btn-lg']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

</div>
