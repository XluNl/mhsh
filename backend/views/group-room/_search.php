<?php

use backend\models\BackendCommon;
use backend\services\CustomerService;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\GroupRoomSearch */
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
                    'id' => 'groupRoomSearchForm',
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
                                'active_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入活动编号...'], 'columnOptions' => ['colspan' => 3 ]],
                                'room_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入拼团房间编号...'], 'columnOptions' => ['colspan' => 3 ]],
                                'team_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption(CustomerService::searchCustomerOne($model->team_id)),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> kartik\select2\Select2::THEME_BOOTSTRAP,
                                        // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'placeholder'=>'请选择',
                                            'allowClear' => true,
                                            'minimumInputLength' => 1,
                                            'language' => [
                                                'errorLoading' => new JsExpression("function () { return '等待加载中'; }"),
                                            ],
                                            'ajax' => [
                                                'delay'=>500,
                                                'url' => Url::to(['search-option/search-customer']),
                                                'dataType' => 'json',
                                                'data' => new JsExpression('function(params) { return {keyword:params.term}; }'),
                                                'processResults' => new JsExpression('function(data, params) { 
                                                    return {
                                                        results:data.data,
                                                        pagination:{more:false}
                                                    }; 
                                                }'),
                                            ],
                                            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                            'templateResult' => new JsExpression('function(data) {
                                                    if(data.loading){
                                                        return data.text;
                                                    }
                                                    return data.id+"-"+data.text+"-"+data.phone;
                                            }'),
                                            'templateSelection' => new JsExpression('function(data) {
                                                    if(data.loading){
                                                        return data.text;
                                                    }
                                                    return data.text;
                                            }'),
                                        ],
                                    ]
                                ],
                            ]
                        ],
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
