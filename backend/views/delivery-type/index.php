<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\DeliveryType;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $deliveryModel Delivery */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '配送方式列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="page-heading"><?php echo $deliveryModel['nickname']?>(<?php echo $deliveryModel['realname'].'-'.$deliveryModel['phone']?>)的配送方案</h3>
                <?= Html::a('新增配送方案', ['modify','delivery_id'=>$deliveryModel['id']], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'delivery_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['delivery_type'],GoodsConstantEnum::$deliveryTypeArr,GoodsConstantEnum::$deliveryTypeCssArr);
                            },
                        ],
                        [
                            'header' => '配送费用',
                            'attribute' => 'params',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['params']);
                            },
                        ],
                        'updated_at',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}|{delete}{up}{down}',
                            'headerOptions' => ['width' => '190'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key)use ($deliveryModel) {
                                    return BackendViewUtil::generateOperationATag("修改",['/delivery-type/modify','id'=>$model['id'],'delivery_id'=>$deliveryModel['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/delivery-type/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                },
                                'up' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/delivery-type/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-cloud-upload',"确认启用？");
                                },
                                'down' => function ($url, $model, $key) {
                                    if ($model->status==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("禁用",['/delivery-type/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-warning','fa fa-cloud-download',"确认禁用？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
