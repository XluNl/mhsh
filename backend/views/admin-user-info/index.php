<?php

use common\models\AdminUserInfo;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


$this->title = '修改个人信息';
$this->params['breadcrumbs'][] = $this->title;


?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-8 col-xs-offset-2">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">用户信息修改</h3>
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
                                        'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入昵称...'],'columnOptions'=>['colspan'=>4]],
                                        'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>4]],
                                        'sex' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>AdminUserInfo::$sexArr, 'placeholder' => '选择性别...', 'columnOptions' => ['colspan' => 3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'pic'=>[   // radio list
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                            ]
                                        ],
                                        'mark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入备注...'],'columnOptions'=>['colspan'=>6]],
                                    ]
                                ],
                            ]
                        ]);

                        ?>

                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['/goods/index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>