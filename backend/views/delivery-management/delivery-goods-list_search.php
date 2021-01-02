<?php

use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use backend\models\BackendCommon;
use yiichina\icheck\ICheck;

/* @var $this yii\web\View */
/* @var $model backend\models\forms\DeliveryGoodsListSearchForms */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-11">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'method' => 'get',
                    'id' => 'orderDeliverySearchForms',
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
                                'owner_type'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\yiichina\icheck\ICheck',
                                    'options'=>[
                                        'type' => ICheck::TYPE_RADIO_LIST,
                                        'skin' => ICheck::SKIN_SQUARE,
                                        'color' => ICheck::COLOR_GREEN,
                                        'clientOptions'=>[
                                            'labelHover'=>false,
                                            'cursor'=>true,
                                        ],
                                        'options'=>[
                                            'class'=>'label-group',
                                            'separator'=>'',
                                            'template'=>'<span class="check">{input}{label}</span>',
                                            'labelOptions'=>['style'=>'display:inline']
                                        ],
                                        'model' => $model,
                                        'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                    ]
                                ],
                                'expect_arrive_time'=>[
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'removeButton' => false,
                                        'options' => ['placeholder' => '预计送达时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'minView'=>'month',
                                            'autoclose' => true
                                        ]
                                    ]
                                ],
                                'delivery_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>5],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->deliveryOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'options' => ['placeholder' => '选择配送团点...'],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ]
                                ],
                            ]
                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-10">
                <blockquote class="blockquote-reverse">
                    <div class="form-group">
                        <?= Html::submitButton('查询', ['class' => 'col-xs-offset-10 btn btn-success']) ?>
                        <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                    </div>
                </blockquote>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>