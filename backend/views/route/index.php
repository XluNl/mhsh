<?php

use backend\services\CouponBatchService;
use backend\services\CouponService;
use backend\utils\BackendViewUtil;
use common\models\Coupon;
use common\models\CouponBatch;
use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use \yii\bootstrap\Html;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\RouteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '司机路线列表';
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
                <?= Html::a('新增司机路线', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        'nickname',
                        'realname',
                        'phone',
                        'em_phone',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'headerOptions' => ['width' => '185'],
                            'template' => '{update}{delete}{active}{disable}',
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/route/modify','id'=>$model['id']],'btn btn-xs  btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/route/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_DELETED],'btn btn-xs  btn-danger','fa fa-trash',"确认删除？");
                                },
                                'active' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/route/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-cloud-upload',"确认启用？");
                                },
                                'disable' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("停用",['/route/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_DISABLED],'btn btn-xs btn-warning','fa fa-cloud-download',"确认停用？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
