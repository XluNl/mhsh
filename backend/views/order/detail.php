<?php

use backend\assets\StepsAsset;
use backend\models\ModelViewUtils;
use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderLogs;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

StepsAsset::register($this);

/* @var  array $orderStatusFlow */
/* @var array $orderItems */

$orderGoodsProvider = new ArrayDataProvider([
    'allModels' => $model['goods'],
    'sort' => [
    ],
    'pagination' => [
        'pageSize' => 10,
    ],
]);
$orderLogProvider = new ArrayDataProvider([
    'allModels' => $model['logs'],
    'sort' => [
    ],
    'pagination' => [
        //'pageSize' => 10,
    ],
]);

$this->title = '订单详情';
$this->params['breadcrumbs'][] = ['label' => '订单列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php echo Html::cssFile("@web/css/main.css"); ?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
    .step-line  {
        top: 27px;
    }
    .step-icon-custom-box {
        width: 56px;
        height: 56px;
    }
    .step-icon-custom {
        margin-top: -2px;
        font-size: 28px;
    }
</style>
<div class="container-fluid">
	<h1 class="page-heading">订单详情（<?=$model['order_no']?>）</h1>
	<div
		class="alert alert-info">
		<p>
            <?php echo Html::button(Html::tag('i','添加管理员备注',['class'=>'fa fa-plus']), [
                'class' => 'admin_note btn btn-primary',
                'data-toggle' => 'modal',
                'data-admin_note' => $model['admin_note'],
                'data-order_no' => $model['order_no'],
            ])
            ?>
            <?php if ($model['order_status']==Order::ORDER_STATUS_UN_PAY){
                echo BackendViewUtil::generateOperationATag("取消订单",['/order/cancel','order_no'=>$model['order_no']],'btn btn-danger','fa fa-check-circle',"确认取消订单？");
            } ?>
            <?php if ($model['order_status']==Order::ORDER_STATUS_PREPARE){
                //echo BackendViewUtil::generateOperationATag("发货",['/order/delivery-out','order_no'=>$model['order_no']],'btn btn-warning','fa fa-send',"确认发货？");
                echo BackendViewUtil::generateOperationATag("取消订单",['/order/cancel','order_no'=>$model['order_no']],'btn btn-danger','fa fa-check-circle',"确认取消订单？");
            } ?>
            <?php if ($model['order_status']==Order::ORDER_STATUS_RECEIVE){
                echo BackendViewUtil::generateOperationATag("完成订单",['/order/complete','order_no'=>$model['order_no']],'btn btn-success','fa fa-check-circle',"确认完成订单？");
            }?>

            <?php if ($model['customer_service_status']==Order::CUSTOMER_SERVICE_STATUS_TRUE){
                echo BackendViewUtil::generateOperationATag("查看关联售后单",['/customer-service/index','OrderCustomerServiceSearch[order_no]'=>$model['order_no']],'btn btn-primary','fa fa-file-text');
            }?>
		</p>
	</div>
	<div class="panel panel-default panel-no-border">
		<div class="the-box">
            <div class="box box-success">
                <div class="box-header with-border">

                </div>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $orderGoodsProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'schedule_id',
                            [
                                'header' => '商品类型',
                                'attribute' => 'goods_owner',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['goods_owner'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                                },
                            ],
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
                                'template' => '{edit}',
                                'buttons' => [
                                    'edit' => function ($url, $model, $key) {
                                        return Html::button(Html::tag('i','校准数量',['class'=>'fa fa-plus']), [
                                            'class' => 'admin_goods_fix btn btn-primary btn-xs',
                                            'data-toggle' => 'modal',
                                            'data-order_no' => $model['order_no'],
                                            'data-order_goods_id' => $model['id'],
                                            'data-num' => $model['num'],
                                            'data-num_ac' => $model['num_ac'],
                                        ]);
                                    },

                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
			<div class="row">
                <div class="col-sm-8 col-sm-offset-2">
                    <div id="orderStatusFlow">
                    </div>
                </div>
			</div>
			<br/>
            <br/>
            <br/>
			<hr>
			<div class="row" style="text-align: center">
				<div class="col-sm-3">
					<h4 class="small-heading more-margin-bottom">订单信息</h4>
                    <?php foreach ($orderItems['orderInfo'] as $value ):?>
                    <div class="form-group">
                            <label class="control-label"><?= $value['title'] ?></label>
                            <div>
                                <p class="form-control-static"><?= $value['text'] ?></p>
                            </div>
                    </div>
                     <?php endforeach;?>
                </div>
                <div class="col-sm-3">
                    <h4 class="small-heading more-margin-bottom">用户信息</h4>
                    <?php foreach ($orderItems['userInfo'] as $value ):?>
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
                    <?php foreach ($orderItems['deliveryInfo'] as $value ):?>
                        <div class="form-group">
                            <label class="control-label"><?= $value['title'] ?></label>
                            <div>
                                <p class="form-control-static"><?= $value['text'] ?></p>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
                <div class="col-sm-3">
                    <h4 class="small-heading more-margin-bottom">分润信息</h4>
                    <?php foreach ($orderItems['shareInfo'] as $value ):?>
                        <div class="form-group">
                            <label class="control-label"><?= $value['title'] ?></label>
                            <div>
                                <p class="form-control-static"><?= $value['text'] ?></p>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
			</div>

            <div class="box box-success">
                <div class="box-header with-border">

                </div>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $orderLogProvider,
                        'columns' => [
                            ['class' => 'backend\components\InverseSerialColumn'],
                            [
                                'attribute' => 'role',
                                'value' => function ($data) {
                                    return ArrayUtils::getArrayValue($data['role'],OrderLogs::$role_list);
                                },
                            ],
                            'user_id',
                            'name',
                            [
                                'attribute' => 'action',
                                'value' => function ($data) {
                                    return ArrayUtils::getArrayValue($data['action'],OrderLogs::$action_list);
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

<?php
$modelId = 'admin_note';
echo $this->render('../layouts/modal-view-h', [
   'modelType'=>'modal-view-rows',
   'modalId' => $modelId,
   'title'=>'添加管理员备注',
   'requestUrl'=>Url::to(['/order/admin-note']),
   'columns'=>[
       [
           'key'=>'admin_note','title'=>'管理员备注','type'=>'textarea',
           'content'=>Html::textarea('admin_note','',
               ModelViewUtils::mergeDefaultOptions([
                   'id'=>ModelViewUtils::getAttrId($modelId,"admin_note"),
               ]))
       ],
       [
           'key'=>'order_no','title'=>'订单号','type'=>'hiddenInput',
           'content'=>Html::hiddenInput('order_no',null,
               ModelViewUtils::mergeDefaultOptions([
                   'id'=>ModelViewUtils::getAttrId($modelId,"order_no"),
               ]))
       ],
   ],
]); ?>

<?php
$modelId = 'admin_goods_fix';
echo $this->render('../layouts/modal-view-h', [
    'modelType'=>'modal-view-tables',
    'modalId' => $modelId,
    'title'=>'校准提货数量',
    'requestUrl'=>Url::to(['/order/upload-weight']),
    'columns'=>[
        [
            'key'=>'order_no','title'=>'订单号','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('order_no',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"order_no"),
                ]))
        ],
        [
            'key'=>'order_goods_id','title'=>'订单商品编号','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('order_goods_id',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"order_goods_id"),
                ]))
        ],
        [
            'key'=>'num','title'=>'商品数量','type'=>'label',
            'content'=> \yii\bootstrap\Html::tag('span','',
                [
                    'id'=>ModelViewUtils::getAttrId($modelId,"num"),
                ])
        ],
        [
            'key'=>'num_ac','title'=>'实际提货数量','type'=>'textInput',
            'content'=>Html::textInput('num_ac','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"num_ac"),
                ]))
        ],
    ],
]); ?>

<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
let stepView = <?= Json::encode(array_values($orderStatusFlow)) ?>;
let stepData = [];
let firstNoActiveIndex = 0;
for (let i = 0; i < stepView.length; i++) {
    let item = {
        title:stepView[i].text,
        description:"",
    };
    stepData.push(item);
    if (stepView[i].activeIndex){
        firstNoActiveIndex = i;
    }
}
let steps1 = steps({
    el: "#orderStatusFlow",
    /*data: [
        { title: "步骤1", description: "" },
        { title: "步骤2", description: "" },
        { title: "步骤3", description: "" }
    ],*/
    data:stepData,
    active: firstNoActiveIndex,
    iconType: "custom",
    center: true,
    //dataOrder: ["title", "line", "description"]
});


<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>





