<?php

use common\models\Goods;
use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use \backend\models\BackendCommon;
use yiichina\icheck\ICheck;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\GoodsSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var  array $bigSortArr */
/* @var  array $smallSortArr */

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
                    'id' => 'goodsSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
//                            'contentBefore'=>'<legend class="text-info"><small>选择商品类型</small></legend>',
                            'columns'=>12,
                            'class'=>'form-inline',
                            'autoGenerateColumns'=>false, // override columns setting
                            'options'=>['class'=>'form-inline'],
                            'attributes'=>[       // 3 column layout
                                'goods_owner'=>[   // radio list
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
                                        'items' => GoodsConstantEnum::$ownerArr,
                                    ]
                                ],
                                'goods_owner_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
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
                                'sort_1' => [
                                    'type' => Form::INPUT_DROPDOWN_LIST,
                                    'items' => BackendCommon::addBlankOption($bigSortArr),
                                    'placeholder' => '选择商品大类...',
                                    'columnOptions' => ['colspan' => 3],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'onchange'=>'
                                            $.get("/goods-sort/select-options?parent_id="+$(this).val()+"sort_owner="+$("input[name=\'GoodsSearch[goods_owner]\']:checked").val(),function(data){             
                                                $("#goodssearch-sort_2").html("<option value=>请选择</option>");
                                                $("#goodssearch-sort_2").append(data);
                                            });'
                                    ]
                                ],
                                'sort_2' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption($smallSortArr), 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 3]],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品名称...'], 'columnOptions' => ['colspan' => 4]],
                                'goods_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$statusArr), 'placeholder' => '选择商品状态...', 'columnOptions' => ['colspan' => 4]],
//                                'sort_1' => [
//                                    'type' => Form::INPUT_DROPDOWN_LIST,
//                                    'items' => BackendCommon::addBlankOption($bigSortArr),
//                                    'placeholder' => '选择商品大类...',
//                                    'columnOptions' => ['colspan' => 3],
//                                    'options'=>[
//                                        'style'=>'display:inline',
//                                        'onchange'=>'
//                                            $.get("/goods-sort/select-options?parent_id="+$(this).val()+"sort_owner="+$("input[name=\'GoodsSearch[goods_owner]\']:checked").val(),function(data){
//                                                $("#goodssearch-sort_2").html("<option value=>请选择</option>");
//                                                $("#goodssearch-sort_2").append(data);
//                                            });'
//                                    ]
//                                ],
//                                'sort_2' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption($smallSortArr), 'placeholder' => '选择商品小类...', 'columnOptions' => ['colspan' => 3]],
                            ]
                        ],
//                        [
//                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品名称...'], 'columnOptions' => ['colspan' => 4]],
//                                'goods_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$statusArr), 'placeholder' => '选择商品状态...', 'columnOptions' => ['colspan' => 3]],
//                                'goods_owner_id' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入归属ID...'], 'columnOptions' => ['colspan' => 3]],
//                                'goods_sold_channel_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Goods::$goodsSoldChannelTypeArr), 'placeholder' => '选择商品售卖级别...', 'columnOptions' => ['colspan' => 2]],
//                            ]
//                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-10">
                <blockquote class="blockquote-reverse">
                    <div class="form-group">
                        <?= Html::submitButton('查询', ['class' => 'col-xs-offset-4 btn btn-primary']) ?>
                        <?= Html::Button('导出',['onclick'=>'exportXls();','class' => 'btn btn-info']);?>
                        <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                    </div>
                </blockquote>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php $this->beginBlock('js_end') ?>
$("input[name='GoodsSearch[goods_owner]']").on('ifChanged', function(event){
    $.get("/goods-sort/select-options?parent_id=0&sort_owner="+$("input[name='GoodsSearch[goods_owner]']:checked").val(),function(data){
        $("#goodssearch-sort_1").html("<option value=>请选择</option>");
        $("#goodssearch-sort_2").html("<option value=>请选择</option>");
        $("#goodssearch-sort_1").append(data);
    });
    $.get("/owner-type/options?owner_type="+$("input[name='GoodsSearch[goods_owner]']:checked").val(),function(data){
        $("#goodssearch-goods_owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
    });
});
function exportXls() {
    let url = '/goods/export?'+$('#goodsSearchForm').serialize();
    window.open(url);
}
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
