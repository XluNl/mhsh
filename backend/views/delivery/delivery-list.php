<?php



/* @var $deliveryOrderList array */

use common\models\Common;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Json;

$provider = new ArrayDataProvider([
    'allModels' => $deliveryOrderList
]);
echo GridView::widget([
    'dataProvider' => $provider,
    'layout'=>"{items}",
    'showHeader' => false,
    'tableOptions'=>['class' => 'table table-condensed table-bordered'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['id' => $model['delivery_id'], 'onclick' => 'centerDelivery('.$model['delivery_id'].');'];
    },
    'columns' => [
        [
            'value' => function ($data) {
                return "{$data['delivery_nickname']}:订单{$data['order_count']},金额{$data['real_amount']}";
            },
        ],
    ],
]);
?>

<div>
<script>
    <?php echo "var deliveryOrderList = " . Json::encode($deliveryOrderList) . ";"; ?>
</script>
</div>
