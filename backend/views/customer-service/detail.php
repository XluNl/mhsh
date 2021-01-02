<?php

use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceLog;
use common\models\OrderGoods;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\grid\GridView;
use yii\helpers\Html;


/* @var  $displayVO array */
/* @var $orderGoodsProvider */

$this->title = '售后单详情';
$this->params['breadcrumbs'][] = ['label' => '售后单列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<?php echo Html::cssFile("@web/css/main.css"); ?>
<div class="container-fluid">
	<h1 class="page-heading">售后详情（<?=$model['order_no']?>）</h1>
	<div
		class="alert alert-info">
		<p>
            <?php if ($model['id']==OrderCustomerService::STATUS_UN_DEAL&&$model['audit_level']!=OrderCustomerService::AUDIT_LEVEL_AGENT):?>
            <?= BackendViewUtil::generateOperationATag("通过",['/customer-service/operation','id'=>$model['id'],'commander'=>OrderCustomerService::STATUS_ACCEPT],'btn btn-success','fa fa-cloud-upload',"确认通过？"); ?>
            <?= BackendViewUtil::generateOperationATag("拒绝",['/customer-service/operation','id'=>$model['id'],'commander'=>OrderCustomerService::STATUS_DENY],'btn btn-danger','fa fa-cloud-download',"确认拒绝？");?>
            <?php endif;?>
            <?= BackendViewUtil::generateOperationATag("订单详情",['/order/detail','order_no'=>$model['order_no']],'btn btn-primary','fa fa-file-text');?>
        </p>
	</div>

	<div class="panel panel-default panel-no-border">
		<div class="the-box">
            <div class="box box-info">
                <div class="box-header with-border">
                    售后基本信息
                </div>
                <div class="box-body" style="text-align: center">
                    <div class="row">
                        <div class="col-sm-3">
                            <h4 class="small-heading more-margin-bottom">审核状态</h4>
                            <?php foreach ($displayVO['customerService'] as $value ):?>
                                <div class="form-group">
                                    <label class="control-label"><?= $value['title'] ?></label>
                                    <div>
                                        <p class="form-control-static"><?= $value['text'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach;?>
                        </div>
                        <div class="col-sm-3">
                            <h4 class="small-heading more-margin-bottom">订单信息</h4>
                            <?php foreach ($displayVO['order'] as $value ):?>
                                <div class="form-group">
                                    <label class="control-label"><?= $value['title'] ?></label>
                                    <div>
                                        <p class="form-control-static"><?= $value['text'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach;?>
                        </div>
                        <div class="col-sm-3">
                            <h4 class="small-heading more-margin-bottom">配送信息</h4>
                            <?php foreach ($displayVO['delivery'] as $value ):?>
                                <div class="form-group">
                                    <label class="control-label"><?= $value['title'] ?></label>
                                    <div>
                                        <p class="form-control-static"><?= $value['text'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach;?>
                        </div>
                        <div class="col-sm-3">
                            <h4 class="small-heading more-margin-bottom">收件信息</h4>
                            <?php foreach ($displayVO['receive'] as $value ):?>
                                <div class="form-group">
                                    <label class="control-label"><?= $value['title'] ?></label>
                                    <div>
                                        <p class="form-control-static"><?= $value['text'] ?></p>
                                    </div>
                                </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    售后商品
                </div>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $orderGoodsProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'goods_img',
                                'format' => [
                                    'image',
                                    [
                                        'onerror' => 'ifImgNotExists(this)',
                                        'class' => 'img-circle',
                                        'width'=>'40',
                                        'height'=>'40'
                                    ]
                                ],
                                'value' => function ($data) {
                                    if (!StringUtils::isBlank($data['sku_img'])){
                                        return Common::generateAbsoluteUrl($data['sku_img']);
                                    }
                                    if (!StringUtils::isBlank($data['goods_img'])){
                                        return Common::generateAbsoluteUrl($data['goods_img']);
                                    }
                                    return '';
                                },
                            ],
                            'schedule_name',
                            [
                                'attribute' => 'goods_name',
                                'value' => function ($data) {
                                    return $data['goods_name'].$data['sku_name'];
                                },
                            ],
                            [
                                'attribute' => 'sku_price',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['sku_price']);
                                },
                            ],
                            'num',
                            'num_ac',
                            'sku_unit',
                            [
                                'attribute' => 'discount',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['discount']);
                                },
                            ],
                            [
                                'attribute' => 'amount',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['amount']);
                                },
                            ],
                            [
                                'attribute' => 'amount_ac',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['amount_ac']);
                                },
                            ],
                            [
                                'header' => '退款金额',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['refund_amount']);
                                },
                            ],
                            'expect_arrive_time',
                            [
                                'attribute' => 'delivery_status',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['delivery_status'],OrderGoods::$deliveryStatusArr,OrderGoods::$deliveryStatusCssArr);
                                },
                            ],
                            [
                                'attribute' => 'customer_service_status',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['customer_service_status'],OrderGoods::$customerServiceStatusArr,OrderGoods::$customerServiceStatusCssArr);
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{detail}',
                                'buttons' => [
                                    'edit' => function ($url, $model, $key) {
                                        return Html::a(
                                            Html::tag('i','详情',['class'=>'fa fa-share']),
                                            ['/order/detail','order_no'=>$model['id']],
                                            ['title' => '详情','class'=>'btn btn-info btn-xs'] );
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>

            <div class="box box-success">
                <div class="box-header with-border">
                    操作日志
                </div>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $customerServiceLogProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'operator_name',
                            [
                                'attribute' => 'action',
                                'value' => function ($data) {
                                    return ArrayUtils::getArrayValue($data['action'],OrderCustomerServiceLog::$actionArr);
                                },
                            ],
                            'created_at',
                            'remark',
                        ],
                    ]); ?>
                </div>
            </div>
		</div>
	</div>
</div>

