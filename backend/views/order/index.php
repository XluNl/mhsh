<?php

use backend\services\OrderService;
use backend\utils\BackendViewUtil;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use kartik\popover\PopoverX;
use \yii\bootstrap\Html;
use kartik\grid\GridView;
use \common\models\Common;
use \common\models\Order;
use \common\models\Payment;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\OrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
$this->title = '订单列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body th {
        text-align: center;
    }
</style>
<div class="container-fluid">

    <?php echo $this->render('_search', ['model' => $searchModel,
        'deliveryNames' => $deliveryNames,
        'allianceNames' => $allianceNames
    ]); ?>

    <div class="panel with-nav-tabs panel-primary" style="text-align: center">
        <?php echo $this->render('filter', ['order_status' => $searchModel->order_status]); ?>
        <div class="box-body" style="text-align: center">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'order_no',
                    'created_at',
                    [
                        'attribute' => 'order_status',
                        'format' => 'raw',
                        'value' => function ($data) {
                            $content = "用户收货时间：{$data['accept_time']}<br/>";
                            $content .= "订单完成时间：{$data['completion_time']}";
                            return PopoverX::widget([
                                'header' => '时间',
                                'placement' => PopoverX::ALIGN_RIGHT,
                                'content' => $content,
                                'footer' => '',
                                'toggleButton' => [
                                    'label' => ArrayUtils::getArrayValue($data['order_status'], Order::$order_status_list),
                                    'class' => ArrayUtils::getArrayValue($data['order_status'], Order::$order_status_list_css),
                                ],
                            ]);
                            //return BackendViewUtil::getArrayWithLabel($data['order_status'],Order::$order_status_list,Order::$order_status_list_css);
                        },
                    ],
                    [
                        'attribute' => 'real_amount',
                        'value' => function ($data) {
                            return Common::showAmount($data['real_amount']);
                        },
                    ],
                    [
                        'attribute' => 'accept_delivery_type',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return BackendViewUtil::getArrayWithLabel($data['accept_delivery_type'], GoodsConstantEnum::$deliveryTypeArr, GoodsConstantEnum::$deliveryTypeCssArr);
                        },
                    ],
                    [
                        'attribute' => 'accept_address',
                        'value' => function ($data) {
                            return $data['accept_community'] . $data['accept_address'];
                        },
                    ],
                    'accept_name',
                    'accept_mobile',
                    [
                        'attribute' => 'order_type',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return BackendViewUtil::getArrayWithLabel($data['order_type'], Order::getOrderTypeArr(), GoodsConstantEnum::$typeCssArr);
                        },
                    ],
                    [
                        'header' => '备注',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::tag("label", OrderService::generateNote($data), ['class' => 'label label-danger']);;
                        },
                    ],

                    [
                        'attribute' => 'pay_type',
                        'value' => function ($data) {
                            return ArrayUtils::getArrayValue($data['pay_type'], Payment::$typeArr);
                        },
                    ],
                    [
                        'attribute' => 'order_owner',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return BackendViewUtil::getArrayWithLabel($data['order_owner'], GoodsConstantEnum::$ownerArr, GoodsConstantEnum::$ownerCssArr);
                        },
                    ],
                    /*[
                        'attribute' => 'delivery_id',
                        'format' => 'raw',
                        'value' => function ($data) use ($deliveryNames){
                            return Html::dropDownList('delivery_id',$data['delivery_id'],BackendCommon::addBlankOption($deliveryNames),['class'=>'form-control']);
                        },
                    ],*/
                    /*[
                        'attribute' => 'delivery_id',
                        'format' => 'raw',
                        'value' => function ($data) use ($deliveryNames){
                            if (!in_array($data['order_status'],[Order::ORDER_STATUS_UN_PAY,Order::ORDER_STATUS_PREPARE,Order::ORDER_STATUS_DELIVERY])){
                                return $data['delivery_nickname'];
                            }
                            return Select2::widget([
                                'model' => $data,
                                'attribute' => 'delivery_id',
                                'size' => Select2::SMALL,
                                'data' => BackendCommon::addBlankOption($deliveryNames),
                                'options' => [
                                    'id'=>"delivery_id_{$data['id']}",
                                ],
                                'pluginEvents' =>[
                                    "change" => "function() {
                                         console.log('change');
                                    }",
                                ]
                            ]);
                        },
                    ],*/

                    'delivery_name',
                    'delivery_phone',
                    [
                        'header' => '操作',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{detail}{output}',
                        'headerOptions' => ['width' => '130'],
                        'buttons' => [
                            'detail' => function ($url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("详情", ['/order/detail', 'order_no' => $model['order_no']], 'btn btn-xs btn-info', 'fa fa-share');
                            },
                            'output' => function ($url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("导出", ['/download/order', 'order_no' => $model['order_no']], 'btn btn-xs btn-primary', 'fa fa-download');
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>