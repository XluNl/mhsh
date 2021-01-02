<?php

use backend\utils\BackendViewUtil;
use common\models\BonusBatch;
use common\models\CommonStatus;
use common\models\CouponBatch;
use common\models\CustomerInvitationActivity;
use common\utils\DateTimeUtils;
use kartik\grid\GridView;
use \yii\bootstrap\Html;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerInvitationActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '邀请奖励活动列表';
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
                <?= Html::a('新增邀请奖励活动', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
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
                                return BackendViewUtil::getArrayWithLabel($data['type'],CustomerInvitationActivity::$typeArr,CustomerInvitationActivity::$typeCssArr);
                            },
                        ],
                        'name',
                        [
                            'headerOptions' => ['width' => '156'],
                            'header' => '展示时间',
                            'attribute' => 'draw_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['show_start_time'].'<br/>'.$data['show_end_time'];
                            },
                        ],
                        [
                            'headerOptions' => ['width' => '156'],
                            'header' => '活动时间',
                            'attribute' => 'activity_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['activity_start_time'].'<br/>'.$data['activity_end_time'];
                            },
                        ],
                        'expect_settle_time',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'settle_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['settle_status'],CustomerInvitationActivity::$settleStatusArr,CustomerInvitationActivity::$settleStatusCssArr);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        [
                            'header' => '备注',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data['settle_status']==CustomerInvitationActivity::SETTLE_STATUS_DEAL){
                                    return "结算人：{$data['settle_operator_name']};结算时间：{$data['settle_time']}";
                                }
                                return '';
                            },
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{active}{disable}{prize}{preStatistic}{trySettle}{result}',
                            'headerOptions' => ['width' => '315'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/customer-invitation-activity/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'active' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/customer-invitation-activity/operation','id'=>$model->id,'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认启用？");
                                },
                                'disable' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("停用",['/customer-invitation-activity/operation','id'=>$model->id,'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-cloud-download',"确认停用？");
                                },
                                'prize' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("奖品",['/customer-invitation-activity-prize/index','CustomerInvitationActivityPrizeSearch[activity_id]'=>$model['id']],'btn btn-xs btn-info','fa fa-file-text');
                                },
                                'preStatistic' => function ($url, $model, $key) {
                                    if ($model['settle_status']===CustomerInvitationActivity::SETTLE_STATUS_UN_DEAL){
                                        return BackendViewUtil::generateOperationATag("预统计",['/customer-invitation-activity/pre-statistic','id'=>$model['id']],'btn btn-xs btn-success','fa fa-file-text');
                                    }
                                    return "";
                                },
                                'trySettle' => function ($url, $model, $key) {
                                    if ($model['settle_status']===CustomerInvitationActivity::SETTLE_STATUS_UN_DEAL&&$model['activity_end_time']<DateTimeUtils::parseStandardWLongDate(time())){
                                        return BackendViewUtil::generateOperationATag("预结算",['/customer-invitation-activity/try-settle','id'=>$model['id']],'btn btn-xs btn-warning','fa fa-file-text');
                                    }
                                    return "";
                                },
                                'result' => function ($url, $model, $key) {
                                    if ($model['settle_status']===CustomerInvitationActivity::SETTLE_STATUS_DEAL){
                                        return BackendViewUtil::generateOperationATag("结算结果",['/customer-invitation-activity-result/index','CustomerInvitationActivityResultSearch[activity_id]'=>$model['id']],'btn btn-xs btn-success','fa fa-file-text');
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
