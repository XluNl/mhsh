<?php

use backend\utils\BackendViewUtil;
use common\models\Common;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\StringUtils;
use yii\grid\GridView;

/* @var $searchModel */
/* @var $dataProvider */
$this->title = '排期列表';
$this->context->layout = 'sub';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body th {
        text-align: center;
    }

    .field-groupactivesearch-status {
        display: flex;
        flex-direction: column;
    }
</style>
<div class="" style="margin-bottom: 15px;
    display: flex;
    justify-content: flex-end;
    padding-right: 15px;">
</div>
<div class="panel with-nav-tabs panel-primary" style="text-align: center;margin: 20px">

    <div class="container-fluid">
        <?php echo $this->render('_search_goods_schedule', ['model' => $searchModel]); ?>
        <div class="row">
            <div class="box box-success">
                <div class="box-header with-border">
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12"
                             style="display:flex;justify-content:flex-start;margin-top: 0px;">
                            <button class="btn btn-primary" id="icancel" type="reset" style="margin-right: 10px;">选择
                            </button>
                        </div>
                    </div>
                </div>
                <div class="box-body" style="text-align: center">
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'rowOptions' => function ($model) {
                            return ['id' => "tr-" . $model->id];
                        },
                        'columns' => [
                            [
                                'class' => 'yii\grid\RadioButtonColumn',
                                'radioOptions' => function ($model) {
                                    return [
                                        'data-id' => $model['id'],
                                        'data-stock' => $model['schedule_stock'],
                                        'data-goods_id' => $model['goods']['id'],
                                        'data-name' => $model['goods']['goods_name'],
                                        'data-img' => Common::generateAbsoluteUrl(StringUtils::filterFirstNotBlank($model['goodsSku']['sku_img'],$model['goods']['goods_img'])),
                                        'data-desc' => $model['goods']['goods_describe'],
                                        'data-owner_id' => $model['owner_id'],
                                        'data-schedule_name' => $model['schedule_name'],
                                        'data-price' => Common::showAmount($model['price']),
                                        'data-sku_name' => $model['goodsSku']['sku_name']
                                    ];
                                },
                                'headerOptions' => ['width' => '5%'],
                            ],
                            [
                                'attribute' => 'schedule_name',
                                'label' => '排期名称',
                            ],
                            [
                                'attribute' => 'goods_id',
                                'label' => '商品ID',
                            ],
                            [
                                'attribute' => 'sku_id',
                                'label' => '规格ID',
                            ],
                            [
                                'format' => [
                                    'image',
                                    [
                                        'onerror' => 'ifImgNotExists(this)',
                                        'class' => 'img-circle',
                                        'width'=>'40',
                                        'height'=>'40'
                                    ]
                                ],
                                'value' => function ($data) {
                                    if (!StringUtils::isBlank($data['goodsSku']['sku_img'])){
                                        return Common::generateAbsoluteUrl($data['goodsSku']['sku_img']);
                                    }
                                    if (!StringUtils::isBlank($data['goods']['goods_img'])){
                                        return Common::generateAbsoluteUrl($data['goods']['goods_img']);
                                    }
                                    return '';
                                },
                            ],
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
                            [
                                'attribute' => 'owner_type',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return BackendViewUtil::getArrayWithLabel($model['owner_type'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);

                                }
                            ],
                            [
                                'attribute' => 'schedule_display_channel',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return BackendViewUtil::getArrayWithLabel($model['schedule_display_channel'],GoodsConstantEnum::$scheduleDisplayChannelArr,GoodsConstantEnum::$scheduleDisplayChannelCssArr);
                                },
                            ],
                            [
                                'header' => '状态(商品/属性/排期)',
                                'attribute' => 'schedule_status',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    $label = "";
                                    if (key_exists('goods',$data->relatedRecords)){
                                        $label .= BackendViewUtil::getArrayWithLabel($data['goods']['goods_status'],GoodsConstantEnum::$statusArr,GoodsConstantEnum::$statusCssArr);
                                    }
                                    $label .="/";
                                    if (key_exists('goodsSku',$data->relatedRecords)){
                                        $label .= BackendViewUtil::getArrayWithLabel($data['goodsSku']['sku_status'],GoodsConstantEnum::$statusArr,GoodsConstantEnum::$statusCssArr);
                                    }
                                    $label .="/";
                                    $label .=BackendViewUtil::getArrayWithLabel($data['schedule_status'],GoodsConstantEnum::$statusArr,GoodsConstantEnum::$statusCssArr);
                                    return $label;
                                },
                            ],
                            [
                                'header' => '展示时间',
                                'attribute' => 'display_start',
                                'format' => 'raw',
                                'headerOptions' => ['width' => '155'],
                                'value' => function ($data) {
                                    return $data['display_start'].'<br/>'.$data['display_end'];
                                },
                            ],
                            [
                                'header' => '售卖时间',
                                'attribute' => 'online_time',
                                'format' => 'raw',
                                'headerOptions' => ['width' => '155'],
                                'value' => function ($data) {
                                    return $data['online_time'].'<br/>'.$data['offline_time'];
                                },
                            ],
                            [
                                'attribute' => 'price',
                                'value' => function ($data) {
                                    return Common::showAmountWithYuan($data['price']);
                                },
                            ],
                            [
                                'header' => '活动库存/已售数量',
                                'value' => function ($data) {
                                    return $data['schedule_stock'].'/'.$data['schedule_sold'];
                                },
                            ],
                            /*[
                                'attribute' => 'goods_describe',
                                'label' => '商品描述',
                                'value' => function ($data) {
                                    return $data['goods']['goods_describe'];
                                },
                            ],*/

                        ]
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let goods_info = {};
    $(":radio").click(function () {
        goods_info = $(this).data();
        console.log(goods_info);
    });
    $('#icancel').click(function () {
        if (!goods_info.id) {
            layer.msg('请选择一件商品', {icon: 5, time: 1000});
            return;
        }
        parent.say(goods_info);
        parent.layer.close(parent.layer.getFrameIndex(window.name));
    });
</script>