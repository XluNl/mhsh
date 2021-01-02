<?php

use backend\services\RegionService;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/* @var common\models\Popularizer $model */
$this->title = '推广团长信息保存';
$this->params['breadcrumbs'][] = ['label' => '推广团长列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">推广团长信息保存</h3>
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
                                        'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入配送点名称...'],'columnOptions'=>['colspan'=>2]],
                                        'realname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>2]],
                                        'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入常用手机号...'],'columnOptions'=>['colspan'=>2]],
                                        'em_phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入紧急手机号...'],'columnOptions'=>['colspan'=>2]],
                                        'wx_number'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入微信号...'],'columnOptions'=>['colspan'=>2]],
                                        'occupation'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入职业...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
//                                [
//                                    'columns'=>12,
//                                    'autoGenerateColumns'=>false, // override columns setting
//                                    'attributes'=>[       // 3 column layout
//                                        'wx_number'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入微信号...'],'columnOptions'=>['colspan'=>3]],
//                                        'occupation'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入职业...'],'columnOptions'=>['colspan'=>3]],
//                                    ]
//                                ],
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
                                                     $("#popularizer-city_id").html("<option value=>请选择城市</option>");
                                                     $("#popularizer-county_id").html("<option value=>请选择地区</option>");
                                                     $("#popularizer-city_id").append(data);
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
                                                     $("#popularizer-county_id").html("<option value=>请选择地区</option>");
                                                     $("#popularizer-county_id").append(data);
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