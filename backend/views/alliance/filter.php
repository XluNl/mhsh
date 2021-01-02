<?php

use common\models\Alliance;
use common\models\Delivery;
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
            <?php echo Html::a("所有",ArrayHelper::merge(['alliance/index'], ArrayHelper::merge($params, ['AllianceSearch[type]' => ''])))  ?>
        </li>
        <?php foreach (Alliance::$typeArr as  $key => $value): ?>
            <li <?php if ($type == $key&&!StringUtils::isBlank($type)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['alliance/index'], ArrayHelper::merge($params, ['AllianceSearch[type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>