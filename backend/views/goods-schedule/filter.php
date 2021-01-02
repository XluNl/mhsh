<?php

use common\models\GoodsConstantEnum;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $schedule_display_channel string */
?>
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($schedule_display_channel)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['goods-schedule/index'], ArrayHelper::merge($params, ['GoodsScheduleSearch[schedule_display_channel]' => ''])))  ?>
        </li>
        <?php foreach (GoodsConstantEnum::$scheduleDisplayChannelArr as  $key => $value): ?>
            <li <?php if ($schedule_display_channel == $key&&!StringUtils::isBlank($schedule_display_channel)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['goods-schedule/index'], ArrayHelper::merge($params, ['GoodsScheduleSearch[schedule_display_channel]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>