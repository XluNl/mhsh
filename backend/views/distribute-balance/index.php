<?php

use backend\models\BackendCommon;
use backend\utils\BackendViewUtil;
use common\models\BizTypeEnum;
use common\models\Common;
use yii\bootstrap\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DistributeBalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '分润账户列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body th {
        text-align: center;
    }
</style>
<div class="container-fluid">

    <div style="margin-left: -15px;margin-right: -15px;">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php if (BackendCommon::isSuperCompany(BackendCommon::getFCompanyId())) {
                    echo Html::a('余额扣款', ['claim'], ['class' => 'btn btn-warning btn-sm']);
                }
                echo Html::a('申请提现', ['withdraw'], ['class' => 'btn btn-primary btn-sm']);
                ?>
            </div>
            <div class="panel with-nav-tabs panel-primary" style="text-align: center">
                <?php echo $this->render('filter', ['bizType' => $searchModel->biz_type]); ?>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'header' => '账户类型',
                                'attribute' => 'biz_type',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['biz_type'], BizTypeEnum::$bizTypeArr, BizTypeEnum::$bizTypeCssArr);
                                },
                            ],
                            [
                                'header' => '账户名',
                                'attribute' => 'search_name',
                            ],
                            [
                                'header' => '账户手机号',
                                'attribute' => 'search_phone',
                            ],
                            [
                                'attribute' => 'amount',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['amount']);
                                },
                            ],
                            [
                                'attribute' => 'remain_amount',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['remain_amount']);
                                },
                            ],
                            'created_at',
                            'updated_at',
                            [
                                'header' => '操作',
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{detail}',
                                'buttons' => [
                                    'detail' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("详情", ['/distribute-balance-item/index', 'DistributeBalanceItemSearch[distribute_balance_id]' => $model['id']], 'btn btn-xs btn-success', 'fa fa-share');
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>