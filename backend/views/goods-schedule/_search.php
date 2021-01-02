<?php

use backend\models\BackendCommon;
use common\models\GoodsSchedule;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\GoodsScheduleSearch */
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
                    'id' => 'orderSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'schedule_display_channel' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'schedule_date'=>[   // radio list
                                    'columnOptions'=>['colspan'=>4],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '查询指定日期排期','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'minView'=>'month',
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                               // 'schedule_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入排期名称...'], 'columnOptions' => ['colspan' => 3    ]],
                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品名称...'], 'columnOptions' => ['colspan' => 4    ]],
                                'recommend' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(GoodsSchedule::$isRecommendArr), 'placeholder' => '选择推荐状态...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
                    ]
                ]);
                ?>

            </div>
            <div class="col-md-offset-1 col-md-10">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-6 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
