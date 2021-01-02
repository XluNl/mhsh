<?php

use backend\models\BackendCommon;
use common\models\BonusBatch;
use common\models\Coupon;
use common\models\CouponBatch;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10" style="z-index: 10;margin-left: 0">
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
                                'name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入活动名称...'], 'columnOptions' => ['colspan' => 3    ]],
                                'batch_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入批次编号...'], 'columnOptions' => ['colspan' => 3    ]],
                                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(BonusBatch::$typeArr), 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 3]],
                                'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(BonusBatch::$statusDisplayArr), 'placeholder' => '选择活动状态...', 'columnOptions' => ['colspan' => 3]],
                            ]
                        ],
//                        [
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(BonusBatch::$statusDisplayArr), 'placeholder' => '选择活动状态...', 'columnOptions' => ['colspan' => 2]],
//                            ]
//                        ],
                    ]
                ]);
                ?>

            </div>
            <div class="col-md-offset-1 col-md-12" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-9 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
