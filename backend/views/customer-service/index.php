<?php

use backend\models\ModelViewUtils;
use backend\utils\BackendViewUtil;
use common\models\GoodsConstantEnum;
use common\models\OrderCustomerService;
use common\models\WithdrawApply;
use kartik\popover\PopoverX;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsScheduleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array */
/* @var $deliveryOptions array */
$this->title = '售后列表';
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
        <?php  echo $this->render('_search', ['model' => $searchModel,'deliveryOptions'=>$deliveryOptions]); ?>
    </div>

    <div class="row">
        <div class="box box-success">
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'order_no',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::generateOperationATag($data['order_no'],['order/detail','order_no'=>$data['order_no']]);
                            },
                        ],
                        [
                            'header' => '订单类型',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if (!key_exists('order',$data->relatedRecords)){
                                    return "";
                                }
                                return BackendViewUtil::getArrayWithLabel($data['order']['order_owner'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],OrderCustomerService::$typeArr,OrderCustomerService::$typeCssArr);
                            },
                        ],
                        [
                            'header' => '图片和文本',
                            'format' => 'raw',
                            'value' => function ($data){
                                return  PopoverX::widget([
                                    'header' => '申请售后说明',
                                    'placement' => PopoverX::ALIGN_LEFT,
                                    'size' => PopoverX::SIZE_LARGE,
                                    'content' => $this->render('pop-view', ['images' =>$data['images'],'messages' =>$data['remark']]),
                                    'toggleButton' => ['label'=>'查看', 'class'=>'btn btn-default'],
                                ]);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],OrderCustomerService::$statusArr,OrderCustomerService::$statusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'audit_level',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['audit_level'],OrderCustomerService::$auditLevelArr,OrderCustomerService::$auditLevelCssArr);
                            },
                        ],
                        [
                            'header' => '配送点',
                            'value' => function ($data) {
                                if (!key_exists('delivery',$data->relatedRecords)){
                                    return "";
                                }
                                return $data['delivery']['nickname']."({$data['delivery']['realname']}-{$data['delivery']['phone']})";
                            },
                        ],
                        'created_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{detail}{up}{down}',
                            'headerOptions' => ['width' => '50'],
                            'buttons' =>[
                                'detail' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("详情",['/customer-service/detail','id'=>$model['id']],'btn btn-xs btn-info','fa fa-file-text');
                                },
                                'up' => function ($url, $model, $key) {
                                    if ($model->status!=OrderCustomerService::STATUS_UN_DEAL||$model->audit_level!=OrderCustomerService::AUDIT_LEVEL_AGENT){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核通过',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-success btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_remark' => '审核通过',
                                        'data-id' => $model['id'],
                                        'data-commander'=>OrderCustomerService::STATUS_ACCEPT,
                                    ]);
                                },
                                'down' => function ($url, $model, $key) {
                                    if ($model->status!=OrderCustomerService::STATUS_UN_DEAL||$model->audit_level!=OrderCustomerService::AUDIT_LEVEL_AGENT){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核拒绝',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-warning btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_remark' =>'审核拒绝',
                                        'data-id' => $model['id'],
                                        'data-commander'=>OrderCustomerService::STATUS_DENY,
                                    ]);
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
    'requestUrl'=>Url::to(['/customer-service/operation']),
    'columns'=>[
        [
            'key'=>'audit_remark','title'=>'备注','type'=>'textarea',
            'content'=> Html::textarea('audit_remark','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"audit_remark"),
                ]))
        ],
        [
            'key'=>'id','title'=>'售后单id','type'=>'hiddenInput',
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