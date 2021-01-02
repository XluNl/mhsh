<?php

use backend\services\BizTypeService;
use backend\utils\BackendViewUtil;
use common\models\BizTypeEnum;
use common\models\BonusBatchDrawLog;
use kartik\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\BonusBatchDrawLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '奖励金活动发放记录';
$this->params['breadcrumbs'][] = ['label' => '奖励金列表', 'url' => ['/bonus-batch/index']];
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
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'draw_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['draw_type'],BonusBatchDrawLog::$drawTypeArr,BonusBatchDrawLog::$drawTypeCssArr);
                            },
                        ],
                        [
                            'header' => '账户类型',
                            'attribute' => 'biz_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['biz_type'],BizTypeEnum::$bizTypeArr,BizTypeEnum::$bizTypeCssArr);
                            },
                        ],
                        [
                            'header' => '账户名称',
                            'attribute' => 'biz_name',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BizTypeService::createJumpUrl($data['biz_type'],$data['biz_id'],$data['biz_name']);
                            },
                        ],
                        'created_at',
                        [
                            'attribute' => 'num',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return Common::showAmountWithYuan($data['num']);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        'remark',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
