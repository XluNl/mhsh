<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'orderSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'realname' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入姓名...'], 'columnOptions' => ['colspan' => 2]],
                                'community' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入小区名称...'], 'columnOptions' => ['colspan' => 2]],
                                'phone' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入手机号...'], 'columnOptions' => ['colspan' => 2]],
                                'nickname' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入昵称...'], 'columnOptions' => ['colspan' => 2]],
                                'em_phone' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入紧急手机号...'], 'columnOptions' => ['colspan' => 2]],
                                'wx_number' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入微信号...'], 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
//                        [
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'nickname' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入昵称...'], 'columnOptions' => ['colspan' => 3]],
//                                'em_phone' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入紧急手机号...'], 'columnOptions' => ['colspan' => 3]],
//                                'wx_number' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入微信号...'], 'columnOptions' => ['colspan' => 3]],
//                            ]
//                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-12" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-9 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
