<?php

use backend\utils\BackendViewUtil;
use common\models\BonusBatch;
use common\models\CommonStatus;
use common\models\CouponBatch;
use common\models\CustomerInvitationActivity;
use common\models\CustomerInvitationActivityPrize;
use kartik\grid\GridView;
use \yii\bootstrap\Html;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerInvitationActivityPrizeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider
 * @var $activityModel
 */
$this->title = '邀请奖励活动奖品列表';
$this->params['breadcrumbs'][] = ['label' => '邀请奖励活动列表', 'url' => ['/customer-invitation-activity/index']];
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
                <h3 class="page-heading"><?php echo $activityModel['name']?>的奖品</h3>
                <?= Html::a('新增奖品', ['modify','activity_id'=>$activityModel['id']], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'level_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['level_type'],CustomerInvitationActivityPrize::$levelTypeArr,CustomerInvitationActivityPrize::$levelTypeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],CustomerInvitationActivityPrize::$typeArr,CustomerInvitationActivityPrize::$typeCssArr);
                            },
                        ],
                        'name',
                        'batch_no',
                        [
                            'header' => '范围',
                            'attribute' => 'range_start',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['range_start'].'~'.$data['range_end'].'个';
                            },
                        ],
                        [
                            'attribute' => 'num',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data['type']==CustomerInvitationActivityPrize::TYPE_COUPON){
                                    return $data['num'];
                                }
                                else if ($data['type']==CustomerInvitationActivityPrize::TYPE_BONUS){
                                    return Common::showAmountWithYuan($data['num']);
                                }
                                else if ($data['type']==CustomerInvitationActivityPrize::TYPE_OTHER){
                                    return $data['num'];
                                }
                                return $data['num'];
                            },
                        ],
                        [
                            'header' => '已发放/库存',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data['type']==CustomerInvitationActivityPrize::TYPE_COUPON){
                                    return $data['real_quantity'].'/'.$data['expect_quantity'];
                                }
                                else if ($data['type']==CustomerInvitationActivityPrize::TYPE_BONUS){
                                    return Common::showAmountWithYuan($data['real_quantity']).'/'.Common::showAmountWithYuan($data['expect_quantity']);
                                }
                                else if ($data['type']==CustomerInvitationActivityPrize::TYPE_OTHER){
                                    return $data['real_quantity'].'/'.$data['expect_quantity'];
                                }
                                return $data['real_quantity'].'/'.$data['expect_quantity'];
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{active}{disable}',
                            'headerOptions' => ['width' => '140'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key)  use($activityModel){
                                    return BackendViewUtil::generateOperationATag("修改",['/customer-invitation-activity-prize/modify','id'=>$model['id'],'activity_id'=>$activityModel['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'active' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/customer-invitation-activity-prize/operation','id'=>$model->id,'activity_id'=>$model->activity_id,'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认启用？");
                                },
                                'disable' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("停用",['/customer-invitation-activity-prize/operation','id'=>$model->id,'activity_id'=>$model->activity_id,'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-cloud-download',"确认停用？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
