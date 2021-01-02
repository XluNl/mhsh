<?php

use backend\services\CouponService;
use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\Coupon;
use common\models\RoleEnum;
use common\utils\ArrayUtils;
use kartik\grid\GridView;
use \yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CouponBatchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '已领优惠券列表';
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
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php  echo $this->render('filter', ['status' => $searchModel->status]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'header' => '归属人',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['customer_name'].'<br/>'.$data['customer_phone'];
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],Coupon::$typeArr,Coupon::$typeCssArr);
                            },
                        ],
                        [
                            //'contentOptions' => ['style'=>'max-width:150px;'],
                            'attribute' => 'coupon_no',
                            'value' => function ($data) {
                                return $data['coupon_no'];
                            },
                        ],
                        'name',
                        [
                            'headerOptions' => ['width' => '155'],
                            'header' => '优惠明细',
                            'value' => function ($data) {
                                return CouponService::generateCouponDesc($data['type'],$data['startup'],$data['discount'],$data['limit_type'],$data['limit_type_params']);
                            },
                        ],
                        [
                            'attribute' => 'restore',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['restore'],Coupon::$restoreArr,Coupon::$restoreCssArr);
                            },
                        ],
                        [
                            'headerOptions' => ['width' => '155'],
                            'header' => '有效期',
                            'attribute' => 'use_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['start_time'].'<br/>'.$data['end_time'];
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],Coupon::$statusArr,Coupon::$statusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'remark',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data['status']==Coupon::STATUS_USED){
                                    return Html::a("订单号:{$data['order_no']}",['/order/index','OrderSearch[order_no]'=>$data['order_no']])
                                        .'<br/>'."使用时间:{$data['use_time']}";
                                }
                                return $data['remark'];
                            },
                        ],
                        [
                            'attribute' => 'draw_operator_name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return ArrayUtils::getArrayValue($data['draw_operator_type'],RoleEnum::$roleList).':'.$data['draw_operator_name'];
                            },
                        ],
                        'created_at',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{discard}',
                            'buttons' =>[
                                'discard' => function ($url, $model, $key) {
                                    if ($model->status!=Coupon::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("作废",['/coupon/discard','id'=>$model['id']],'btn btn-xs btn-danger','fa fa-money','确认作废？');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
