<?php

use backend\utils\BootstrapFileInputConfigUtil;
use yiichina\icheck\ICheck;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $bigSortArr */
/* @var array $smallSortArr */
/* @var common\models\Goods $model */
$this->title = '保存商品信息';
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->goods_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;


?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">商品信息修改</h3>
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
                                    'contentBefore'=>'<legend class="text-info"><small>填写分类信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'goods_owner'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\yiichina\icheck\ICheck',
                                            'options'=>[
                                                'type' => \yiichina\icheck\ICheck::TYPE_RADIO_LIST,
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
                                                'items' => GoodsConstantEnum::$ownerArr,
                                            ]
                                        ],
                                        'sort_1' => [
                                            'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' => $bigSortArr,
                                            'placeholder' => '选择商品大类...',
                                            'columnOptions' => ['colspan' => 2],
                                            'options'=>[
                                                'style'=>'display:inline',
                                                'onchange'=>'
                                            $.get("/goods-sort/select-options?parent_id="+$(this).val()+"sort_owner="+$("input[name=\'GoodsSearch[goods_owner]\']:checked").val(),function(data){             
                                                $("#goods-sort_2").html("<option value=>请选择</option>");
                                                $("#goods-sort_2").append(data);
                                            });'
                                            ]
                                        ],
                                        'sort_2' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>$smallSortArr, 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 2]],
                                        'goods_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsConstantEnum::$typeArr, 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 2]],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'goods_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入商品名称...'],'columnOptions'=>['colspan'=>2]],
                                        'goods_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsConstantEnum::$statusListArr, 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 2]],
                                        'display_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'从大到小排列，默认0'],'columnOptions'=>['colspan'=>2]],
                                        'goods_img'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[

                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'goods_images'=>[   // radio list
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                                'clientOptions' => [
                                                    'pick' => [
                                                        'multiple' => true,
                                                    ],
                                                ],
                                            ]
                                        ]
                                    ]
                                ],

//                                [
//                                    'columns'=>12,
//                                    'autoGenerateColumns'=>false, // override columns setting
//                                    'attributes'=>[       // 3 column layout
//                                        'goods_img'=>[   // radio list
//                                            'columnOptions'=>['colspan'=>2],
//                                            'type'=>Form::INPUT_WIDGET,
//                                            'widgetClass'=>'\manks\FileInput',
//                                            'options'=>[
//
//                                            ]
//                                        ],
//                                        'goods_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsConstantEnum::$statusListArr, 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 2]],
//                                        'display_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'从大到小排列，默认0'],'columnOptions'=>['colspan'=>2]],
//                                    ]
//                                ],
                            ]
                        ]);

                        ?>

                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-4 col-xs-1 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-1 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->beginBlock('js_end_1') ?>
$("#goods-goods_owner").on('ifChanged', function(event){
    $.get("/goods-sort/select-options?parent_id=0&sort_owner="+$("input[name='Goods[goods_owner]']:checked").val(),function(data){
        $("#goods-sort_1").html("<option value=>请选择</option>");
        $("#goods-sort_2").html("<option value=>请选择</option>");
        $("#goods-sort_1").append(data);
    });
});
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end_1'], \yii\web\View::POS_READY); ?>