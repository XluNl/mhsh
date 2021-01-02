<?php

use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use \backend\models\BackendCommon;
use \common\models\Payment;
use common\models\Order;
use yiichina\icheck\ICheck;
use common\models\GroupActive;
use common\models\GroupRoom;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
?>
<style>

    .row {
        margin-left: 0px;
        margin-right: 0px;
    }

    .help-block {
        display: block;
        margin-top: 0px;
        margin-bottom: 0px;
        color: #737373;
    }

    .field-grouproomordersearch-status {
        display: flex;
        flex-direction: column;
    }
</style>
<div class="box box-success" style="margin-top: 20px">
    <div class="box-body">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12" style="z-index: 10;margin-left: 0;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                ]);
                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    'rows' => [
                        [
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [
                                'active_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入活动编号...'], 'columnOptions' => ['colspan' => 2 ]],
                                'room_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入拼团房间编号...'], 'columnOptions' => ['colspan' => 2 ]],
                                'owner_type'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'placeholder'=>'请选择',
                                            'allowClear' => true,
                                        ],
                                        'pluginEvents' => [
                                            "change" => 'function() { 
                                                $.get("/goods-schedule/goods-options?goods_owner="+$(this).val(),function(data){             
                                                      $("#grouproomordersearch-goods_id").html("<option value=>请选择</option>").append(data).trigger("change");
                                                });         
                                            }',
                                        ],
                                    ]
                                ],
                                'goods_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',

                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->goodsOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'placeholder'=>'请选择',
                                            'allowClear' => true,
                                        ],
                                    ]
                                ],
                                'status'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',
                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption(GroupRoom::$groupRoomStatus),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        // 'size' => \kartik\widgets\Select2::SMALL,
                                        'pluginOptions' => [
                                            'placeholder'=>'请选择',
                                            'allowClear' => true,
                                        ],
                                        'pluginEvents' => [
                                        ],
                                    ]
                                ],
                                'phone' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '拼主手机号...'], 'columnOptions' => ['colspan' => 2]],
                                //'group_no' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '拼团单号...'], 'columnOptions' => ['colspan' => 2]],
                            ]
                        ]
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="form-group" style="display: flex;justify-content:flex-end;">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-4 btn btn-primary', 'style' => 'margin-right: 10px;']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php $this->beginBlock('js_end') ?>
function exportXls() {
let url = '/order/export?'+$('#orderSearchForm').serialize();
window.open(url);
}
<?php $this->endBlock() ?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
