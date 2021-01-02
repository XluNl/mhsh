<?php

use backend\models\BackendCommon;
use common\models\BonusBatch;
use common\models\CustomerInvitationActivity;
use common\models\CustomerInvitationActivityPrize;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var common\models\BonusBatch $model
 * @var $activityModel
 */
$this->title = '保存邀请奖励活动奖品';
$this->params['breadcrumbs'][] = ['label' => '邀请奖励活动奖品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading"><?= $activityModel['name']?>邀请拉新活动的奖品修改</h3>
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
                                    'columns'=>6,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动名称...'],'columnOptions'=>['colspan'=>1]],
                                        'level_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CustomerInvitationActivityPrize::$levelTypeArr), 'placeholder' => '选择奖励等级...', 'columnOptions' => ['colspan' => 1]],
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CustomerInvitationActivityPrize::$typeArr), 'placeholder' => '选择奖品类型...', 'columnOptions' => ['colspan' => 1]],
                                        'batch_no'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入奖品批次...'],'columnOptions'=>['colspan'=>1]],
                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入单次发放数量...'],'columnOptions'=>['colspan'=>1]],
                                    ]
                                ],
//                                [
//                                    'contentBefore'=>'<legend class="text-info"><small>填写奖品信息</small></legend>',
//                                    'columns'=>12,
//                                    'autoGenerateColumns'=>false, // override columns setting
//                                    'attributes'=>[       // 3 column layout
//                                        'level_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CustomerInvitationActivityPrize::$levelTypeArr), 'placeholder' => '选择奖励等级...', 'columnOptions' => ['colspan' => 2]],
//                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => BackendCommon::addBlankOption(CustomerInvitationActivityPrize::$typeArr), 'placeholder' => '选择奖品类型...', 'columnOptions' => ['colspan' => 2]],
//                                        'batch_no'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入奖品批次...'],'columnOptions'=>['colspan'=>5]],
//                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入单次发放数量...'],'columnOptions'=>['colspan'=>3]],
//                                    ]
//                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写规则信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'range_start'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入开始范围数量...'],'columnOptions'=>['colspan'=>2]],
                                        'range_end'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入截止范围数量...'],'columnOptions'=>['colspan'=>2]],
                                        'expect_quantity'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入预计分发数量...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index','CustomerInvitationActivityPrizeSearch[activity_id]'=>$activityModel['id']], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>