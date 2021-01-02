<?php



/* @var $routes */

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Json;

$provider = new ArrayDataProvider([
    'allModels' => $routes
]);
echo GridView::widget([
    'dataProvider' => $provider,
    'layout'=>"{items}",
    'showHeader' => false,
    'tableOptions'=>['class' => 'table table-condensed table-bordered'],
    'rowOptions' => function ($model, $key, $index, $grid) {
        return ['id' => $model['id'], 'onclick' => 'showDelivery(this);'];
    },
    'columns' => [
        [
            'value' => function ($data) {
                return "{$data['nickname']}-{$data['phone']}({$data['delivery_count']})";
            },
        ],
    ],
]);
?>

<div>
<script>
    <?php echo "var routes = " . Json::encode($routes) . ";"; ?>
</script>
</div>
