<?php

use backend\models\forms\SignupForm;
use common\utils\StringUtils;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var SignupForm $model */
$this->title = '代理商用户信息保存';
$this->params['breadcrumbs'][] = ['label' => '代理商列表', 'url' => ['/company/index']];
$this->params['breadcrumbs'][] = ['label' => '用户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (StringUtils::isBlank($model->id)){
    $passwordsCol = [
        'password' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入密码...'], 'columnOptions' => ['colspan' => 3 ]],
        'retypePassword' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '重复密码...'], 'columnOptions' => ['colspan' => 3 ]]
    ];
    $usernameCol = ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入登录名...'], 'columnOptions' => ['colspan' => 3 ]];
}
else{
    $passwordsCol = [
        'password' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '如您无需重置密码，则不需要填写此项'], 'columnOptions' => ['colspan' => 3 ]],
        'retypePassword' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '如您无需重置密码，则不需要填写此项'], 'columnOptions' => ['colspan' => 3 ]]
    ];
    $usernameCol = ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入登录名...','disabled'=>'disabled'], 'columnOptions' => ['colspan' => 3 ]];
}

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">代理商用户信息保存</h3>
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
                                        'username' => $usernameCol,
                                        'nickname' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入昵称...'], 'columnOptions' => ['colspan' => 3 ]],
                                        'email' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入Email...'], 'columnOptions' => ['colspan' => 3 ]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>$passwordsCol
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton(StringUtils::isBlank($model->id) ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>