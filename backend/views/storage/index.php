<?php

use backend\utils\BackendViewUtil;
use common\models\GoodsConstantEnum;
use kartik\widgets\Select2;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;
use \common\models\Order;
use \common\models\Payment;
use \backend\models\BackendCommon;

/* @var $this yii\web\View */
$this->title = '分拣单';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search'); ?>

</div>
