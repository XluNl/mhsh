<?php

use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use \backend\models\BackendCommon;
use \common\models\Payment;
use common\models\Order;
use yiichina\icheck\ICheck;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
?>
<style>
    .select2-container--krajee{
        display: inline-block !important;
        width: auto !important;
    }
</style>
<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-11" style="z-index: 10;margin-left: 0;">
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
                                'order_status' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
//                            'contentBefore' => '<legend class="text-info"><small>日期查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'order_time_start'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '创建时间起始','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:00',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'order_time_end'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '创建时间截止','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:59',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'complete_time_start'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '完成时间起始','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:00',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'complete_time_end'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '完成时间截止','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:59',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'order_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入订单号...'], 'columnOptions' => ['colspan' => 3]],
                                'order_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getOrderTypeArr()), 'placeholder' => '选择订单类型...', 'columnOptions' => ['colspan' => 2]],
                                'pay_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Payment::$typeArr), 'placeholder' => '选择支付类型...', 'columnOptions' => ['colspan' => 2]],
                                'pay_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::$pay_status_list), 'placeholder' => '选择支付结果...', 'columnOptions' => ['colspan' => 2]],
                                'evaluate' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::$evaluateArr), 'placeholder' => '选择评价状态...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                //'order_owner' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getOrderOwnerArr()), 'placeholder' => '选择订单归属类型...', 'columnOptions' => ['colspan' => 2]],
                                'order_owner'=>[   // radio list
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
                                        'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                    ]
                                ],
                                'order_owner_id'=> [
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => $allianceNames,
                                        'model' => $model,
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'language' => 'zh-CN',
                                        'options' => [
                                            'placeholder' => '选择异业联盟点 ...',
                                        ],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ],
                                ],
                                'delivery_id'=> [
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => $deliveryNames,
                                        'model' => $model,
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'language' => 'zh-CN',
                                        'options' => [
                                            'placeholder' => '选择配送团长 ...',
                                        ],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ],
                                ],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'accept_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货人...'], 'columnOptions' => ['colspan' => 3]],
                                'accept_mobile' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货手机号...'], 'columnOptions' => ['colspan' => 3]],
                                'accept_address' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货地址...'], 'columnOptions' => ['colspan' => 3]],
                                'accept_delivery_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getDeliveryTypeArr()), 'placeholder' => '选择配送方式...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'goods_num_start' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数起...'], 'columnOptions' => ['colspan' => 2]],
                                'goods_num_end' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数止...'], 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
//                        [
////                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'order_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入订单号...'], 'columnOptions' => ['colspan' => 2]],
//                                'order_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getOrderTypeArr()), 'placeholder' => '选择订单类型...', 'columnOptions' => ['colspan' => 2]],
//                                'delivery_id'=> [
//                                    'columnOptions'=>['colspan'=>2],
//                                    'type'=>Form::INPUT_WIDGET,
//                                    'widgetClass'=>'\kartik\widgets\Select2',
//                                    'options'=>[
//                                        'data' => $deliveryNames,
//                                        'model' => $model,
//                                        'theme'=> Select2::THEME_BOOTSTRAP,
//                                        'language' => 'zh-CN',
//                                        'options' => [
//                                            'placeholder' => '选择配送团长 ...',
//                                        ],
//                                        'pluginOptions' => [
//                                            'allowClear' => true
//                                        ],
//                                    ],
//                                ],
//                                'order_owner' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getOrderOwnerArr()), 'placeholder' => '选择订单归属类型...', 'columnOptions' => ['colspan' => 2]],
//                                'order_owner_id' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入归属ID...'], 'columnOptions' => ['colspan' => 2]],
//                            ]
//                        ],
//                        [
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
////                                'pay_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Payment::$typeArr), 'placeholder' => '选择支付类型...', 'columnOptions' => ['colspan' => 3]],
////                                'pay_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::$pay_status_list), 'placeholder' => '选择支付结果...', 'columnOptions' => ['colspan' => 3]],
//                                'accept_delivery_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::getDeliveryTypeArr()), 'placeholder' => '选择配送方式...', 'columnOptions' => ['colspan' => 2]],
//                                'accept_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货人...'], 'columnOptions' => ['colspan' => 2]],
//                                'accept_mobile' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货手机号...'], 'columnOptions' => ['colspan' => 2]],
//                                'accept_address' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货地址...'], 'columnOptions' => ['colspan' => 2]],
//                                'goods_num_start' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数起...'], 'columnOptions' => ['colspan' => 2]],
//                                'goods_num_end' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数止...'], 'columnOptions' => ['colspan' => 2]],
////                                'evaluate' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Order::$evaluateArr), 'placeholder' => '选择评价状态...', 'columnOptions' => ['colspan' => 3]],
//                            ]
//                        ],
//                        [
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'accept_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货人...'], 'columnOptions' => ['colspan' => 2]],
//                                'accept_mobile' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货手机号...'], 'columnOptions' => ['colspan' => 3]],
//                                'accept_address' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入收货地址...'], 'columnOptions' => ['colspan' => 3]],
//                                'goods_num_start' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数起...'], 'columnOptions' => ['colspan' => 2]],
//                                'goods_num_end' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品总数止...'], 'columnOptions' => ['colspan' => 2]],
//                            ]
//                        ],

                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-10">
                <blockquote class="blockquote-reverse">
                    <div class="form-group">
                        <?= Html::submitButton('查询', ['class' => 'col-xs-offset-10 btn btn-success']) ?>
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
    function exportXls() {
        let url = '/order/export?'+$('#orderSearchForm').serialize();
        window.open(url);
    }
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
