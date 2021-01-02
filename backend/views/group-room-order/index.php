<?php

use backend\models\BackendCommon;
use backend\utils\BackendViewUtil;
use common\models\Order;
use common\utils\StringUtils;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use common\models\GoodsConstantEnum;
use common\models\GroupRoom;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GroupRoomOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array
 * @var $allianceNames array
 */
$this->title = '拼团订单列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body th {
        text-align: center;
    }
</style>
<div class="container-fluid">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="panel with-nav-tabs panel-primary" style="text-align: center">
        <?php echo $this->render('filter', ['order_status' => $searchModel->order_status]); ?>
        <div class="box-body" style="text-align: center">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'caption' => "团订单表",
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'active_no',
                    'room_no',
                    'order_no',
                    [
                        'label' => '归属',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return BackendViewUtil::getArrayWithLabel($model['groupActive']['owner_type'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                        },
                    ],
                    [
                        'label' => '商品名称',
                        'value' => function ($model) {
                            return $model['order']['goods'][0]['goods_name'];
                        },
                    ],
                    [
                        'label' => '订单状态',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return BackendViewUtil::getArrayWithLabel($data['order']['order_status'], Order::$order_status_list, Order::$order_status_list_css);
                        },
                    ],
                    [
                        'attribute' => 'schedule_amount',
                        'value' => function ($model) {
                            return BackendCommon::showAmount($model['schedule_amount']);
                        },
                    ],
                    [
                        'attribute' => 'active_amount',
                        'value' => function ($model) {
                            return StringUtils::isNotBlank($model['active_amount'])?BackendCommon::showAmount($model['active_amount']):"";
                        },
                    ],
                    [
                        'label' => '拼团房间状态',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return BackendViewUtil::getArrayWithLabel($model['groupRoom']['status'],GroupRoom::$groupRoomStatus,GroupRoom::$groupRoomStatusCss);
                        },
                    ],
                    [
                        'header' => '操作',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{detail}',
                        'headerOptions' => ['width' => '130'],
                        'buttons' => [
                            'detail' => function ($url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("详情", ['/order/detail', 'order_no' => $model['order_no']], 'btn btn-xs btn-success', 'fa fa-share',null,['target'=>'_blank']);
                            },
                            'view' => function ($url, $model, $key) {
                                return Html::a('详情', 'javascript:void(0);', ['value' => $model->order_no, 'class' => 'btn btn-info btn-xs group_detail']);
                            }
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

</div>

<script type="text/javascript">

    /*$('.group_detail').on('click', function () {
        var order_no = $(this).attr('value');
        layer.open({
            type: 2,
            area: ['70%', '80%'],
            fixed: false,
            title: '详情',
            maxmin: true,
            content: ["<?= Url::toRoute(['/order/detail']);?>" + '?order_no=' + order_no]
        });
    });*/
</script>