<?php

use common\models\Alliance;
use common\models\Delivery;
use common\models\GoodsSkuAlliance;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $audit_status string */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($audit_status)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['goods-sku-alliance/index'], ArrayHelper::merge($params, ['GoodsSkuAllianceSearch[audit_status]' => ''])))  ?>
        </li>
        <?php foreach (GoodsSkuAlliance::$auditStatusArr as  $key => $value): ?>
            <li <?php if ($audit_status == $key&&!StringUtils::isBlank($audit_status)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['goods-sku-alliance/index'], ArrayHelper::merge($params, ['GoodsSkuAllianceSearch[audit_status]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>