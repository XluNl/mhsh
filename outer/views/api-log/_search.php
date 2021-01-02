<?php

use backend\models\BackendCommon;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use common\models\ApiLog;
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row" style="display: flex;align-items: center;">
            <div class="col-md-9" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'routeSearchForm',
                    'class' =>['col-md-offset-1 col-md-12']
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            // 'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [
                                'env' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(ApiLog::$typeArrText), 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 2]],
                                'app_id'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入应用标识...'],'columnOptions'=>['colspan'=>2]],
                                'module'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入模块...'],'columnOptions'=>['colspan'=>2]],
                                'controller'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入控制...'],'columnOptions'=>['colspan'=>3]],
                                'action'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入操作...'],'columnOptions'=>['colspan'=>3]],
                            ]
                        ],
                    ]
                ]);
                ?>

            </div>
            <div class="col-md-3" style="display: flex;flex-direction: row-reverse;">
                <div class="">
                    <?= Html::submitButton('查询', ['class' => 'btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>