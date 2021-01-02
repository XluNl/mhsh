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
    .row{
        margin-left: 0px;
        margin-right: 0px;
    }
</style>
<div class="box box-success" style="margin-top: 20px">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'method' => 'get',
                ]);
                echo FormGrid::widget([
                    'model'=>$model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'owner_type' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false,
                            'attributes' => [
                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品商品名称...'], 'columnOptions' => ['colspan' => 3]],
                            ]
                        ]
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group" style="display: flex;justify-content:flex-end;">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-4 btn btn-primary','style'=>'margin-right: 10px;']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
