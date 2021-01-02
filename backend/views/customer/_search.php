<?php

use backend\services\RegionService;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\builder\FormGrid;
use backend\models\BackendCommon;
use kartik\builder\Form;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\CustomerSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">

                <?php
                $form = ActiveForm::begin([
                    'type'=>ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id'=>'customerSearchForm',
                ]);

                echo FormGrid::widget([
                    'model'=>$model,
                    'form'=>$form,

                    'autoGenerateColumns'=>true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows'=>[
                        [
                            'contentBefore'=>'<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns'=>12,
                            'autoGenerateColumns'=>false, // override columns setting
                            'attributes'=>[       // 3 column layout
                                'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入昵称...'],'columnOptions'=>['colspan'=>4]],
                                'realname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>4]],
                                'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                        [
                            'columns'=>12,
                            'autoGenerateColumns'=>false, // override columns setting
                            'attributes'=>[       // 3 column layout
                                'province_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById(0),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择省份',
                                        'onchange'=>'
                                $.get("/region/region?id='.'"+$(this).val(),function(data){             
                                     $("#customersearch-city_id").html("<option value=>请选择城市</option>");
                                     $("#customersearch-county_id").html("<option value=>请选择地区</option>");
                                     $("#customersearch-city_id").append(data);
                                });'
                                    ]],
                                'city_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById($model->province_id),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择城市',
                                        'onchange'=>'
                                $.get("/region/region?id='.'"+$(this).val(),function(data){             
                                     $("#customersearch-county_id").html("<option value=>请选择地区</option>");
                                     $("#customersearch-county_id").append(data);
                                });'
                                    ]],
                                'county_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById($model->city_id),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择地区',
                                ]],
                                'address'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入具体地址...'],'columnOptions'=>['colspan'=>6]],
                            ]
                        ],
                        [
                            'columns'=>4,
                            'autoGenerateColumns'=>true, // override columns setting
                            'attributes'=>[       // 3 column layout
                                'status'=>['type'=>Form::INPUT_DROPDOWN_LIST, 'items'=>BackendCommon::addBlankOption(\common\models\Customer::$StatusArr), 'placeholder'=>'选择...','columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                    ]
                ]);
                ?>
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-8 btn btn-primary']) ?>
                    <?= Html::Button('导出',['onclick'=>'exportXls();','class' => 'btn btn-info']);?>
                    <?= Html::resetButton('重置', ['class' => ' btn   btn-default']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
