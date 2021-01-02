<?php

use common\models\Customer;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var  backend\models\searches\CustomerInvitationSearch $searchModel */
/* @var  Customer $customerModel */
/* @var  array $parentCustomer */
/* @var ActiveDataProvider $dataProvider */
$this->title = '粉丝详情';
$this->params['breadcrumbs'][] = ['label' => '合伙人列表', 'url' => ['/partner/partner']];
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
                <h3 class="page-heading"></h3>
                <h4 class="page-heading"></h4>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'header' => '头像',
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
                                return $data['headimgurl'];
                            },
                        ],
                        [
                            'header' => '昵称',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['nickname'];
                            },
                        ],
                        [
                            'header' => '手机号',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['phone'];
                            },
                        ],
                        [
                            'header' => '订单数',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['order_count'];
                            },
                        ],
                        [
                            'header' => '订单金额',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['amount_sum'];
                            },
                        ]
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
