<?php

use backend\models\BackendCommon;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\GoodsScheduleCollectionSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 "  >
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'goodsScheduleCollectionSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
//                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'collection_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入排期名称...'], 'columnOptions' => ['colspan' => 4]],
                                'owner_type' => [
                                    'type' => Form::INPUT_DROPDOWN_LIST,
                                    'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                    'placeholder' => '选择类型...',
                                    'columnOptions' => ['colspan' => 2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'onchange'=>'
                                                $.get("/owner-type/options?owner_type="+$(this).val(),function(data){             
                                                    $("#goodsschedulecollectionsearch-owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                    ]
                                ],
                                'owner_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->ownerTypeOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'options' => ['placeholder' => '选择类型...'],
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
            <div class="col-md-offset-9 col-md-3" >
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-4 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
