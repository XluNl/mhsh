<?php

use backend\services\CouponBatchService;
use backend\services\CouponService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\models\Order;
use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use \yii\bootstrap\Html;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsScheduleCollectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '排期列表';
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
                <?= Html::a('新增排期活动', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'owner_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['owner_type'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'header' => '归属名称',
                            'attribute' => 'owner_name',
                        ],
                        'collection_name',
                        [
                            'header' => '展示时间',
                            'attribute' => 'display_start',
                            'format' => 'raw',
                            'headerOptions' => ['width' => '156'],
                            'value' => function ($data) {
                                return $data['display_start'].'<br/>'.$data['display_end'];
                            },
                        ],
                        [
                            'header' => '售卖时间',
                            'attribute' => 'online_time',
                            'format' => 'raw',
                            'headerOptions' => ['width' => '156'],
                            'value' => function ($data) {
                                return $data['online_time'].'<br/>'.$data['offline_time'];
                            },
                        ],
                        'operation_name',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{delete}{detail}{output}',
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/goods-schedule-collection/modify','id'=>$model['id']],'btn  btn-xs   btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/goods-schedule-collection/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn  btn-xs  btn-danger','fa fa-trash',"确认删除？");
                                },
                                'detail' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("详情",['/goods-schedule/index','GoodsScheduleSearch[collection_id]'=>$model->id],'btn  btn-xs  btn-info','fa fa-cloud-upload');
                                },
                                'output' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("采购单",['/download/purchase-list-collection','collection_id'=>$model['id']],'btn btn-xs btn-success','fa fa-download');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
