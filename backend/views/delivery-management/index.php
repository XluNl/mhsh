<?php


use backend\utils\BackendViewUtil;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use kartik\date\DatePicker;
use kartik\editable\Editable;
use kartik\grid\GridView;
use kartik\popover\PopoverX;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DeliveryManagementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '发货管理';
$this->params['breadcrumbs'][] = $this->title;
?>
    <style type="text/css">
        .box-body   th
        {
            text-align:center;
        }
    </style>
<div class="container-fluid">

    <div style="margin-left: -15px;margin-right: -15px;">
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                需配送商品列表
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'id' => 'delivery-management',
                    'dataProvider' => $dataProvider,
                    'layout' => "{toolbar}\n{summary}\n{items}\n{pager}",
                    'headerRowOptions' => ['class' => 'kartik-sheet-style'],
                    'toolbar' =>  [
                        [
                            'content' =>
                                Html::a('批量发货',
                                    "javascript:void(0);",
                                    [
                                        'id'=>'delivery-btn',
                                        'class' => 'btn btn-success gridview',
                                        'title' =>'发货',
                                    ]),
                        ],
                    ],
                    'columns' => [
                        ['class' => 'kartik\grid\SerialColumn'],
                        [
                            'class' => 'kartik\grid\CheckboxColumn',
                            'headerOptions' => ['class' => 'kartik-sheet-style'],
                            'checkboxOptions' => function ($model, $key, $index, $column) {
                                return ['value'=>$model['schedule_id']];
                            }
                        ],
                        [
                            'header' => '商品类型',
                            'attribute' => 'goods_owner',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['goods_owner'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'header' => '排期',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a("{$data['schedule_name']}({$data['schedule_id']})",
                                    ['goods-schedule/index','GoodsScheduleSearch[id]'=>$data['schedule_id']],
                                    ['target'=>'_blank']
                                );
                            },
                        ],
                        [
                            'header' => '商品',
                            'attribute' => 'goods_name',
                        ],
                        [
                            'header' => '规格',
                            'attribute'=>'sku_name',
                        ],
                        [
                            'header' => '单位',
                            'attribute'=>'sku_unit',
                        ],
                        [
                            'header' => '已售卖',
                            'attribute'=>'sold_amount',
                        ],
                        [
                            'header' => '待发货',
                            'attribute'=>'un_delivery_amount',
                        ],
                        [
                            'header' => '预计送达时间',
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'expect_arrive_time',
                            'value' => function ($data) {
                                return $data['expect_arrive_time'];
                            },
                            'xlFormat' => "yyyy-MM-dd",
                            'readonly' => function($model, $key, $index, $widget) {
                                return false; // do not allow editing of inactive records
                            },
                            'options' => [
                                'submitOnEnter' => false,
                            ],
                            'editableOptions'=> function ($model, $key, $index) {
                                return [
                                    'formOptions' => ['action' => ['/delivery-management/modify-expect-arrive-time']],
                                    'placement' => PopoverX::ALIGN_LEFT,
                                    'header' => '修改预计送达时间',
                                    'attribute' => 'expect_arrive_time',
                                    'name' => 'expect_arrive_time',
                                    'inputType' => Editable::INPUT_DATE,
                                    'options' => [
                                        'readonly'=>true,
                                        'type' => DatePicker::TYPE_INLINE,
                                        'pluginOptions' => [
                                            'autoclose'=>true,
                                            'format' => 'yyyy-mm-dd',
                                            'todayHighlight' => true,
                                        ]
                                    ],
                                ];
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<script>
<?php $this->beginBlock('js_end') ?>
$("#delivery-btn").on("click",function(){
    bootbox.confirm({
        message: "批量发货？",
        buttons: {
            confirm: {
                label: '确认发货',
                className: 'btn-success'
            },
            cancel: {
                label: '取消',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (!result) return;
            $('#delivery-btn').button('loading');
            let ids = $('#delivery-management').yiiGridView('getSelectedRows');
            if (ids===undefined||ids.length<1){
                bootbox.alert("请先选择需要发货的商品");
                return;
            }
            let idsStr = ids.join(',');

            let url = "/delivery-management/delivery-out?scheduleIds="+idsStr;
            let order_time_start= $('#deliverymanagementsearch-order_time_start').val();
            if (order_time_start!==undefined||order_time_start!==''){
                url += "&order_time_start="+order_time_start;
            }
            let order_time_end= $('#deliverymanagementsearch-order_time_end').val();
            if (order_time_end!==undefined||order_time_end!==''){
                url += "&order_time_end="+order_time_end;
            }
            $.getJSON(url,function(data,status){
                let message  = "";
                if (data===undefined){
                    message = (errorMsg);
                }
                else if (data.status===false){
                    message = ("<pre>"+data.error+"</pre>");
                }
                else {
                    message = ("<pre>"+data.data+"</pre>");
                }
                bootbox.alert({
                    message: message,
                    callback: function () {
                        window.location.reload();
                    }
                });
                $('#delivery-btn').button('reset');

            });
        }
    });
});
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>