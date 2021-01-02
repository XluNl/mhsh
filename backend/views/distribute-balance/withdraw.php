<?php

use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var backend\models\forms\WithdrawForm $model
 */
$this->title = '填写提现申请';
$this->params['breadcrumbs'][] = ['label' => '资金账户列表', 'url' => ['/distribute-balance/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
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
                                    'contentBefore'=>'<legend class="text-info"><small>填写提现申请</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'available_amount'=>['type'=>Form::INPUT_TEXT, 'options'=>['disabled'=>'disabled'],'columnOptions'=>['colspan'=>2]],
                                        'withdraw_amount'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入提现金额（单位:元，最少0.01元）...'],'columnOptions'=>['colspan'=>6]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton('确认提现', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>