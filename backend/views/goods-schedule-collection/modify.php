<?php

use backend\models\BackendCommon;
use common\models\Coupon;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $sortArr */
/* @var array $goodsArr */
/* @var array $skusArr */
/* @var common\models\GoodsScheduleCollection $model */
/* @var array $ownerTypeOptions */
$this->title = '保存排期活动信息';
$this->params['breadcrumbs'][] = ['label' => '排期活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">排期活动修改</h3>
                    </div>
                    <div class="box-body">
                        <?php $form = ActiveForm::begin();
                        echo FormGrid::widget([
                            'model'=>$model,
                            'form'=>$form,
                            'autoGenerateColumns'=>true,
                            //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                            'rows'=>[
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'collection_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动名称...'],'columnOptions'=>['colspan'=>6]],
                                        'owner_type' => [
                                            'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                            'placeholder' => '选择类型...',
                                            'columnOptions' => ['colspan' => 2],
                                            'options'=>[
                                                'style'=>'display:inline',
                                                'onchange'=>'
                                                $.get("/owner-type/options?owner_type="+$(this).val(),function(data){             
                                                    $("#goodsschedulecollection-owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                            ]
                                        ],
                                        'owner_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => BackendCommon::addBlankOption($ownerTypeOptions),
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'theme'=> Select2::THEME_BOOTSTRAP,
                                                'options' => ['placeholder' => '选择类型...'],
                                                'pluginOptions' => [
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'display_start'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'display_end'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示结束时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'online_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择有效期起始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'offline_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择有效期截止时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-2 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-3 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>