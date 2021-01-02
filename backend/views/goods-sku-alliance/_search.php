<?php

use backend\models\BackendCommon;
use common\models\Alliance;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuAlliance;
use common\models\Payment;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\GoodsSkuAllianceSearch */
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
                    'id' => 'allianceSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'audit_status' => ['type' => Form::INPUT_HIDDEN],
                                'goods_id' => ['type' => Form::INPUT_HIDDEN],
                                'sku_id' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'goods_owner_type' => [
                                    'type' => Form::INPUT_DROPDOWN_LIST,
                                    'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                    'placeholder' => '选择类型...',
                                    'columnOptions' => ['colspan' => 2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'onchange'=>'
                                                $.get("/owner-type/options?owner_type="+$(this).val(),function(data){             
                                                    $("#goodsskualliancesearch-goods_owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                    ]
                                ],
                                'goods_owner_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>5],
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
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品名称...'], 'columnOptions' => ['colspan' => 3 ]],
                                'goods_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$typeArr), 'placeholder' => '选择商品类型...', 'columnOptions' => ['colspan' => 3]],
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
