<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\utils\StringUtils;
use kartik\widgets\SwitchInput;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsScheduleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $deliveryNames array */
/*<?= Html::a('批量上架', ['/goods-schedule-collection/schedule-operation','id'=>$searchModel->collection_id,'commander'=>GoodsConstantEnum::STATUS_UP], ['class' => 'btn btn-success btn-sm']) ?>*/
/*<?= Html::a('批量下架', ['/goods-schedule-collection/schedule-operation','id'=>$searchModel->collection_id,'commander'=>GoodsConstantEnum::STATUS_DOWN], ['class' => 'btn btn-danger btn-sm']) ?>
*/
$this->title = '商品排期列表';
$this->params['breadcrumbs'][] = ['label' => '排期列表', 'url' => ['/goods-schedule-collection/index']];
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
                <?= Html::a('新增商品排期', ['modify','collection_id'=>$searchModel->collection_id], ['class' => 'btn btn-info btn-sm']) ?>
                <?= Html::a('导入排期', ['/goods-schedule-collection/import','collection_id'=>$searchModel->collection_id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('下载排期样例文件', ['/file/排期样例.xlsx'], ['class' => 'btn btn-warning btn-sm']) ?>
                <?= Html::a('批量上架',"javascript:void(0);",['class' => 'btn btn-success btn-sm','id'=>'batch-operation-up']) ?>
                <?= Html::a('批量下架',"javascript:void(0);",['class' => 'btn btn-danger btn-sm','id'=>'batch-operation-down']) ?>
                <?= Html::a('文本导出', ['/goods-schedule-collection/schedule-text','id'=>$searchModel->collection_id], ['class' => 'btn btn-info btn-sm']) ?>
                <?php  echo $this->render('filter', ['schedule_display_channel' => $searchModel->schedule_display_channel]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                          'class' => 'yii\grid\CheckboxColumn',
                          'headerOptions' => ['width' => '5%'],
                          // 'checkboxOptions' => function ($model, $key, $index, $column) {
                          //       return ['value'=>$model['id']];
                          //   }
                        ],
                        'id',
                        [
                            'attribute' => 'schedule_display_channel',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['schedule_display_channel'],GoodsConstantEnum::$scheduleDisplayChannelArr,GoodsConstantEnum::$scheduleDisplayChannelCssArr);
                            },
                        ],
                        'schedule_name',
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
                                if (StringUtils::isNotBlank($data['goodsSku']['sku_img'])){
                                    return Common::generateAbsoluteUrl($data['goodsSku']['sku_img']);
                                }
                                if (StringUtils::isNotBlank($data['goods']['goods_img'])){
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
                        [
                            'header' => '限购数量',
                            'attribute' => 'schedule_limit_quantity',
                            'value' => function ($data) {
                                return $data['schedule_limit_quantity'];
                            },
                        ],
                        'coupon_batch_count',
                        [
                            'header' => '有效期/保质期',
                            'attribute' => 'validity_start',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['validity_start'].'<br/>'.$data['validity_end'];
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
                            'header' => '仓库商品绑定',
                            'attribute' => 'storage_sku_id',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data->hasStorageMapping()){
                                    return Html::tag("label","仓库商品:{$data['storage_sku_id']}(1:{$data['storage_sku_num']})",['class'=>'label label-success']);
                                }
                                else {
                                    return Html::tag("label",'未绑定仓库商品',['class'=>'label label-warning']);
                                }
                            },
                        ],
                        [
                            'attribute' => 'recommend',
                            'format' => 'raw',
                            'value' => function ($data){
                                return SwitchInput::widget([
                                    'model' => $data,
                                    'attribute' => 'recommend',
                                    'pluginOptions' => [
                                        'size' => 'small',
                                        'onText'=>GoodsSchedule::$isRecommendArr[GoodsSchedule::IS_RECOMMEND_TRUE],
                                        'offText'=>GoodsSchedule::$isRecommendArr[GoodsSchedule::IS_RECOMMEND_FALSE]
                                    ],
                                    'options' => [
                                        'id'=>"recommend_{$data['id']}",
                                    ],
                                    'pluginEvents' =>[
                                        "switchChange.bootstrapSwitch" => 'function($event) { 
                                            let commander = $(this).is(":checked")?1:0;
                                            $.get("/goods-schedule/recommend-operation?commander="+commander+"&id='.$data['id'].'",function(data){  
                                               if(data==undefined){
                                                   bootbox.alert("网络错误");                 
                                               }   
                                               else{
                                                   data = JSON.parse(data);
                                                   if(data.status!=true){
                                                        bootbox.alert(data.error);
                                                   }
                                               }           
                                            });           
                                        }',
                                    ]
                                ]);
                            },
                        ],
                        'operation_name',
                        'display_order',
                        'updated_at',
                        [
                            'header' => '商品操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}{delete}{up}{down}{copy}{output}',
                            'headerOptions' => ['width' => '180'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/goods-schedule/modify','schedule_id'=>$model['id'],'collection_id'=>$model['collection_id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/goods-schedule/operation','schedule_id'=>$model['id'],'commander'=>GoodsConstantEnum::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                },
                                'up' => function ($url, $model, $key) {
                                    if ($model->schedule_status==GoodsConstantEnum::STATUS_UP){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("上架",['/goods-schedule/operation','schedule_id'=>$model['id'],'commander'=>GoodsConstantEnum::STATUS_UP],'btn btn-xs btn-danger','fa fa-cloud-upload',"确认上架？");
                                },
                                'down' => function ($url, $model, $key) {
                                    if ($model->schedule_status==GoodsConstantEnum::STATUS_DOWN){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("下架",['/goods-schedule/operation','schedule_id'=>$model['id'],'commander'=>GoodsConstantEnum::STATUS_DOWN],'btn btn-xs btn-warning','fa fa-cloud-download',"确认下架？");
                                },
                                'copy' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("复制",['/goods-schedule/modify','src_schedule_id'=>$model['id'],'collection_id'=>$model['collection_id']],'btn btn-xs btn-info','fa fa-copy ');
                                },
                                'output' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("采购单",['/download/purchase-list','schedule_id'=>$model['id']],'btn btn-xs btn-primary','fa fa-download ');
                                },
                                'delivery' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("发货",['/goods-schedule/delivery-out','schedule_id'=>$model['id']],'btn btn-xs btn-success','fa fa-send ');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#batch-operation-up").on('click',function(data){
        var ids = new Array();
        $("input:checkbox[name='selection[]']:checked").each(function(i){
            ids.push($(this).val());
        });

        $.ajax({
               type: "GET",
               url: "<?php echo Url::toRoute('/goods-schedule-collection/schedule-operation');?>",
               data:{
                'id':"<?php echo $searchModel->collection_id ;?>",
                'commander':"<?php echo GoodsConstantEnum::STATUS_UP ;?>",
                'ids':ids,
                '_csrf':'<?php echo Yii::$app->request->csrfToken ;?>'
               },
               success: function(re){
               },error:function(re){
               },complete:function(re){
                 console.log(re);
               }
        });
    });

    $("#batch-operation-down").on('click',function(data){
        var ids = new Array();
        $("input:checkbox[name='selection[]']:checked").each(function(i){
            ids.push($(this).val());
        });
        $.ajax({
               type: "GET",
               url: "<?php echo Url::toRoute('/goods-schedule-collection/schedule-operation');?>",
               data:{
                'id':"<?php echo $searchModel->collection_id ;?>",
                'commander':"<?php echo GoodsConstantEnum::STATUS_DOWN ;?>",
                'ids':ids,
                '_csrf':'<?php echo Yii::$app->request->csrfToken ;?>'
               },
               success: function(re){
               },error:function(re){
               },complete:function(re){
                 console.log(re);
               }
        });
    });
</script>