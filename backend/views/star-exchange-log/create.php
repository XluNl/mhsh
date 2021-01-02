<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StarExchangeLog */

$this->title = 'Create Star Exchange Log';
$this->params['breadcrumbs'][] = ['label' => 'Star Exchange Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="star-exchange-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
