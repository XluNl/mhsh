<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Customer;
use backend\models\BackendCommon;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '客户列表';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="customer-index">

    <h1 class="page-heading">客户列表</h1>
    <div class="alert alert-warning alert-bold-border fade in alert-dismissable">
        <p><strong></strong></p>
    </div>
    <div class="alert alert-warning alert-bold-border fade in alert-dismissable">
        <p>
            <?= Html::a('新增客户', ['update'], ['class' => 'btn btn-info']) ?>
        </p>
    </div>
    <div class="panel with-nav-tabs panel-primary">
        <div class="panel-heading">

        </div>
        <div class="panel-body">
            <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
        </div>
        <div class="panel-body">
            <div class="panel-body">
                <div class="row">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                'id',
                                'nickname',
                                'realname',
                                'phone',
                                [
                                    'header' => '地址',
                                    'value' => function ($data) {
                                        return $data['province_text'].$data['city_text'].$data['county_text'].$data['community'].$data['address'];
                                    },
                                ],
                                [
                                    'attribute' => 'status',
                                    'format' => 'raw',
                                    'value' => function ($model) {
                                        return Html::tag("label",Customer::$StatusArr[$model->status],['class'=>Customer::$StatusCssArr[$model->status]]);
                                    },
                                ],
                                'created_at',
                                ['class' => 'yii\grid\ActionColumn'],
                            ],
                        ]); ?>
                    <?php Pjax::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
