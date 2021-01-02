<?php

use common\models\Customer;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use backend\utils\BackendViewUtil;

/* @var  backend\models\searches\CustomerInvitationSearch $searchModel */
/* @var  Customer $customerModel */
/* @var  array $parentCustomer */
/* @var ActiveDataProvider $dataProvider */
$this->title = '合伙人列表';
$this->params['breadcrumbs'][] = ['label' => '合伙人统计', 'url' => ['/partner/index']];
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
                                return $data['head_img_url'];
                            },
                        ],
                        [
                            'header' => '昵称/姓名',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['nickname'].'/'.$data['realname'];
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
                            'header' => '地址',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['address'];
                            },
                        ],
                        [
                            'header' => '粉丝数',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['user_count']?:0;
                            },
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{detail}{output}',
                            'headerOptions' => ['width' => '130'],
                            'buttons' => [
                                'detail' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("查看",['/partner/fans','UserSearch[delivery_id]'=>$model['id']],'btn btn-xs btn-info','fa fa-share');
                                }
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
