<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\GoodsSku;
use common\utils\ArrayUtils;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use backend\models\BackendCommon;
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/02/04/004
 * Time: 1:19
 */



if (!empty($model['goodsSku'])){
    $skuModels = $model['goodsSku'];
    $skuModels = GoodsDisplayDomainService::batchRenameImageUrlOrSetDefault($skuModels,'sku_img');
    $provider = new ArrayDataProvider([
        'allModels' => $skuModels
    ]);
    echo "<tr><td colspan='12'>";
    echo GridView::widget([
        'dataProvider' => $provider,
        'layout'=>"{items}",
        //'showHeader' => false,
        'tableOptions'=>['class' => 'table table-condensed table-bordered'],

        'columns' => [
            [
                'header' => '',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::tag("i","",['class'=>'fa fa-angle-double-right fa-lg']);
                },
            ],
            [
                'header' => '属性名称',
                'attribute' => 'sku_name',
            ],
            [
                'attribute' => 'sku_img',
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-circle',
                        'width'=>'40',
                        'height'=>'40'
                    ]
                ],
                'value' => function ($skuModel) {
                    return $skuModel['sku_img'];
                },
            ],
            'attribute' => 'sku_unit',
            [
                'header' => '是否标准件',
                'attribute' => 'sku_standard',
                'value' => function ($skuModel) {
                    return ArrayUtils::getArrayValue($skuModel['sku_standard'],GoodsSku::$skuStandardArr);
                },
            ],
            'sku_unit_factor',
            [
                'attribute' => 'sale_price',
                'value' => function ($skuModel) {
                    return Common::showAmountWithYuan($skuModel['sale_price']);
                },
            ],
            [
                'attribute' => 'reference_price',
                'value' => function ($skuModel) {
                    return Common::showAmountWithYuan($skuModel['reference_price']);
                },
            ],
            [
                'attribute' => 'purchase_price',
                'value' => function ($skuModel) {
                    return Common::showAmountWithYuan($skuModel['purchase_price']);
                },
            ],
            'sku_stock',
            'sku_sold',
            [
                'attribute' => 'sku_status',
                'format' => 'raw',
                'value' => function ($skuModel) {
                    return BackendViewUtil::getArrayWithLabel($skuModel->sku_status,GoodsConstantEnum::$statusArr,GoodsConstantEnum::$statusCssArr);
                },
            ],
            [
                'header' => '仓库绑定',
                'value' => function ($skuModel) {
                    if (!empty($skuModel['storageSkuMapping'])){
                        $storageSkuBindStorageSkuNum =  $skuModel['storageSkuMapping']['storage_sku_num'];
                        $storageSkuBindStorageSkuId = $skuModel['storageSkuMapping']['storage_sku_id'];
                        $storageSkuBindStorageSkuName = $skuModel['storageSkuMapping']['storage_sku_name'];
                        return $storageSkuBindStorageSkuName."(1:".$storageSkuBindStorageSkuNum.")";
                    }
                    return "未绑定";
                },
            ],
            'display_order',
            [
                'header' => '属性操作',
                'class' => 'yii\grid\ActionColumn',
                'template' => '{update}{delete}{skuUp}{skuDown}{storageSkuBind}',
                'headerOptions' => ['width' => '180'],
                'buttons' =>[
                    'update' => function ($url, $skuModel, $key) use($model) {
                        return BackendViewUtil::generateOperationATag("修改",['/goods-sku/modify','goods_id'=>$model['id'],'id'=>$skuModel['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                    },
                    'delete' => function ($url, $skuModel, $key) use ($model) {
                        return BackendViewUtil::generateOperationATag("删除",['/goods-sku/operation','goods_id'=>$model['id'],'id'=>$skuModel['id'],'commander'=>GoodsConstantEnum::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash','确认删除？');
                    },
                    'skuUp' => function ($url, $skuModel, $key)  use ($model){
                        if ($skuModel['sku_status']==GoodsConstantEnum::STATUS_UP){
                            return "";
                        }
                        return BackendViewUtil::generateOperationATag("上架",['/goods-sku/operation','goods_id'=>$model['id'],'id'=>$skuModel['id'],'commander'=>GoodsConstantEnum::STATUS_UP],'btn btn-xs btn-default','fa fa-cloud-upload','确认上架？');
                    },
                    'skuDown' => function ($url, $skuModel, $key) use ($model) {
                        if ($skuModel['sku_status']==GoodsConstantEnum::STATUS_DOWN){
                            return "";
                        }
                        return BackendViewUtil::generateOperationATag("下架",['/goods-sku/operation','goods_id'=>$model['id'],'id'=>$skuModel['id'],'commander'=>GoodsConstantEnum::STATUS_DOWN],'btn btn-xs btn-warning','fa fa-cloud-download','确认下架？');
                    },
                    'storageSkuBind' => function ($url, $skuModel, $key) use ($model) {
                        $storageSkuBindStorageSkuNum = null;
                        $storageSkuBindStorageSkuId = null;
                        $storageSkuBindStorageSkuName = null;
                        if (!empty($skuModel['storageSkuMapping'])){
                            $storageSkuBindStorageSkuNum =  $skuModel['storageSkuMapping']['storage_sku_num'];
                            $storageSkuBindStorageSkuId = $skuModel['storageSkuMapping']['storage_sku_id'];
                            $storageSkuBindStorageSkuName = $skuModel['storageSkuMapping']['storage_sku_name'];
                        }
                        return Html::button(Html::tag('i','绑仓库商品',['class'=>'fa fa-plus']), [
                            'class' => 'storageSkuBind btn btn-primary btn-xs',
                            'data-toggle' => 'modal',
                            'data-storageSkuBindSkuId' => $skuModel['id'],
                            'data-storageSkuBindGoodsId' => $model['id'],
                            'data-storageSkuBindStorageSkuNum' => $storageSkuBindStorageSkuNum,
                            'data-storageSkuBindStorageSkuName' => $storageSkuBindStorageSkuName,
                            'data-storageSkuBindStorageSkuId' => $storageSkuBindStorageSkuId,
                        ]);
                    },
                ],
            ]
        ],
    ]);
    echo "</td></tr>";
}
else{
    echo "<tr><td colspan='12'>";
    echo "</td></tr>";
}
?>