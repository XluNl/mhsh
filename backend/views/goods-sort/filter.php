<?php

use backend\models\BackendCommon;
use common\models\GoodsSort;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var  integer $sortOwner*/
$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
?>

<div class="panel-heading">
    <ul class="nav nav-tabs">
        <?php foreach (GoodsSort::getSortOwnerArr() as $k=>$v): ?>
            <li <?php if ($sortOwner == $k): ?>class="active"<?php endif;?>>
                <?php echo Html::a($v,ArrayHelper::merge(['goods-sort/index'], ArrayHelper::merge($params, ['GoodsSortSearch[sort_owner]' => $k])))  ?>
            </li>
        <?php endforeach;?>
    </ul>
</div>

