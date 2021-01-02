<?php

use backend\components\ICheck;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSort;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use \backend\models\BackendCommon;
use \common\models\Payment;
use common\models\Order;use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var  array $bigSortArr */
/* @var  array $smallSortArr */

?>
<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-6" style="margin-left: 0;z-index: 10">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'method' => 'get',
                    'id' => 'deliverySearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'nickname' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'start_time'=>[
                                    'columnOptions'=>['colspan'=>4],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '选择开始时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:00',
                                            'todayHighlight' => true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'end_time'=>[
                                    'columnOptions'=>['colspan'=>4],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '选择结束时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:59',
                                            'todayHighlight' => true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入一级推荐人手机号...'],'columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                    ]
                ]);
                ?>

            </div>
            <div class="col-md-offset-1 col-md-8" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-8 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
