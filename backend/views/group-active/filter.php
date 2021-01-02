<?php

use backend\models\BackendCommon;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $owner_type string */
?>

<div class="panel-heading">
    <ul class="nav nav-tabs">
        <li <?php if (StringUtils::isBlank($owner_type)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['group-active/index'], ArrayHelper::merge($params, ['GroupActiveSearch[owner_type]' => ''])))  ?>
        </li>
        <?php foreach (GoodsConstantEnum::$ownerArr as  $key => $value): ?>
            <li <?php if ($owner_type == $key&&!StringUtils::isBlank($owner_type)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['group-active/index'], ArrayHelper::merge($params, ['GroupActiveSearch[owner_type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

