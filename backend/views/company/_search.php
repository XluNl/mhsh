<?php

use backend\models\BackendCommon;
use common\models\CommonStatus;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\CompanySearch */
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
                                'name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入名称...'], 'columnOptions' => ['colspan' => 3 ]],
                                'contact' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入联系人...'], 'columnOptions' => ['colspan' => 3 ]],
                                'telphone' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入手机号...'], 'columnOptions' => ['colspan' => 3 ]],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'address' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入地址...'], 'columnOptions' => ['colspan' => 5 ]],
                                'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CommonStatus::$StatusArr), 'placeholder' => '选择状态...', 'columnOptions' => ['colspan' => 3]],
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