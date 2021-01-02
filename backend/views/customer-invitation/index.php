<?php

use common\models\Common;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use common\models\Customer;

/* @var  backend\models\searches\CustomerInvitationSearch $searchModel */
/* @var  Customer $customerModel */
/* @var  array $parentCustomer */
/* @var ActiveDataProvider $dataProvider */
$this->title = '用户邀请分润详情';
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['/user-info/index']];
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
                <h3 class="page-heading"><?= $customerModel['nickname'] ?> ——邀请分润详情</h3>
                <h4 class="page-heading">
                    <?php if (!empty($parentCustomer)):?>
                        其上级为：<?=$parentCustomer['nickname']?>
                        <?php echo Html::a('查看上级详情',['/user-info/index','UserInfoSearch[id]'=>$parentCustomer['user_id']],['class'=>'btn  btn-info'])?>
                    <?php else:?>
                        未绑定上级
                    <?php endif;?>
                </h4>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'class' => 'kartik\grid\ExpandRowColumn',
                            'detailAnimationDuration'=>'fast',
                            'value' => function ($model, $key, $index, $column) {
                                return GridView::ROW_COLLAPSED;
                            },
                            'detail' => function ($model, $key, $index, $column) {
                                return Yii::$app->controller->renderPartial('expand_details_item', ['oneLevelModel' => $model]);
                            },
                            'headerOptions' => ['class' => 'kartik-sheet-style'],
                            'expandOneOnly' => true
                        ],
                        [
                            'header' => '二级用户数',
                            'headerOptions' => ['width' => '60'],
                            'format' => 'raw',
                            'value' => function ($data) {
                                return count($data['children']);
                            },
                        ],
                        [
                            'header' => '客户头像',
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
                                return $data['head_img_url'];
                            },
                        ],
                        [
                            'header' => '客户昵称/姓名',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['nickname'].'/'.$data['realname'];
                            },
                        ],
                        [
                            'header' => '客户手机号',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['phone'];
                            },
                        ],
                        [
                            'header' => '客户等级',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['level_text'];
                            },
                        ],
                        [
                            'header' => '预估佣金/实际佣金',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if (!key_exists('statistics',$data))return"";
                                return Common::showAmountWithYuan($data['statistics']['amount']).'/'.Common::showAmountWithYuan($data['statistics']['amount_ac']);
                            },
                        ],
                        [
                            'header' => '订单金额',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if (!key_exists('statistics',$data))return"";
                                return Common::showAmountWithYuan($data['statistics']['order_amount']);
                            },
                        ],
                        [
                            'header' => '订单数量',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if (!key_exists('statistics',$data))return"";
                                return $data['statistics']['order_count'];
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
