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
/* @var $model backend\models\searches\OrderSearch */
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
                                'use_limit_type' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'coupon_type'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption(CouponBatch::$couponType),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'options' => ['placeholder' => '选择类型...'],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ]
                                ],
                                'owner_type' => [
                                    'type' => Form::INPUT_DROPDOWN_LIST,
                                    'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                    'placeholder' => '选择类型...',
                                    'columnOptions' => ['colspan' => 2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'onchange'=>'
                                                $.get("/owner-type/options?owner_type="+$(this).val(),function(data){             
                                                    $("#couponbatchsearch-owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
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
                                'name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入优惠券活动名称...'], 'columnOptions' => ['colspan' => 2    ]],
                                'batch_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入优惠券批次编号...'], 'columnOptions' => ['colspan' =>2    ]],
                                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Coupon::$typeDisplayArr), 'placeholder' => '选择优惠券类型...', 'columnOptions' => ['colspan' =>2]],
                            ]
                        ],
                    ]
                ]);
                ?>

            </div>
            <div class="col-md-offset-1 col-md-12" >
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-7 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
