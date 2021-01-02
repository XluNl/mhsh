<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DeliverySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '配送团长列表';
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
                            'attribute' => 'allow_order',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['allow_order'],Delivery::$allowOrderArr,Delivery::$allowOrderCssArr);
                            },
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],Delivery::$typeArr,Delivery::$typeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'auth',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['auth'],Delivery::$authStatusArr,Delivery::$authStatusCssArr);
                            },
                        ],
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{delete}{up}{down}{freight}{goodsDelivery}{platform-royalty}',
                            'headerOptions' => ['width' => '288'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/delivery/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/delivery/operation','id'=>$model['id'],'commander'=>GoodsConstantEnum::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                },
                                'up' => function ($url, $model, $key) {
                                    if ($model->allow_order==Delivery::ALLOW_ORDER_TRUE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("允许下单",['/delivery/operation-allow-order','id'=>$model['id'],'commander'=>Delivery::ALLOW_ORDER_TRUE],'btn btn-xs btn-success','fa fa-cloud-upload',"确认允许下单？");
                                },
                                'down' => function ($url, $model, $key) {
                                    if ($model->allow_order==Delivery::ALLOW_ORDER_FALSE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("拒绝下单",['/delivery/operation-allow-order','id'=>$model['id'],'commander'=>Delivery::ALLOW_ORDER_FALSE],'btn btn-xs btn-warning','fa fa-cloud-download',"确认拒绝下单？");
                                },
                                'freight' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("配送方式",['/delivery-type/index','delivery_id'=>$model['id']],'btn btn-xs btn-danger','fa fa-money',null,['target'=>'_blank']);
                                },
                                'goodsDelivery' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("投放商品",['/delivery/goods-delivery','delivery_id'=>$model['id']],'btn btn-xs btn-info','fa fa-envelope',null,['target'=>'_blank']);
                                },
                                'platform-royalty' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("平台提成",['/delivery/platform-royalty','delivery_id'=>$model['id']],'btn btn-xs btn-primary','fa fa-envelope',null,['target'=>'_blank']);
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
