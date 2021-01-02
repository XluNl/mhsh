<?php

use backend\services\OrderService;
use backend\utils\BackendViewUtil;
use common\models\BusinessApply;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use kartik\popover\PopoverX;
use \yii\bootstrap\Html;
use kartik\grid\GridView;
use \common\models\Common;
use \common\models\Order;
use \common\models\Payment;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\forms\OrderDeliverySearchForms */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '配送订单列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

    <?php  echo $this->render('order-delivery_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php  echo $this->render('order-delivery-filter', ['order_status' => $searchModel->order_status]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'order_no',
                            'header' => '订单编号',
                        ],
                        [
                            'attribute' => 'created_at',
                            'header' => '创建时间',
                        ],
                        [
                            'attribute' => 'order_status',
                            'header' => '订单状态',
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
                                        'label'=>ArrayUtils::getArrayValue($data['order_status'],Order::$order_status_list),
                                        'class'=>ArrayUtils::getArrayValue($data['order_status'],Order::$order_status_list_css),
                                    ],
                                ]);
                            },
                        ],
                        [
                            'header' => '商品总额/数量',
                            'attribute' => 'real_amount',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['real_amount']).'/'.$data['goods_num'].'个';
                            },
                        ],
                        [
                            'header' => '当日配送金额/数量',
                            'attribute' => 'goods_amount_count',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['goods_amount_count']).'/'.$data['goods_num_count'].'个';
                            },
                        ],
                        [
                            'header' => '配送方式',
                            'attribute' => 'accept_delivery_type',
                            'value' => function ($data) {
                                return ArrayUtils::getArrayValue($data['accept_delivery_type'],GoodsConstantEnum::$deliveryTypeArr);
                            },
                        ],
                        [
                            'header' => '收货地址',
                            'attribute' => 'accept_address',
                            'value' => function ($data) {
                                return $data['accept_province_text'].$data['accept_city_text'].$data['accept_county_text'].$data['accept_community'].$data['accept_address'];
                            },
                        ],
                        [
                            'attribute' => 'accept_name',
                            'header' => '收货人姓名',
                        ],
                        [
                            'attribute' => 'accept_mobile',
                            'header' => '收货人电话',
                        ],
                        [
                            'header' => '订单类型',
                            'attribute' => 'order_type',
                            'value' => function ($data) {
                                return ArrayUtils::getArrayValue($data['order_type'],Order::getOrderTypeArr());
                            },
                        ],
                        [
                            'header' => '备注',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::tag("label", OrderService::generateNote($data) ,['class'=>'label label-danger']);;
                            },
                        ],

                        [
                            'header' => '订单归属',
                            'attribute' => 'order_owner',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['order_owner'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'attribute' => 'delivery_name',
                            'header' => '配送点联系人',
                        ],
                        [
                            'attribute' => 'delivery_phone',
                            'header' => '配送点联系电话',
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{detail}{cancel}',
                            'headerOptions' => ['width' => '130'],
                            'buttons' => [
                                'detail' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("订单详情",['/order/detail','order_no'=>$model['order_no']],'btn btn-xs btn-info','fa fa-share');
                                },
                                'cancel' => function ( $url, $model, $key) {
                                    if ($model['order_status']==Order::ORDER_STATUS_PREPARE){
                                        return BackendViewUtil::generateOperationATag("取消订单",['/order/cancel','order_no'=>$model['order_no']],'btn btn-xs btn-danger','fa fa-trash',"确认取消订单？将直接退款");
                                    }
                                    return "";
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
