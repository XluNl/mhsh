<?php

use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $goodsArr */
/* @var array $goodsSkuArr */
/* @var array $scheduleDisplayChannelArr */
/* @var common\models\GoodsSchedule $model */
$this->title = '保存商品排期信息';
$this->params['breadcrumbs'][] = ['label' => '排期列表', 'url' => ['/goods-schedule-collection/index']];
$this->params['breadcrumbs'][] = ['label' => '商品排期列表', 'url' => ['index','GoodsScheduleSearch[collection_id]'=>$model->collection_id]];
$this->params['breadcrumbs'][] = $this->title;

$select2Options = [];
if (StringUtils::isNotBlank($model->id)){
    $select2Options = ['disabled' => 'disabled'];
}

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">商品排期信息修改</h3>
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
                                    'attributes' => [       // 3 column layout
                                        'collection_id' => ['type' => Form::INPUT_HIDDEN],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>选择分类信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'owner_type'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => GoodsConstantEnum::$ownerArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                'options' => array_merge([],$select2Options),
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => 'function() { 
                                                       $.get("/goods-schedule/goods-options?goods_owner="+$(this).val(),function(data){             
                                                            $("#goodsschedule-goods_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                            $("#goodsschedule-sku_id").html("<option value=>请选择</option>").trigger("change");
                                                            $("#goodsschedule-schedule_display_channel").html("<option value=>请选择</option>").trigger("change");
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
                                                'data' => $goodsArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                'options' => array_merge([],$select2Options),
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => 'function() { 
                                                        $("#goodsschedule-schedule_display_channel").html("<option value=>请选择</option>").trigger("change");
                                                       $.get("/goods-schedule/goods-sku-options?goods_id="+$(this).val(),function(data){             
                                                            $("#goodsschedule-sku_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                       });
                                                       $.get("/goods-schedule/schedule-display-channel-options?goods_id="+$(this).val(),function(data){             
                                                            $("#goodsschedule-schedule_display_channel").html("<option value=>请选择</option>").append(data).trigger("change");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'sku_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $goodsSkuArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                'options' => array_merge([],$select2Options),
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                    'autoclose'
                                                ],
                                            ]
                                        ],
                                        'schedule_display_channel'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $scheduleDisplayChannelArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'schedule_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入排期名称...'],'columnOptions'=>['colspan'=>2]],
                                        'price'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动价格...'],'columnOptions'=>['colspan'=>2]],
                                        'schedule_stock'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动库存...'],'columnOptions'=>['colspan'=>2]],
                                        'schedule_limit_quantity'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入限购数量...'],'columnOptions'=>['colspan'=>2]],
                                        'display_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入排序顺序...'],'columnOptions'=>['colspan'=>2]],
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
                                    'autoGenerateColumns'=>true, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'expect_arrive_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择预计送达时间','readonly'=>true],
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
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'validity_start'=>[
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
                                        'validity_end'=>[
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
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>仓库映射关系</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'actions'=>['type'=>Form::INPUT_RAW, 'value'=>function ($model){
                                            if (StringUtils::isNotBlank($model->id)){
                                                if ($model->hasStorageMapping()){
                                                    return Html::tag("label","仓库商品:{$model['storage_sku_id']}(1:{$model['storage_sku_num']})",['class'=>'label label-success']);
                                                }
                                                else{
                                                    return Html::tag("label",'未绑定仓库商品，请重新创建排期',['class'=>'label label-warning']);
                                                }
                                            }
                                            return "";
                                        }],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回',['/goods-schedule/index','GoodsScheduleSearch[collection_id]' => $model->collection_id], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
