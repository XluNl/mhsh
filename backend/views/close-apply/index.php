<?php

use backend\models\ModelViewUtils;
use backend\utils\BackendViewUtil;
use common\models\BizTypeEnum;
use common\models\CloseApply;
use kartik\popover\PopoverX;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CloseApplySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '关闭申请列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php  echo $this->render('filter', ['type' => $searchModel->biz_type]); ?>
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'biz_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['biz_type'],BizTypeEnum::$bizTypeArr,BizTypeEnum::$bizTypeCssArr);
                            },
                        ],
                        'name',
                        'phone',
                        [
                            'attribute' => 'action',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['action'],CloseApply::$actionArr,CloseApply::$actionCssArr);
                            },
                        ],
                        [
                            'header' => '图片',
                            'format' => 'raw',
                            'value' => function ($data){
                                $imageHtml = "";
                                if ($data['biz_type']==BizTypeEnum::BIZ_TYPE_HA){
                                    $imageHtml .= PopoverX::widget([
                                        'header' => '凭证',
                                        'placement' => PopoverX::ALIGN_LEFT,
                                        'size' => PopoverX::SIZE_LARGE,
                                        'content' => $this->render('pop-view', ['images' =>$data['images']]),
                                        'toggleButton' => ['label'=>'凭证', 'class'=>'btn btn-default'],
                                    ]);
                                }
                                return $imageHtml;
                            },
                        ],
                        'operator_name',
                        'operator_remark',
                        'updated_at',
                        [
                            'header' => '审核操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{accept}{deny}<br/>{delete}',
                            'headerOptions' => ['width' => '152'],
                            'buttons' =>[
                                'delete' => function ( $url, $model, $key) {
                                    if ($model->action==CloseApply::ACTION_APPLY){
                                        return BackendViewUtil::generateOperationATag("删除",['/close-apply/operation','id'=>$model['id'],'commander'=>CloseApply::ACTION_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                    }
                                    return "";
                                },
                                'accept' => function ( $url, $model, $key) {
                                    if ($model['action']!=CloseApply::ACTION_APPLY){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核通过',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-success btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' => '同意',
                                        'data-id' => $model['id'],
                                        'data-commander'=>CloseApply::ACTION_ACCEPT,
                                    ]);
                                },
                                'deny' => function ( $url, $model, $key) {
                                    if ($model['action']!=CloseApply::ACTION_APPLY){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核拒绝',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-warning btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' =>'拒绝',
                                        'data-id' => $model['id'],
                                        'data-commander'=>CloseApply::ACTION_DENY,
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
    'requestUrl'=>Url::to(['/close-apply/operation']),
    'columns'=>[
        [
            'key'=>'audit_note','title'=>'备注','type'=>'textarea',
            'content'=> Html::textarea('audit_note','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"audit_note"),
                ]))
        ],
        [
            'key'=>'id','title'=>'联盟商品审核id','type'=>'hiddenInput',
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