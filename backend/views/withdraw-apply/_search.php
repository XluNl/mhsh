<?php

use backend\models\BackendCommon;
use common\models\BizTypeEnum;
use common\models\BusinessApply;
use common\models\WithdrawApply;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\WithdrawApplySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'withdrawApplySearchForm',
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
                                'biz_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(BizTypeEnum::$bizTypeArr), 'placeholder' => '选择账户类型...', 'columnOptions' => ['colspan' => 2]],
                                'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$typeArr), 'placeholder' => '选择提现方式...', 'columnOptions' => ['colspan' => 2]],
                                'biz_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入名称...'], 'columnOptions' => ['colspan' => 2]],
                                'audit_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$auditStatusArr), 'placeholder' => '选择审核状态...', 'columnOptions' => ['colspan' => 2]],
                                'process_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$processStatusArr), 'placeholder' => '选择处理状态...', 'columnOptions' => ['colspan' => 2]],
                                'is_return' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$isReturnArr), 'placeholder' => '选择退还状态...', 'columnOptions' => ['colspan' => 2]],
                            ]
                        ],
//                        [
//                            'columns' => 12,
//                            'autoGenerateColumns' => false, // override columns setting
//                            'attributes' => [       // 3 column layout
//                                'audit_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$auditStatusArr), 'placeholder' => '选择审核状态...', 'columnOptions' => ['colspan' => 3]],
//                                'process_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$processStatusArr), 'placeholder' => '选择处理状态...', 'columnOptions' => ['colspan' => 3]],
//                                'is_return' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(WithdrawApply::$isReturnArr), 'placeholder' => '选择退还状态...', 'columnOptions' => ['colspan' => 3]],
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
