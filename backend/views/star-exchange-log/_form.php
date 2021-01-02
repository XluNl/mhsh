<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StarExchangeLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="star-exchange-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'trade_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'exchange_time')->textInput() ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'biz_type')->textInput() ?>

    <?= $form->field($model, 'biz_id')->textInput() ?>

    <?= $form->field($model, 'balance_id')->textInput() ?>

    <?= $form->field($model, 'balance_log_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
