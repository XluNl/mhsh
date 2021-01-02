<?php

use common\models\Delivery;
use common\models\GroupRoom;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $status string */
?>
<div class="panel-heading">
    <ul class="nav nav-tabs">
        <li <?php if (StringUtils::isBlank($status)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['group-room/index'], ArrayHelper::merge($params, ['GroupRoomSearch[status]' => ''])))  ?>
        </li>
        <?php foreach (GroupRoom::$groupRoomStatus as  $key => $value): ?>
            <li <?php if ($status == $key&&!StringUtils::isBlank($status)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['group-room/index'], ArrayHelper::merge($params, ['GroupRoomSearch[status]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>