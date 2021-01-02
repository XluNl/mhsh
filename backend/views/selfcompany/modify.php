<?php

/* @var $this yii\web\View */

use backend\services\RegionService;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$title = "公司信息修改";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = $title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="page-heading">区域代理信息修改</h3>
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
                                    'name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入公司名称...'],'columnOptions'=>['colspan'=>6]],
                                    'contact'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>3]],
                                    'telphone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>3]],
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
                                     $("#company-city_id").html("<option value=>请选择城市</option>");
                                     $("#company-county_id").html("<option value=>请选择地区</option>");
                                     $("#company-city_id").append(data);
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
                                     $("#company-county_id").html("<option value=>请选择地区</option>");
                                     $("#company-county_id").append(data);
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
                                'columns'=>12,
                                'autoGenerateColumns'=>false, // override columns setting
                                'attributes'=>[       // 3 column layout
                                    'email'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入公司名称...'],'columnOptions'=>['colspan'=>6]],
                                    'office_phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>3]],
                                    'service_phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>3]],
                                ]
                            ],
                            [
                                'columns'=>12,
                                'autoGenerateColumns'=>false, // override columns setting
                                'attributes'=>[       // 3 column layout
                                    'fax'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入公司名称...'],'columnOptions'=>['colspan'=>6]],
                                    'zip_code'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>3]],
                                    'id'=>['type'=>Form::INPUT_HIDDEN],
                                ]
                            ],
                        ]
                    ]);

                    ?>

                    <div class="form-group">
                        <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
                        <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-warning btn-lg']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
// 首页设置框
$('#home_setting').hide();
// 首页设置框结束
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
