<?php

use backend\utils\BackendViewUtil;
use common\models\GoodsSkuStock;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsSkuStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '出入库记录';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

    <div style="margin-left: -15px;margin-right: -15px;">
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php  echo Html::Button('导出现有库存',['onclick'=>'exportStockXls();','class' => 'btn btn-info btn-lg']);?>
                <?php  echo $this->render('filter', ['type' => $searchModel->type]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],GoodsSkuStock::$typeArr,GoodsSkuStock::$typeCssArr);
                            },
                        ],
                        'num',

                        [
                            'header' => '商品-属性',
                            'value' => function ($data) {
                                $name = "";
                                if (key_exists('goods',$data->relatedRecords)){
                                    $name = $data['goods']['goods_name'];
                                }
                                if (key_exists('goodsSku',$data->relatedRecords)){
                                    $name = $name.'-'.$data['goodsSku']['sku_name'];
                                }
                                return $name;
                            },
                        ],
                        'remark',
                        'operator_name',
                        'created_at',
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php $this->beginBlock('js_end_index') ?>
function exportStockXls() {
    let url = '/goods-sku-stock/export-stock';
    window.open(url);
}
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end_index'], \yii\web\View::POS_END); ?>
