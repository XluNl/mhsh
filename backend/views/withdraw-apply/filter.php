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
<style>
    .panel-heading .nav a{
        padding: 5px 10px;
    }
</style>
<div class="panel-heading">
    <ul class="nav nav-pills nav-danger">
        <li <?php if (StringUtils::isBlank($bizType)): ?>class="active" <?php endif; ?>>
            <?php echo Html::a("所有",ArrayHelper::merge(['withdraw-apply/index'], ArrayHelper::merge($params, ['WithdrawApplySearch[biz_type]' => ''])))  ?>
        </li>
        <?php foreach (BizTypeEnum::getBizTypeShowArr(BackendCommon::getFCompanyId()) as $key => $value): ?>
            <li <?php if ($bizType == $key&&!StringUtils::isBlank($bizType)): ?>class="active" <?php endif; ?> >
                <?php echo Html::a($value,ArrayHelper::merge(['withdraw-apply/index'], ArrayHelper::merge($params, ['WithdrawApplySearch[biz_type]' => $key])))  ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>