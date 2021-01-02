<?php

use common\models\Coupon;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $status integer */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($status)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['coupon/index'], ArrayHelper::merge($params, ['CouponSearch[status]' => ''])))  ?>
        </li>
        <?php foreach (Coupon::$statusArr as  $key => $value): ?>
            <li <?php if ($status == $key&&!StringUtils::isBlank($status)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['coupon/index'], ArrayHelper::merge($params, ['CouponSearch[status]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>