<?php

use common\utils\DateTimeUtils;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
/* @var $this yii\web\View */
/* @var $model backend\models\searches\GoodsSkuStockSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-7" style="margin-left: 0;z-index: 10">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'goodsSkuStockSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                    'rows' => [
                        [
                            'attributes' => [       // 3 column layout
                                'type' => ['type' => Form::INPUT_HIDDEN],
                            ]
                        ],
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'goods_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入商品名称...'], 'columnOptions' => ['colspan' => 3 ]],
                                'start_time'=>[
                                    'columnOptions'=>['colspan'=>4],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => [ 'placeholder' => '起始时间', 'readonly'=>true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd HH:mm:00',
                                            'todayHighlight' => true,
                                            'autoclose'=>true,
                                        ]
                                    ]
                                ],
                                'end_time'=>[
                                    'columnOptions'=>['colspan'=>4],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                    'options'=>[
                                        'model' => $model,
                                        'options' => ['placeholder' => '截止时间','readonly'=>true],
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
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-offset-1 col-md-9" style="margin-top: -50px;">
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-8 btn btn-primary']) ?>
                    <?= Html::Button('导出日志',['onclick'=>'exportLogXls();','class' => 'btn btn-info']);?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<?php $this->beginBlock('js_end') ?>

function exportLogXls() {
    let url = '/goods-sku-stock/export-log?'+$('#goodsSkuStockSearchForm').serialize();
    window.open(url);
}

<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
