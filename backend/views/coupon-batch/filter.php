<?php

use common\models\Coupon;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $use_limit_type integer */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($use_limit_type)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['coupon-batch/index'], ArrayHelper::merge($params, ['CouponBatchSearch[use_limit_type]' => ''])))  ?>
        </li>
        <?php foreach (Coupon::$limitTypeArr as  $key => $value): ?>
            <li <?php if ($use_limit_type == $key&&!StringUtils::isBlank($use_limit_type)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['coupon-batch/index'], ArrayHelper::merge($params, ['CouponBatchSearch[use_limit_type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>