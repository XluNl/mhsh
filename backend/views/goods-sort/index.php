<?php

use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\GoodsSort;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var  array $bigSortArr */
/* @var  array $smallSortArr */
/* @var  ActiveDataProvider $dataProvider */
/* @var  backend\models\searches\GoodsSortSearch $searchModel */
?>
<style type="text/css">
    .panel-body th {
        text-align: center;
    }
</style>
<div class="container-fluid">
    <h1 class="page-heading">商品分类列表</h1>
    <div class="">
        <p style="">
            <?= Html::a('添加新分类', '/goods-sort/modify', ['class' => 'btn btn-primary']) ?>
        </p>
    </div>

    <div class="panel with-nav-tabs panel-primary" style="text-align: center">
        <?php echo $this->render('filter', ['sortOwner' => $searchModel->sort_owner]); ?>
        <div class="panel-body">
            <?php Pjax::begin(['id' => 'datalist']); ?>
            <div class="col-xs-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-bordered'],
                    'rowOptions' => function ($model, $key, $index, $grid) {
                        return ['style' => 'background:rgb(238, 238, 238)'];
                    },
                    'columns' => [
//                            'id',
                        'sort_name',
                        [
                            'attribute' => 'pic_name',
                            'format' => [
                                'image',
                                [
                                    'onerror' => 'ifImgNotExists(this)',
                                    'class' => 'img-circle',
                                    'width' => '40',
                                    'height' => '40'
                                ]
                            ],
                            'value' => function ($model) {
                                return Common::generateAbsoluteUrl($model['pic_icon']);
                            },
                        ],
                        [
                            'attribute' => 'sort_show',
                            'format' => 'raw',
                            'value' => function ($model) {
                                return BackendViewUtil::getArrayWithLabel($model->sort_show, GoodsSort::$showStatusArr, GoodsSort::$showStatusCssArr);
                            },
                        ],
                        'sort_order',
                        [
                            'header' => '分类操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}|{delete}{sortUp}{sortDown}<br/>',
                            'headerOptions' => ['width' => '190'],
                            'buttons' => [
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改", ['/goods-sort/modify', 'sort_id' => $model['id']], 'btn btn-xs btn-primary', 'fa fa-pencil-square-o');
                                },
                                'delete' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除", ['/goods-sort/delete', 'sort_id' => $model->id], 'btn btn-xs btn-danger', 'fa fa-trash', "确认删除？");
                                },
                                'sortUp' => function ($url, $model, $key) {
                                    if ($model->sort_show == GoodsSort::SHOW_STATUS_SHOW) {
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("显示", ['/goods-sort/operation', 'sort_id' => $model->id, 'commander' => GoodsSort::SHOW_STATUS_SHOW], 'btn btn-xs btn-danger', 'fa fa-cloud-upload', "确认显示？");
                                },
                                'sortDown' => function ($url, $model, $key) {
                                    if ($model->sort_show == GoodsSort::SHOW_STATUS_HIDE) {
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("隐藏", ['/goods-sort/operation', 'sort_id' => $model->id, 'commander' => GoodsSort::SHOW_STATUS_HIDE], 'btn btn-xs btn-warning', 'fa fa-cloud-download', "确认隐藏？");
                                },
                            ],
                        ],

                    ],
                    'afterRow' => function ($model, $key, $index, $grid) {
                        return \Yii::$app->view->render("_item", ['model' => $model]);
                    }
                ]); ?>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
