<?php

use backend\utils\BackendViewUtil;
use common\models\BizTypeEnum;
use common\models\BusinessApply;
use common\models\Common;
use common\models\DistributeBalanceItem;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DistributeBalanceItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '分润账户日志';
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

            </div>
            <div class="panel with-nav-tabs panel-primary" style="text-align: center">
                <?php echo $this->render('filter', ['type' => $searchModel->type]); ?>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'biz_type',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['biz_type'], BizTypeEnum::$bizTypeArr, BizTypeEnum::$bizTypeCssArr);
                                },
                            ],
                            [
                                'attribute' => 'type',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['type'], DistributeBalanceItem::$typeArr, DistributeBalanceItem::$typeCssArr);
                                },
                            ],
                            [
                                'attribute' => 'in_out',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['in_out'], DistributeBalanceItem::$inOutArr, DistributeBalanceItem::$inOutCssArr);
                                },
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
                            [
                                'attribute' => 'action',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return BackendViewUtil::getArrayWithLabel($data['action'], DistributeBalanceItem::$actionArr, DistributeBalanceItem::$actionCssArr);
                                },
                            ],
                            'operator_name',
                            [
                                'header' => '备注',
                                'value' => function ($data) {
                                    if ($data['type'] == DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE) {
                                        return $data['order_no'] . "(" . Common::showAmountWithYuan($data['order_amount']) . ")";
                                    } else if ($data['type'] == DistributeBalanceItem::TYPE_WITHDRAW) {
                                        return $data['remark'];
                                    } else {
                                        return $data['remark'];
                                    }
                                },
                            ],
                            'updated_at',
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>