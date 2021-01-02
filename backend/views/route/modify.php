<?php


use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var common\models\Route $model */
$this->title = '保存路线信息';
$this->params['breadcrumbs'][] = ['label' => '路线信息列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">路线信息修改</h3>
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
                                        'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入昵称...'],'columnOptions'=>['colspan'=>2]],
                                        'realname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>2]],
                                        'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>2]],
                                        'em_phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入紧急手机号...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-4 col-xs-1 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-1 col-xs-1 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>