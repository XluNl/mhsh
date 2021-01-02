<?php

use backend\services\OrderService;
use backend\utils\BackendViewUtil;
use common\models\BusinessApply;
use common\models\GoodsConstantEnum;
use kartik\popover\PopoverX;
use \yii\bootstrap\Html;
use kartik\grid\GridView;
use \common\models\Common;
use \common\models\Order;
use \common\models\Payment;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\forms\DeliveryGoodsListSearchForms */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '团长确认接收列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

    <?php  echo $this->render('delivery-goods-list_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">

            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'header' => '统计类型',
                            'format' => 'raw',
                            'attribute' => 'order_owner',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['order_owner'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'attribute' => 'nickname',
                            'header' => '配送团长',
                        ],
                        [
                            'attribute' => 'phone',
                            'header' => '手机号',
                        ],
                        [
                            'header' => '地址',
                            'value' => function ($data) {
                                return $data['province_text'].$data['city_text'].$data['county_text'].$data['community'].$data['address'];
                            },
                        ],
                        [
                            'header' => '当日送货件量',
                            'attribute' => 'goods_num_count',
                            'value' => function ($data) {
                                return $data['goods_num_count'];
                            },
                        ],
                        [
                            'header' => '商品总金额',
                            'attribute' => 'goods_amount_count',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['goods_amount_count']);
                            },
                        ],

                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{detail}',
                            'headerOptions' => ['width' => '130'],
                            'buttons' => [
                                'detail' => function ($url, $model, $key) use ($searchModel) {
                                    return BackendViewUtil::generateOperationATag("配送订单",['/delivery-management/order-delivery-index','OrderDeliverySearchForms[owner_type]'=>$model['order_owner'],'OrderDeliverySearchForms[delivery_id]'=>$model['id'],'OrderDeliverySearchForms[expect_arrive_time]'=>$searchModel['expect_arrive_time']],'btn btn-xs btn-info','fa fa-share');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
