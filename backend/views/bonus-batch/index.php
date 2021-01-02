<?php

use backend\utils\BackendViewUtil;
use common\models\BonusBatch;
use common\models\CouponBatch;
use kartik\grid\GridView;
use \yii\bootstrap\Html;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\BonusBatchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '奖励金活动列表';
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
                <?= Html::a('新增奖励金活动', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],BonusBatch::$typeArr,BonusBatch::$typeCssArr);
                            },
                        ],
                        [
                            'contentOptions' => ['style'=>'max-width:150px;'],
                            'format'=>'raw',
                            'attribute' => 'batch_no',
                            'value' => function ($data) {
                                return $data['batch_no'];
                            },
                        ],
                        'name',
                        [
                            'headerOptions' => ['width' => '155'],
                            'header' => '领取时间',
                            'attribute' => 'draw_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['draw_start_time'].'<br/>'.$data['draw_end_time'];
                            },
                        ],
                        [
                            'attribute' => 'amount',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['amount']);
                            },
                        ],
                        [
                            'attribute' => 'draw_amount',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['draw_amount']);
                            },
                        ],
                        [
                            'header' => '占比',
                            'value' => function ($data) {
                                return Common::calcPercentWithUnit($data['draw_amount'],$data['amount']);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],BonusBatch::$statusArr,BonusBatch::$statusCssArr);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        'remark',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{delete}{active}{disable}{detail}{draw}',
                            'headerOptions' => ['width' => '315'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/bonus-batch/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/bonus-batch/operation','id'=>$model->id,'commander'=>BonusBatch::STATUS_DELETED],'btn btn-xs  btn-danger','fa fa-trash',"确认删除？");
                                },
                                'active' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/bonus-batch/operation','id'=>$model->id,'commander'=>BonusBatch::STATUS_ACTIVE],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认启用？");
                                },
                                'disable' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("停用",['/bonus-batch/operation','id'=>$model->id,'commander'=>BonusBatch::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-cloud-download',"确认停用？");
                                },
                                'detail' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("记录",['/bonus-batch-draw-log/index','BonusBatchDrawLogSearch[batch_id]'=>$model['id']],'btn btn-xs btn-info','fa fa-file-text');
                                },
                                'draw' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("手动发放",['/bonus-batch-draw-log/draw','batch_no'=>$model['batch_no']],'btn btn-xs btn-info','fa fa-file-text');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
