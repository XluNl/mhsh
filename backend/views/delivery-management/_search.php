<?php

use backend\models\BackendCommon;
use common\models\DeliveryComment;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\DeliveryManagementSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row"  >
            <div class="col-md-offset-1 col-md-8" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'deliveryManagementSearchForm',
                ]);
                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'expect_arrive_time'=>[
                                    'columnOptions'=>['colspan'=>3],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'removeButton' => false,
                                        'options' => ['placeholder' => '预计送达时间','readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'minView'=>'month',
                                            'autoclose' => true
                                        ]
                                    ]
                                ],
                                'order_time_start'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入下单时间开始...','readonly' => true],'columnOptions'=>['colspan'=>4]],
                                'order_time_end'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入下单时间截止...','readonly' => true],'columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-10" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-9 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
laydate.render({elem: '#deliverymanagementsearch-order_time_start',type: 'datetime' });
laydate.render({elem: '#deliverymanagementsearch-order_time_end',type: 'datetime'});
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END);
