<?php

use backend\models\ModelViewUtils;
use common\models\Common;
use common\models\CustomerInvitationActivityPrize;
use common\utils\ArrayUtils;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerInvitationActivitySearch */
/* @var $dataProvider yii\data\ArrayDataProvider
 * @var $successPrizesDataProvider yii\data\ArrayDataProvider
 * @var $failedPrizesDataProvider yii\data\ArrayDataProvider
 * @var $activityModel
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
<div class="container-fluid">

    <div class="alert alert-info">
        <p>
            <?php echo Html::button(Html::tag('i','确认结算活动',['class'=>'fa fa-plus']), [
                'class' => 'remark btn btn-primary',
                'data-toggle' => 'modal',
                'data-remark' => '',
                'data-activity_id' => $activityModel['id'],
            ])?>
        </p>
    </div>
    <div class="row" style="text-align: center">
        <div class="box box-success">
            <div class="box-header with-border">
                奖品发放成功汇总
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $successPrizesDataProvider,
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
            <div class="box-header with-border">
                奖品发放失败汇总
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $failedPrizesDataProvider,
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

    <div class="row" style="text-align: center">
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

                            'header' => '邀请人数',
                            'attribute' => 'invitation_count',
                            'value' => function ($data) {
                                return $data['invitation_count'];
                            },
                        ],
                        [

                            'header' => '已下单人数',
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
                                if (!empty($data['prizes'])){
                                    foreach ($data['prizes'] as $v){
                                        if ($v['is_draw']){
                                            $t = $t.ArrayUtils::getArrayValue($v['level_type'],CustomerInvitationActivityPrize::$levelTypeArr)."——{$v['name']}({$v['num_text']})"."--已发放<br/>";
                                        }
                                        else{
                                            $t = $t.ArrayUtils::getArrayValue($v['level_type'],CustomerInvitationActivityPrize::$levelTypeArr)."——{$v['name']}({$v['num_text']})"."--未发放({$v['draw_error']})<br/>";
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
<?php
$modelId = 'remark';
echo $this->render('../layouts/modal-view-h', [
    'modelType'=>'modal-view-rows',
    'modalId' => $modelId,
    'title'=>'添加确认结算',
    'requestUrl'=>Url::to(['/customer-invitation-activity/real-settle']),
    'columns'=>[
        [
            'key'=>'remark','title'=>'备注','type'=>'textarea',
            'content'=>Html::textarea('remark','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"remark"),
                ]))
        ],
        [
            'key'=>'activity_id','title'=>'活动编号','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('activity_id',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"activity_id"),
                ]))
        ],
    ],
]); ?>