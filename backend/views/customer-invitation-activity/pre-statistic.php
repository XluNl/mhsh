<?php

use common\models\Common;
use common\models\CustomerInvitationActivityPrize;
use common\utils\ArrayUtils;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerInvitationActivitySearch */
/* @var $dataProvider yii\data\ArrayDataProvider
 * @var $sumPrizesDataProvider yii\data\ArrayDataProvider
 */
$this->title = '邀请奖励预计发放活动列表';
$this->params['breadcrumbs'][] = ['label' => '奖励金活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid" style="text-align: center">

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                奖品汇总
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $sumPrizesDataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [

                            'header' => '奖品名称',
                            'attribute' => 'name',
                            'value' => function ($data) {
                                return $data['name'];
                            },
                        ],
                        [

                            'header' => '奖品数量',
                            'attribute' => 'num_text',
                            'value' => function ($data) {
                                return $data['num_text'];
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                邀请明细
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [

                            'header' => '客户名称',
                            'attribute' => 'customer_name',
                            'value' => function ($data) {
                                return $data['customer_name'];
                            },
                        ],
                        [

                            'header' => '客户联系方式',
                            'attribute' => 'customer_phone',
                            'value' => function ($data) {
                                return $data['customer_phone'];
                            },
                        ],
                        [

                            'header' => '一级邀请人数',
                            'attribute' => 'invitation_count',
                            'value' => function ($data) {
                                return $data['invitation_count'];
                            },
                        ],
                        [

                            'header' => '一级已下单人数',
                            'attribute' => 'invitation_order_count',
                            'value' => function ($data) {
                                return $data['invitation_order_count'];
                            },
                        ],
                        [

                            'header' => '二级邀请人数',
                            'attribute' => 'invitation_children_count',
                            'value' => function ($data) {
                                return $data['invitation_children_count'];
                            },
                        ],
                        [

                            'header' => '二级已下单人数',
                            'attribute' => 'invitation_children_order_count',
                            'value' => function ($data) {
                                return $data['invitation_children_order_count'];
                            },
                        ],
                        [
                            'class' => 'kartik\grid\ExpandRowColumn',
                            'detailAnimationDuration'=>'fast',
                            'value' => function ($model, $key, $index, $column) {
                                return GridView::ROW_COLLAPSED;
                            },
                            'detail' => function ($model, $key, $index, $column) {
                                return Yii::$app->controller->renderPartial('expand_details_item', ['preStatisticModel' => $model]);
                            },
                            'headerOptions' => ['class' => 'kartik-sheet-style'],
                            'expandOneOnly' => true
                        ],
                        [

                            'header' => '预计发放奖品',
                            'attribute' => 'prizes',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $t = "";
                                if (!empty($data['prizes'])){
                                    foreach ($data['prizes'] as $v){
                                        $t = $t.ArrayUtils::getArrayValue($v['level_type'],CustomerInvitationActivityPrize::$levelTypeArr)."——{$v['name']}({$v['num_text']})"."<br/>";
                                    }
                                }
                                return $t;
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
