<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\StarExchangeLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Star Exchange Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="star-exchange-log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Star Exchange Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'created_at',
            'updated_at',
            'trade_no',
            'exchange_time',
            //'phone',
            //'amount',
            //'biz_type',
            //'biz_id',
            //'balance_id',
            //'balance_log_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
