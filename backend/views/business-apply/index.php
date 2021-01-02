<?php

use backend\utils\BackendViewUtil;
use common\models\BusinessApply;
use common\models\GoodsConstantEnum;
use kartik\popover\PopoverX;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\BusinessApplySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '申请列表';
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
                <?php  echo $this->render('filter', ['type' => $searchModel->type]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],BusinessApply::$applyTypeArr,BusinessApply::$applyTypeCssArr);
                            },
                        ],
                        'nickname',
                        'realname',
                        'phone',
                        'em_phone',
                        'wx_number',
                        'occupation',
                        'community',
                        'address',
                        [
                            'attribute' => 'action',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['action'],BusinessApply::$actionArr,BusinessApply::$actionCssArr);
                            },
                        ],
                        'operator_name',
                        'operator_remark',
                        [
                            'header' => '图片',
                            'format' => 'raw',
                            'value' => function ($data){
                                $imageHtml = "";
                                if ($data['type']==BusinessApply::APPLY_TYPE_HA){
                                    $imageHtml .=PopoverX::widget([
                                        'header' => '门店图片',
                                        'placement' => PopoverX::ALIGN_LEFT,
                                        'size' => PopoverX::SIZE_LARGE,
                                        'content' => $this->render('pop-view', ['images' =>$data['ext_images']]),
                                        'toggleButton' => ['label'=>'门店图片', 'class'=>'btn btn-default'],
                                    ]);
                                    $imageHtml .=PopoverX::widget([
                                        'header' => '门店资质',
                                        'placement' => PopoverX::ALIGN_LEFT,
                                        'size' => PopoverX::SIZE_LARGE,
                                        'content' => $this->render('pop-view', ['images' =>$data['images']]),
                                        'toggleButton' => ['label'=>'门店资质', 'class'=>'btn btn-default'],
                                    ]);
                                }
                                return $imageHtml;
                            },
                        ],
                        'updated_at',
                        [
                            'header' => '审核操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{delete}{accept}{deny}',
                            'headerOptions' => ['width' => '210'],
                            'buttons' =>[
                                'delete' => function ( $url, $model, $key) {
                                    if ($model->action==BusinessApply::ACTION_APPLY){
                                        return BackendViewUtil::generateOperationATag("删除",['/business-apply/operation','id'=>$model['id'],'commander'=>BusinessApply::ACTION_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                    }
                                    return "";
                                },
                                'accept' => function ($url, $model, $key) {
                                    if ($model->action==BusinessApply::ACTION_APPLY){
                                        return BackendViewUtil::generateOperationATag("通过",['/business-apply/operation','id'=>$model['id'],'commander'=>BusinessApply::ACTION_ACCEPT],'btn btn-xs btn-success','fa fa-cloud-upload',"确认通过？");
                                    }
                                    return "";
                                },
                                'deny' => function ($url, $model, $key) {
                                    if ($model->action==BusinessApply::ACTION_APPLY){
                                        return BackendViewUtil::generateOperationATag("拒绝",['/business-apply/operation','id'=>$model['id'],'commander'=>BusinessApply::ACTION_DENY],'btn btn-xs btn-warning','fa fa-cloud-download',"确认拒绝？");
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