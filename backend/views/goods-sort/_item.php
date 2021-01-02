<?php

use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\models\GoodsSort;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use backend\models\BackendCommon;
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/02/04/004
 * Time: 1:19
 */



if (!empty($model['subSort'])){
    $provider = new ArrayDataProvider([
        'allModels' => $model['subSort']
    ]);
    echo "<tr><td colspan='12'>";
    echo GridView::widget([
        'dataProvider' => $provider,
        'layout'=>"{items}",
        //'showHeader' => false,
        'tableOptions'=>['class' => 'table table-condensed table-bordered'],

        'columns' => [
            [
                'header' => '',
                'format' => 'raw',
                'headerOptions' => ['width' => '50'],
                'value' => function ($model) {
                    return Html::tag("i","",['class'=>'fa fa-angle-double-right fa-lg']);
                },
            ],
            [
                'attribute' => 'sort_name',
                'headerOptions' => ['width' => '150'],
            ],
            [
                'attribute' => 'pic_name',
                'headerOptions' => ['width' => '530'],
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-circle',
                        'width'=>'40',
                        'height'=>'40'
                    ]
                ],
                'value' => function ($model) {
                    return Common::generateAbsoluteUrl($model->pic_name);
                },
            ],
            [
                'attribute' => 'sort_show',
                'format' => 'raw',
                'headerOptions' => ['width' => '310'],
                'value' => function ($model) {
                    return BackendViewUtil::getArrayWithLabel($model->sort_show,GoodsSort::$showStatusArr,GoodsSort::$showStatusCssArr);
                },
            ],
//            'sort_order',
            [
                'attribute' => 'sort_order',
                'headerOptions' => ['width' => '302'],
            ],
            [
                'header' => '分类操作',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}|{delete}{sortUp}{sortDown}',
                'headerOptions' => ['width' => '190'],
                'buttons' =>[
                    'update' => function ($url, $model, $key) {
                        return BackendViewUtil::generateOperationATag("修改",['/goods-sort/modify','sort_id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                    },
                    'delete' => function ( $url, $model, $key) {
                        return BackendViewUtil::generateOperationATag("删除",['/goods-sort/operation','sort_id'=>$model->id,'commander'=>GoodsConstantEnum::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                    },
                    'sortUp' => function ($url, $model, $key) {
                        if ($model->sort_show==GoodsSort::SHOW_STATUS_SHOW){
                            return "";
                        }
                        return BackendViewUtil::generateOperationATag("显示",['/goods-sort/operation','sort_id'=>$model->id,'commander'=>GoodsSort::SHOW_STATUS_SHOW],'btn btn-xs btn-danger','fa fa-cloud-upload',"确认显示？");
                    },
                    'sortDown' => function ($url, $model, $key) {
                        if ($model->sort_show==GoodsSort::SHOW_STATUS_HIDE){
                            return "";
                        }
                        return BackendViewUtil::generateOperationATag("隐藏",['/goods-sort/operation','sort_id'=>$model->id,'commander'=>GoodsSort::SHOW_STATUS_HIDE],'btn btn-xs btn-warning','fa fa-cloud-download',"确认隐藏？");
                    },
                ],
            ],
        ],
    ]);
    echo "</td></tr>";
}
else{
    echo "<tr><td colspan='12'>";
    echo "</td></tr>";
}

