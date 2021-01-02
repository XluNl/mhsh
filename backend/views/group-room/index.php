<?php

use backend\utils\BackendViewUtil;
use common\models\GroupRoom;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GroupRoomSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '拼团房间列表';
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
        <?php echo $this->render('filter', ['status' => $searchModel->status]); ?>
        <div class="box-body" style="text-align: center">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'active_no',
                    'room_no',
                    'team_name',
                    'continued',
                    'place_count',
                    'paid_order_count',
                    'min_level',
                    'max_level',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($data) {
                            return BackendViewUtil::getArrayWithLabel($data['status'], GroupRoom::$groupRoomStatus, GroupRoom::$groupRoomStatusCss);
                        },
                    ],
                    'finished_at',
                    'msg',
                    'created_at',
                    'updated_at',
                    [
                        'header' => '操作',
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{orders}',
                        'headerOptions' => ['width' => '150'],
                        'buttons' => [
                            'orders' => function ($url, $model, $key) {
                                return BackendViewUtil::generateOperationATag("拼团订单", ['/group-room-order/index', 'GroupRoomOrderSearch[room_no]' => $model['room_no']], 'btn btn-xs btn-success', 'fa fa-share',null,['target'=>'_blank']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>

    </div>
</div>