<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */

$this->title = '保存用户';
$this->params['breadcrumbs'][] = ['label' => '用户列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
