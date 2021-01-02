<?php

use backend\utils\BackendViewUtil;
use common\models\Banner;
use common\models\CommonStatus;
use kartik\popover\PopoverX;
use yii\bootstrap\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\BannerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Banner列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?= Html::a('新增Banner', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
                <?php  echo $this->render('filter', ['type' => $searchModel->type]); ?>
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],Banner::$typeArr,Banner::$typeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'sub_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['sub_type'],Banner::$subTypeArr,Banner::$subTypeCssArr);
                            },
                        ],
                        'name',
                        [
                            'header' => '图片和文本',
                            'format' => 'raw',
                            'value' => function ($data){
                                return  PopoverX::widget([
                                    'header' => '评论内容',
                                    'placement' => PopoverX::ALIGN_LEFT,
                                    'size' => PopoverX::SIZE_LARGE,
                                    'content' => $this->render('pop-view', ['images' =>$data['images'],'messages' =>$data['messages']]),
                                    'toggleButton' => ['label'=>'查看', 'class'=>'btn btn-default'],
                                ]);
                            },
                        ],
                        'display_order',
                        [
                            'header' => '生效时间',
                            'value' => function ($data) {
                                return $data['online_time'].'-'.$data['offline_time'];
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
                            'template' => '{update}<br/>{delete}<br/>{enable}{disable}',
                            'headerOptions' => ['width' => '100'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/banner/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {

                                    return BackendViewUtil::generateOperationATag("删除",['/banner/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                },
                                'enable' => function ( $url, $model, $key) {
                                    if ($model['status']==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/banner/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-arrow-up',"确认启用？");
                                },
                                'disable' => function ( $url, $model, $key) {
                                    if ($model['status']==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("禁用",['/banner/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-warning','fa fa-arrow-down',"确认禁用？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
