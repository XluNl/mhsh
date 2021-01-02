<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\Alliance;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\AllianceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '异业联盟列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
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
                                return BackendViewUtil::getArrayWithLabel($data['type'],Alliance::$typeArr,Alliance::$typeCssArr);
                            },
                        ],
                        'nickname',
                        'phone',
                        'realname',
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
                                return BackendViewUtil::getArrayWithLabel($data['status'],Alliance::$statusArr,Alliance::$statusCssArr);
                            },
                        ],

                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}<br/>{online}{pending}{offline}',
                            'headerOptions' => ['width' => '152'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/alliance/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'online' => function ( $url, $model, $key) {
                                    if ($model['status']==Alliance::STATUS_ONLINE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("开店",['/alliance/operation','id'=>$model['id'],'commander'=>Alliance::STATUS_ONLINE],'btn btn-xs btn-success','fa fa-arrow-up',"确认开店？");
                                },
                                'pending' => function ( $url, $model, $key) {
                                    if ($model['status']==Alliance::STATUS_PENDING){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("暂停营业",['/alliance/operation','id'=>$model['id'],'commander'=>Alliance::STATUS_PENDING],'btn btn-xs btn-warning','fa fa-arrow-down',"确认暂停营业？");
                                },
                                'offline' => function ( $url, $model, $key) {
                                    if ($model['status']==Alliance::STATUS_OFFLINE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("关店",['/alliance/operation','id'=>$model['id'],'commander'=>Alliance::STATUS_OFFLINE],'btn btn-xs btn-danger','fa fa-times',"确认关店？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
