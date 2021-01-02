<?php

use backend\services\RegionService;
use common\models\Delivery;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/* @var common\models\Popularizer $model */
$this->title = '配送团长信息保存';
$this->params['breadcrumbs'][] = ['label' => '配送团长列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">配送团长信息保存</h3>
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
                                        'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入配送点名称...'],'columnOptions'=>['colspan'=>3]],
                                        'realname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>3]],
                                        'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入常用手机号...'],'columnOptions'=>['colspan'=>3]],
                                        'em_phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入紧急手机号...'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'wx_number'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入微信号...'],'columnOptions'=>['colspan'=>3]],
                                        'allow_order' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Delivery::$allowOrderArr, 'placeholder' => '选择是否允许下单...', 'columnOptions' => ['colspan' => 3]],
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Delivery::$typeArr, 'placeholder' => '选择配送点类型...', 'columnOptions' => ['colspan' => 3]],
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
                                                     $("#delivery-city_id").html("<option value=>请选择城市</option>");
                                                     $("#delivery-county_id").html("<option value=>请选择地区</option>");
                                                     $("#delivery-city_id").append(data);
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
                                                     $("#delivery-county_id").html("<option value=>请选择地区</option>");
                                                     $("#delivery-county_id").append(data);
                                                });'
                                            ]],
                                        'county_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                            'items'=>RegionService::getRegionById($model->city_id),
                                            'columnOptions'=>['colspan'=>2],
                                            'options'=>[
                                                'style'=>'display:inline',
                                                'prompt'=>'请选择地区',
                                            ]],
                                        'community'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入小区名称...'],'columnOptions'=>['colspan'=>2]],
                                        'address'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入具体地址...'],'columnOptions'=>['colspan'=>4]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'lat'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入纬度...'],'columnOptions'=>['colspan'=>3]],
                                        'lng'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入经度...'],'columnOptions'=>['colspan'=>3]],
                                        'contract_images'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                                'clientOptions' => [
                                                    'pick' => [
                                                        'multiple' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'auth' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Delivery::$authStatusArr, 'placeholder' => '选择认证类型...', 'columnOptions' => ['colspan' => 3]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>