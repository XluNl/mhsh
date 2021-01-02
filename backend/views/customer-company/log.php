<?php

use common\models\Customer;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;

/* @var  backend\models\searches\CustomerInvitationSearch $searchModel */
/* @var  Customer $customerModel */
/* @var  array $parentCustomer */
/* @var ActiveDataProvider $dataProvider */
$this->title = '客户绑定代理商记录';
$this->params['breadcrumbs'][] = ['label' => '客户绑定', 'url' => ['/customer-company/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

<!--    <div style="margin-left: -15px;margin-right: -15px;">-->
<!--        --><?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<!--    </div>-->
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
<!--                <h3 class="page-heading">--><?//= $customerModel['nickname'] ?><!-- ——邀请分润详情</h3>-->
<!--                <h4 class="page-heading">-->
<!--                    --><?php //if (!empty($parentCustomer)):?>
<!--                        其上级为：--><?//=$parentCustomer['nickname']?>
<!--                        --><?php //echo Html::a('查看上级详情',['/user-info/index','UserInfoSearch[id]'=>$parentCustomer['user_id']],['class'=>'btn  btn-info'])?>
<!--                    --><?php //else:?>
<!--                        未绑定上级-->
<!--                    --><?php //endif;?>
<!--                </h4>-->
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'header' => '代理商名',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['name'];
                            },
                        ],
                        [
                            'header' => '地址',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['address'];
                            },
                        ],
                        [
                            'header' => '最近绑定时间',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['updated_at'];
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
