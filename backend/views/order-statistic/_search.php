<?php

use backend\models\BackendCommon;
use backend\models\forms\DownloadQueryForm;
use common\models\GoodsConstantEnum;
use common\utils\DateTimeUtils;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\forms\DownloadQueryForm */
/* @var $form yii\widgets\ActiveForm */
$model = new DownloadQueryForm();
$model->order_owner = null;
?>
<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-2 col-md-8">
                <div class="coupon-batch-search">
                    <?php
                    $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_VERTICAL,
                        'action' => ['index'],
                        'method' => 'get',
                        'id' => 'downloadQueryForm',
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
                                    'order_time_between'=>[
                                        'type'=>Form::INPUT_TEXT,
                                        'options'=>['placeholder'=>'输入下单时间...','readonly' => true],
                                        'columnOptions'=>['colspan'=>6]
                                    ],
                                    'order_owner'=>[   // radio list
                                        'columnOptions'=>['colspan'=>3],
                                        'type'=>Form::INPUT_WIDGET,
                                        'widgetClass'=>'\kartik\widgets\Select2',
                                        'options'=>[
                                            'data' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                            'language' => 'zh-CN',
                                            'size' => Select2::SMALL,
                                            'options' => ['hidden' => 'hidden'],
                                            'pluginOptions' => [
                                                'allowClear' => false,
                                            ],
                                            'pluginEvents' => [
                                                "select2:select" => 'function() { 
                                                       $.get("/goods-sort/select-options?sort_owner="+$(this).val(),function(data){
                                                            $("#downloadqueryform-big_sort").html("<option value=>请选择</option>").append(data).trigger("select2:select");             
                                                       });
                                                     }',
                                            ],
                                        ]
                                    ],
                                    'big_sort'=>[   // radio list
                                        'columnOptions'=>['colspan'=>3],
                                        'type'=>Form::INPUT_WIDGET,
                                        'widgetClass'=>'\kartik\widgets\Select2',
                                        'options'=>[
                                            'data' => BackendCommon::addBlankOption([]),
                                            'language' => 'zh-CN',
                                            'size' => Select2::SMALL,
                                            // 'options' => ['placeholder' => 'Select a state ...'],
                                            'pluginOptions' => [
                                                'allowClear' => false,
                                            ],
                                            'pluginEvents' => [

                                            ],
                                        ]
                                    ],
                                ]
                            ],
                        ]
                    ]);
                    ?>
                    <?php ActiveForm::end(); ?>
                <div class="form-group">
                    <?= Html::Button('客户维度订单统计导出',['onclick'=>'exportCustomerStatistic();','class' => 'btn btn-info  col-md-4']);?>
                    <?= Html::Button('团长维度订单统计导出',['onclick'=>'exportDeliveryStatistic();','class' => 'btn btn-primary  col-md-4']);?>
                    <?= Html::Button('商品维度订单统计导出',['onclick'=>'exportGoodsStatistic();','class' => 'btn btn-success  col-md-4']);?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
laydate.render({elem: '#downloadqueryform-order_time_between',type: 'datetime' ,range: '~',value:'<?=DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong(time(),false))?> ~ <?=DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong(time(),false))?>'});
function getParams() {
    let url = "";

    let order_time_between= $('#downloadqueryform-order_time_between').val();
    if (order_time_between!==undefined&&order_time_between!==''){
        let order_time_between_arr = order_time_between.split('~');
        url += "&order_time_start="+order_time_between_arr[0].trim();
        url += "&order_time_end="+order_time_between_arr[1].trim();
    }

    let big_sort= $('#downloadqueryform-big_sort').val();
    if (big_sort!==undefined||big_sort!==''){
        url += "&big_sort="+big_sort;
    }

    let order_owner= $('#downloadqueryform-order_owner').val();
    if (order_owner!==undefined||order_owner!==''){
        url += "&owner="+order_owner;
    }
    return url;
}

function exportCustomerStatistic() {
    let url = '/order-statistic/customer-statistic?'+getParams();
    window.open(url);
}

function exportDeliveryStatistic() {
    let url = '/order-statistic/delivery-statistic?'+getParams();
    window.open(url);
}

function exportGoodsStatistic() {
    let url = '/order-statistic/goods-statistic?'+getParams();
    window.open(url);
}

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END);
