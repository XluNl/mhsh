<?php

use backend\models\BackendCommon;
use common\models\AdminUser;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\AdminUserSearch */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'companySearchForm',
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
                                'company_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>5],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->companyOptions),
                                        'language' => 'zh-CN',
                                        'size' => Select2::SMALL,
                                        // 'options' => ['placeholder' => 'Select a state ...'],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                        'pluginEvents' => [

                                        ],
                                    ]
                                ],
                                'username' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入登录名...'], 'columnOptions' => ['colspan' => 3 ]],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'nickname' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入昵称...'], 'columnOptions' => ['colspan' => 3 ]],
                                'email' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入Email...'], 'columnOptions' => ['colspan' => 3 ]],
                                'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(AdminUser::$status_arr), 'placeholder' => '选择状态...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
                    ]
                ]);
                ?>
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-8 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>