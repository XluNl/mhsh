<?php

use backend\models\BackendCommon;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\GroupActiveSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
?>
<style>

</style>
<div class="box box-success" style="margin-top: 20px">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'orderSearchForm',
                ]);
                echo FormGrid::widget([
                    'model'=>$model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    'rows' => [
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [
                                'owner_type'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                       // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                        'pluginEvents' => [
                                            "change" => 'function() { 
                                                $.get("/goods-schedule/goods-options?goods_owner="+$(this).val(),function(data){             
                                                      $("#grouproomordersearch-goods_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                });         
                                            }',
                                        ],
                                    ]
                                ],
                                'goods_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->goodsOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                       // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                    ]
                                ],
                                'start_time'=>[
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '活动开始时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:00',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'end_time'=>[
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '活动结束时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:59',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]);
                ?>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="form-group" style="display: flex;justify-content:flex-end;">
                        <?= Html::submitButton('查询', ['class' => 'col-xs-offset-4 btn btn-primary','style'=>'margin-right: 10px;']) ?>
                        <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
