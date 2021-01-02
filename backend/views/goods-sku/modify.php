<?php

use backend\components\ICheck;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $bigSortArr */
/* @var array $smallSortArr */
/* @var common\models\Goods $goodsModel */
/* @var common\models\GoodsSku $model */
$this->title = '保存商品属性信息';
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $goodsModel->goods_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;


?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading"><?= $goodsModel->goods_name ?> ——属性信息修改</h3>
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
                                        'sku_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入属性名称...'],'columnOptions'=>['colspan'=>4]],
                                        'sku_unit'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入属性单位...'],'columnOptions'=>['colspan'=>4]],
                                       // 'sku_stock'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入属性总库存...'],'columnOptions'=>['colspan'=>2]],
                                        'sku_sold'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入已售数量...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'sku_standard' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>GoodsSku::$skuStandardArr, 'placeholder' => '选择是否是标准件...', 'columnOptions' => ['colspan' => 3]],
                                        'sku_unit_factor'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'重量因子(单位：千克)'],'columnOptions'=>['colspan'=>3]],
                                        'start_sale_num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入起购数量...'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'purchase_price'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入采购价...'],'columnOptions'=>['colspan'=>4]],
                                        'reference_price'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入划线价...'],'columnOptions'=>['colspan'=>4]],
                                        'sale_price'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入默认售卖价...'],'columnOptions'=>['colspan'=>4]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'one_level_rate'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入用户一级分销比例...'],'columnOptions'=>['colspan'=>2]],
                                        'two_level_rate'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入用户二级分销比例...'],'columnOptions'=>['colspan'=>2]],
                                        'share_rate_1'=>['type'=>Form::INPUT_TEXT,'label'=>'分享提成比例', 'options'=>['placeholder'=>'输入分享提成比例...'],'columnOptions'=>['colspan'=>2]],
                                        //'share_rate_2'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入二级分享提成比例...'],'columnOptions'=>['colspan'=>4]],
                                        'delivery_rate'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入配送提成比例...'],'columnOptions'=>['colspan'=>2]],
//                                        'agent_rate'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入代理商提成比例...'],'columnOptions'=>['colspan'=>2]],
//                                        'company_rate'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入公司提成比例...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'production_date'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择生产时间/有效期起始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'expired_date'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择过期时间/有效期结束时间','readonly'=>true],
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
                                        'sku_img'=>[   // radio list
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                            ]
                                        ],
                                        'display_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'从大到小排列，默认0'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'sku_describe'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入商品属性描述信息...'],'columnOptions'=>['colspan'=>12]],
                                    ]
                                ],
                            ]
                        ]);

                        ?>

                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['/goods/index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
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