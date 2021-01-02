<?php

use common\models\BizTypeEnum;
use common\models\DistributeBalanceItem;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $type integer */
?>
<div class="panel-heading">
    <ul class="nav nav-tabs">
        <li <?php if (StringUtils::isBlank($type)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['distribute-balance-item/index'], ArrayHelper::merge($params, ['DistributeBalanceItemSearch[type]' => ''])))  ?>
        </li>
        <?php foreach (DistributeBalanceItem::$typeArr as  $key => $value): ?>
            <li <?php if ($type == $key&&!StringUtils::isBlank($type)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['distribute-balance-item/index'], ArrayHelper::merge($params, ['DistributeBalanceItemSearch[type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>