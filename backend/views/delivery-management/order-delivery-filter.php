<?php

use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use \common\models\Order;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $order_status string */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($order_status)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['delivery-management/order-delivery-index'], ArrayHelper::merge($params, ['OrderDeliverySearchForms[order_status]' => ''])))  ?>
        </li>
        <?php foreach (Order::$order_status_list as $key => $value): ?>
            <li <?php if ($order_status == $key&&!StringUtils::isBlank($order_status)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['delivery-management/order-delivery-index'], ArrayHelper::merge($params, ['OrderDeliverySearchForms[order_status]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>