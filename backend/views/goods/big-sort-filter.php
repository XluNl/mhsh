<?php

use backend\models\BackendCommon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var  integer $bigSortId*/
/* @var  common\models\GoodsSort $bigSortArr*/
$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);
?>

<div class="panel-heading">
    <ul class="nav nav-tabs">
        <li <?php if ($bigSortId == 0): ?>class="active"<?php endif;?>>
            <?php echo Html::a("全部商品",ArrayHelper::merge(['goods/index'], ArrayHelper::merge($params, ['GoodsSearch[sort_1]' => '','GoodsSearch[sort_2]' => ''])))  ?>
        </li>
        <?php foreach ($bigSortArr as $k=>$v): ?>
            <li <?php if ($bigSortId == $k): ?>class="active"<?php endif;?>>
                <?php echo Html::a($v,ArrayHelper::merge(['goods/index'], ArrayHelper::merge($params, ['GoodsSearch[sort_1]' => $k,'GoodsSearch[sort_2]' => ''])))  ?>
            </li>
        <?php endforeach;?>
    </ul>
</div>

