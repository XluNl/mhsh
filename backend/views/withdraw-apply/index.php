<?php

use backend\models\ModelViewUtils;
use backend\utils\BackendViewUtil;
use common\models\BizTypeEnum;
use common\models\BusinessApply;
use common\models\Common;
use common\models\WithdrawApply;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DistributeBalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '提现申请列表';
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
                <?php  echo $this->render('filter', ['bizType' => $searchModel->biz_type]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'header' => '账户类型',
                            'attribute' => 'biz_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['biz_type'],BizTypeEnum::$bizTypeArr,BizTypeEnum::$bizTypeCssArr);
                            },
                        ],
                        [
                            'header' => '账户名',
                            'attribute' => 'biz_name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Html::a($data['biz_name'],['distribute-balance/index','DistributeBalanceSearch[biz_type]'=>$data['biz_type'],'DistributeBalanceSearch[biz_id]'=>$data['biz_id']]);
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],WithdrawApply::$typeArr,WithdrawApply::$typeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'amount',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['amount']);
                            },
                        ],
                        [
                            'attribute' => 'audit_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['audit_status'],WithdrawApply::$auditStatusArr,WithdrawApply::$auditStatusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'process_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['process_status'],WithdrawApply::$processStatusArr,WithdrawApply::$processStatusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'is_return',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_return'],WithdrawApply::$isReturnArr,WithdrawApply::$isReturnCssArr);
                            },
                        ],
                        'remark',
                        'audit_remark',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{account}{auditAccept}{auditDeny}{processDeal}{refund}',
                            'buttons' =>[
                                'account' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("查看账户",['distribute-balance/index','DistributeBalanceSearch[biz_type]'=>$model['biz_type'],'DistributeBalanceSearch[biz_id]'=>$model['biz_id']],'btn btn-xs btn-info');
                                },
                                'auditAccept' => function ( $url, $model, $key) {
                                    if ($model['audit_status']!=WithdrawApply::AUDIT_STATUS_APPLY){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核通过',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-success btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' => '审核通过',
                                        'data-id' => $model['id'],
                                        'data-commander'=>WithdrawApply::AUDIT_STATUS_ACCEPT,
                                    ]);
                                },
                                'auditDeny' => function ( $url, $model, $key) {
                                    if ($model['audit_status']!=WithdrawApply::AUDIT_STATUS_APPLY){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核拒绝',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-warning btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' =>'审核拒绝',
                                        'data-id' => $model['id'],
                                        'data-commander'=>WithdrawApply::AUDIT_STATUS_DENY,
                                    ]);
                                },


                                'processDeal' => function ( $url, $model, $key) {
                                    if ($model['audit_status']==WithdrawApply::AUDIT_STATUS_ACCEPT&&$model['process_status']==WithdrawApply::PROCESS_STATUS_UN_DEAL){
                                        if ($model['type']==WithdrawApply::TYPE_OFFLINE){
                                            return BackendViewUtil::generateOperationATag("已完成打款",['/withdraw-apply/process','id'=>$model['id']],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认已经完成线下打款？");
                                        }
                                        else if ($model['type']==WithdrawApply::TYPE_WECHAT){
                                            if ($model['process_status']==WithdrawApply::PROCESS_STATUS_UN_DEAL){
                                                return BackendViewUtil::generateOperationATag("发起微信打款",['/withdraw-apply/process','id'=>$model['id']],'btn btn-xs btn-success','fa fa-cloud-upload',"确认发起微信打款？");
                                            }
                                        }
                                    }
                                    return "";
                                },
                                'refund' => function ( $url, $model, $key) {
                                    if ($model['audit_status']==WithdrawApply::AUDIT_STATUS_ACCEPT&&$model['process_status']==WithdrawApply::PROCESS_STATUS_UN_DEAL){
                                        return BackendViewUtil::generateOperationATag("退回余额",['/withdraw-apply/refund','id'=>$model['id']],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认退回余额，本次提现结束？");
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
<?php
$modelId = 'audit_note';
echo $this->render('../layouts/modal-view-h', [
    'modelType'=>'modal-view-rows',
    'modalId' => $modelId,
    'title'=>'添加审核备注',
    'requestUrl'=>Url::to(['/withdraw-apply/audit-operation']),
    'columns'=>[
        [
            'key'=>'audit_note','title'=>'备注','type'=>'textarea',
            'content'=> Html::textarea('audit_note','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"audit_note"),
                ]))
        ],
        [
            'key'=>'id','title'=>'提现单id','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('id',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"id"),
                ]))
        ],
        [
            'key'=>'commander','title'=>'审核结果','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('commander',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"commander"),
                ]))
        ],
    ],
]); ?>