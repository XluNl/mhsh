<?php

use backend\models\BackendCommon;
use common\models\Coupon;
use common\models\CouponBatch;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\CouponSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-7" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'couponSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'status' => ['type' => Form::INPUT_HIDDEN],
                                'customer_id' => ['type' => Form::INPUT_HIDDEN],
                                'batch' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入优惠券名称...'], 'columnOptions' => ['colspan' => 3    ]],
                                'coupon_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入优惠券编号...'], 'columnOptions' => ['colspan' => 3    ]],
                                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Coupon::$typeDisplayArr), 'placeholder' => '选择优惠券类型...', 'columnOptions' => ['colspan' =>2]],
                                'restore' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Coupon::$restoreArr), 'placeholder' => '选择是否可恢复...', 'columnOptions' => ['colspan' =>2]],
                                'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(Coupon::$statusArr), 'placeholder' => '选择优惠券状态...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-12" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-6 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
