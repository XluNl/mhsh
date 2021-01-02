<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsScheduleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array */
$this->title = '分享团长列表';
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
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'nickname',
                        'realname',
                        'phone',
                        'em_phone',
                        'wx_number',
                        'occupation',
                        [
                            'header' => '地址',
                            'value' => function ($data) {
                                return $data['province_text'].$data['city_text'].$data['county_text'].$data['community'].$data['address'];
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        'created_at',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{up}{down}',
                            'headerOptions' => ['width' => '152'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/popularizer/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'up' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/popularizer/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-cloud-upload',"确认启用？");
                                },
                                'down' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("禁用",['/popularizer/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-cloud-download',"确认禁用？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
