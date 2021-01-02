<?php

use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $bigSortArr */
/* @var common\models\GoodsSort $model */
$this->title = '商品分类信息保存';
$this->params['breadcrumbs'][] = ['label' => '商品分类列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">商品分类信息保存</h3>
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
                                    'contentBefore'=>'<legend class="text-info"><small>选择分类信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'sort_owner'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => GoodsSort::getSortOwnerArr(),
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => 'function() { 
                                                       $.get("/goods-sort/select-options?sort_owner="+$(this).val(),function(data){             
                                                            $("#goodssort-parent_id").html("<option value=>新的一级分类</option>").append(data).trigger("change");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'parent_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $bigSortArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                            ]
                                        ],

                                        'sort_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入菜单名称...'],'columnOptions'=>['colspan'=>2]],
                                        'sort_show' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsSort::$showStatusArr, 'placeholder' => '选择是否展示...', 'columnOptions' => ['colspan' => 2]],
                                        'sort_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入排序顺序...'],'columnOptions'=>['colspan'=>2]],
                                        'pic_name'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[

                                            ]
                                        ],
                                        'pic_icon'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                                
                                            ]
                                        ]
                                    ]
                                ],
//                                [
//                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
//                                    'columns'=>12,
//                                    'autoGenerateColumns'=>false, // override columns setting
//                                    'attributes'=>[       // 3 column layout
//                                        'sort_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入菜单名称...'],'columnOptions'=>['colspan'=>2]],
//                                        'pic_name'=>[   // radio list
//                                            'columnOptions'=>['colspan'=>2],
//                                            'type'=>Form::INPUT_WIDGET,
//                                            'widgetClass'=>'\manks\FileInput',
//                                            'options'=>[
//
//                                            ]
//                                        ],
//                                        'sort_show' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsSort::$showStatusArr, 'placeholder' => '选择是否展示...', 'columnOptions' => ['colspan' => 2]],
//                                        'sort_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入排序顺序...'],'columnOptions'=>['colspan'=>2]],
//                                    ]
//                                ],
                            ]
                        ]);
                        // echo $form->field($model,'pic_icon')->fileInput();
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-4 col-xs-1 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-1 col-xs-1 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>