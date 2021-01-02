<?php

use backend\models\BackendCommon;
use common\models\BonusBatch;
use common\models\CustomerInvitationActivity;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var common\models\BonusBatch $model */
$this->title = '保存奖励金活动信息';
$this->params['breadcrumbs'][] = ['label' => '奖励金活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">邀请拉新活动修改</h3>
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
                                        'name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动名称...'],'columnOptions'=>['colspan'=>2]],
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CustomerInvitationActivity::$typeArr), 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 2]],
                                        'image'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写时间</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'show_start_time'=>[
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'show_end_time'=>[
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示结束时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'activity_start_time'=>[
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择活动开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'activity_end_time'=>[
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择活动结束时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'expect_settle_time'=>[
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择活动预计结算时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                    'minView'=>0,
                                                    'minuteStep'=>1,
                                                ]
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写活动详情</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'detail'=>[
                                            'columnOptions'=>['colspan'=>12],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\yii\redactor\widgets\Redactor',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => [
                                                    'lang' => 'zh_cn',
                                                ],
                                            ]
                                        ],
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