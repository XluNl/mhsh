<?php

use common\utils\StringUtils;
use kartik\grid\GridView;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerInvitationActivityResultSearch */
/* @var $dataProvider yii\data\ArrayDataProvider
 */
$this->title = '邀请奖励发放结果列表';
$this->params['breadcrumbs'][] = ['label' => '邀请奖励活动列表', 'url' => ['/customer-invitation-activity/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                发放明细
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

                            'header' => '发放奖品结果',
                            'attribute' => 'prizes',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $t = "";
                                $data['prizes'] = StringUtils::isBlank($data['prizes'])?[]:Json::decode($data['prizes']);
                                if (!empty($data['prizes'])){
                                    foreach ($data['prizes'] as $v){
                                        if ($v['is_draw']){
                                            $t = "{$t}{$v['name']}({$v['num_text']})"."--已发放<br/>";
                                        }
                                        else{
                                            $t = "{$t}{$v['name']}({$v['num_text']})"."--未发放({$v['draw_error']})<br/>";
                                        }
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
