<?php

use backend\models\forms\DownloadQueryForm;
use common\utils\DateTimeUtils;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\OrderSearch */
/* @var $form yii\widgets\ActiveForm */
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
                        'model' => new DownloadQueryForm(),
                        'form' => $form,
                        'autoGenerateColumns' => true,
                        //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                        'rows' => [

                            [
                                'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                                'columns' => 12,
                                'autoGenerateColumns' => false, // override columns setting
                                'attributes' => [       // 3 column layout
                                    'order_time_between'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入下单时间...','readonly' => true],'columnOptions'=>['colspan'=>6]],
                                    'sorting_date'=>[
                                        'type'=>Form::INPUT_TEXT,
                                        'options'=>[
                                            'value' => DateTimeUtils::formatYearAndMonthAndDay(time(),false),
                                            'placeholder'=>'输入分拣时间...',
                                            'readonly' => true
                                        ],
                                        'columnOptions'=>['colspan'=>3]
                                    ],
                                ]
                            ],
                        ]
                    ]);
                    ?>
                    <?php ActiveForm::end(); ?>
                <div class="form-group">
                    <?= Html::Button('导出',['onclick'=>'exportXls();','class' => 'btn btn-info btn-block']);?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
laydate.render({elem: '#downloadqueryform-order_time_between',type: 'datetime' ,range: '~'});
laydate.render({elem: '#downloadqueryform-sorting_date',type: 'date'});
    function exportXls() {
        let url = '/download/sorting-list?';
        let sorting_date = $('#downloadqueryform-sorting_date').val();
        if (sorting_date===undefined||sorting_date===''){
            bootbox.alert('分拣时间不能为空');
            return;
        }
        url += "sorting_date="+sorting_date;
        let order_time_between= $('#downloadqueryform-order_time_between').val();
        if (order_time_between!==undefined&&order_time_between!==''){
            let order_time_between_arr = order_time_between.split('~');
            url += "&order_time_start="+order_time_between_arr[0].trim();
            url += "&order_time_end="+order_time_between_arr[1].trim();
        }
        console.log(url);
        window.open(url);
    }
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END);
