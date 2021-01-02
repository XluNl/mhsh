<?php

use common\models\GoodsConstantEnum;
use common\models\GoodsSkuStock;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $goodsArr */
/* @var array $goodsSkuArr */
/* @var array $scheduleDisplayChannelArr */
/* @var common\models\GoodsSchedule $model */
$this->title = '录入商品库存信息';
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['/goods/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <div class="box-title">录入商品库存信息</div>
                        <div class="box-tools pull-right">
                            <?php echo Html::a('出入库日志', ['index'], ['class' => 'btn btn-warning']) ?>
                        </div>
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
                                        'goods_owner'=>[   // radio list
                                            'columnOptions'=>['colspan'=>1],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => GoodsConstantEnum::$ownerArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => 'function() { 
                                                       $.get("/goods-schedule/goods-options?goods_owner="+$(this).val(),function(data){             
                                                            $("#goodsskustock-goods_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                            $("#goodsskustock-sku_id").html("<option value=>请选择</option>").trigger("change");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'goods_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $goodsArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => 'function() { 
                                                        $("#goodsschedule-schedule_display_channel").html("<option value=>请选择</option>").trigger("change");
                                                       $.get("/goods-schedule/goods-sku-options?goods_id="+$(this).val(),function(data){             
                                                            $("#goodsskustock-sku_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'sku_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>1],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $goodsSkuArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                            ]
                                        ],
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsSkuStock::$typeArr, 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 2]],
                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入操作数量...'],'columnOptions'=>['colspan'=>2]],
                                        'remark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入备注...'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
//                                [
//                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
//                                    'columns'=>12,
//                                    'autoGenerateColumns'=>false, // override columns setting
//                                    'attributes'=>[       // 3 column layout
//                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsSkuStock::$typeArr, 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 2]],
//                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入操作数量...'],'columnOptions'=>['colspan'=>2]],
//                                        'remark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入备注...'],'columnOptions'=>['colspan'=>5]],
//                                    ]
//                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'录入':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-4 col-xs-1 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-1 col-xs-1 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->beginBlock('js_end_1') ?>
$("#goods-goods_owner").on('ifChanged', function(event){
    $.get("/sort/select-options?parent_id=0&sort_owner="+$("input[name='Goods[goods_owner]']:checked").val(),function(data){
        $("#goods-sort_1").html("<option value=>请选择</option>");
        $("#goods-sort_2").html("<option value=>请选择</option>");
        $("#goods-sort_1").append(data);
    });
});
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end_1'], \yii\web\View::POS_READY); ?>