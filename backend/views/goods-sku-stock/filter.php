<?php

use common\models\Delivery;
use common\models\GoodsSkuStock;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $type string */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($type)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['goods-sku-stock/index'], ArrayHelper::merge($params, ['GoodsSkuStockSearch[type]' => ''])))  ?>
        </li>
        <?php foreach (GoodsSkuStock::$typeArr as  $key => $value): ?>
            <li <?php if ($type == $key&&!StringUtils::isBlank($type)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['goods-sku-stock/index'], ArrayHelper::merge($params, ['GoodsSkuStockSearch[type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>