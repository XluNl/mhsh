<?php

use backend\models\BackendCommon;
use common\models\OrderCustomerService;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderCustomerServiceSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $deliveryOptions array */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-8" style="z-index: 10;margin-left: 0;">
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
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'order_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入订单号...'], 'columnOptions' => ['colspan' => 3]],
                                'status'=>['type'=>Form::INPUT_DROPDOWN_LIST, 'items'=>BackendCommon::addBlankOption(OrderCustomerService::$statusArr), 'placeholder'=>'选择审核状态...','columnOptions'=>['colspan'=>2]],
                                'audit_level'=>['type'=>Form::INPUT_DROPDOWN_LIST, 'items'=>BackendCommon::addBlankOption(OrderCustomerService::$auditLevelArr), 'placeholder'=>'选择审核等级...','columnOptions'=>['colspan'=>2]],
                                'type'=>['type'=>Form::INPUT_DROPDOWN_LIST, 'items'=>BackendCommon::addBlankOption(OrderCustomerService::$typeArr), 'placeholder'=>'选择申请类型...','columnOptions'=>['colspan'=>2]],
                                'delivery_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($deliveryOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'options' => ['placeholder' => '选择申请类型 ...'],
                                    ]
                                ],
                            ]
                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-10" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-9 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
