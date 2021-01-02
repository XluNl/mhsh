<?php

use backend\models\BackendCommon;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var  integer $smallSortId*/
/* @var  array $smallSortArr*/
$params = urldecode($_SERVER["QUERY_STRING"]);
$params = BackendCommon::convertUrlQuery($params);

if (!empty($smallSortArr)): ?>
    <div class="col-sm-2">
        <div class="list-group success square">
            <?php echo Html::a("全部商品",ArrayHelper::merge(['goods/index'], ArrayHelper::merge($params, ['GoodsSearch[sort_2]' => ''])),['class'=>"list-group-item ".($smallSortId == 0?"active":"")])  ?>
            <?php foreach ($smallSortArr as $k=>$v): ?>
                <?php echo Html::a($v,ArrayHelper::merge(['goods/index'], ArrayHelper::merge($params, ['GoodsSearch[sort_2]' => $k])),['class'=>"list-group-item ".($smallSortId == $k?"active":"")])  ?>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif;?>

