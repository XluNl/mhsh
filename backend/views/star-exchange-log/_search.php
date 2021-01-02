<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\StarExchangeLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="star-exchange-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'created_at') ?>

    <?= $form->field($model, 'updated_at') ?>

    <?= $form->field($model, 'trade_no') ?>

    <?= $form->field($model, 'exchange_time') ?>

    <?php // echo $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'amount') ?>

    <?php // echo $form->field($model, 'biz_type') ?>

    <?php // echo $form->field($model, 'biz_id') ?>

    <?php // echo $form->field($model, 'balance_id') ?>

    <?php // echo $form->field($model, 'balance_log_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
