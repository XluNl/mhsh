<?php

use common\models\BizTypeEnum;
use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use backend\models\BackendCommon;
use common\utils\StringUtils;

$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
/* @var $bizType integer */
?>
<div class="panel-heading">
    <ul class="nav nav-tabs">
        <li <?php if (StringUtils::isBlank($bizType)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['distribute-balance/index'], ArrayHelper::merge($params, ['DistributeBalanceSearch[biz_type]' => ''])))  ?>
        </li>
        <?php foreach (BizTypeEnum::getBizTypeShowArr(BackendCommon::getFCompanyId()) as $key => $value): ?>
            <li <?php if ($bizType == $key&&!StringUtils::isBlank($bizType)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['distribute-balance/index'], ArrayHelper::merge($params, ['DistributeBalanceSearch[biz_type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>