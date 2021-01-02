<?php

use backend\utils\BackendViewUtil;
use common\models\DeliveryComment;
use kartik\popover\PopoverX;
use kartik\widgets\SwitchInput;
use kartik\grid\GridView;

use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\DeliveryCommentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '团长说列表';
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

            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'header' => '配送点',
                            'value' => function ($data) {
                                $name = "";
                                if (key_exists('delivery',$data->relatedRecords)){
                                    $name = $data['delivery']['nickname']."({$data['delivery']['realname']}-{$data['delivery']['phone']})";
                                }
                                return $name;
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
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],DeliveryComment::$statusArr,DeliveryComment::$statusArrCss);
                            },
                        ],
                        [
                            'attribute' => 'is_show',
                            'format' => 'raw',
                            'value' => function ($data){

                                return SwitchInput::widget([
                                    'model' => $data,
                                    'attribute' => 'is_show',
                                    'pluginOptions' => [
                                        'size' => 'small',
                                        'onText'=>DeliveryComment::$isShowArr[DeliveryComment::IS_SHOW_TRUE],
                                        'offText'=>DeliveryComment::$isShowArr[DeliveryComment::IS_SHOW_FALSE]
                                    ],
                                    'options' => [
                                        'id'=>"is_show_{$data['id']}",
                                    ],
                                    'pluginEvents' =>[
                                        "switchChange.bootstrapSwitch" => 'function($event) { 
                                            let commander = $(this).is(":checked")?1:0;
                                            debugger;
                                            $.get("/delivery-comment/show-operation?commander="+commander+"&id='.$data['id'].'",function(data){  
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
                        [
                            'header' => '评论内容',
                            'format' => 'raw',
                            'value' => function ($data){
                                return  PopoverX::widget([
                                    'header' => '评论内容',
                                    'placement' => PopoverX::ALIGN_LEFT,
                                    'size' => PopoverX::SIZE_LARGE,
                                    'content' => $this->render('pop-view', ['images' =>$data['images'],'content' =>$data['content']]),
                                    'toggleButton' => ['label'=>'查看', 'class'=>'btn btn-default'],
                                ]);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        [
                            'header' => '操作',
                            'headerOptions' => ['class' => 'kartik-sheet-style'],
                            'class' => 'kartik\grid\ActionColumn',
                            'dropdown' => true,
                            'template' => '{delete}<br/>{accept}<br/>{deny}',
                            'dropdownButton'=>['label'=>'操作'],
                            'buttons' =>[
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/delivery-comment/operation','id'=>$model['id'],'commander'=>DeliveryComment::STATUS_DELETED],'btn btn-danger','fa fa-trash',"确认删除？");
                                },
                                'accept' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("通过",['/delivery-comment/operation','id'=>$model['id'],'commander'=>DeliveryComment::STATUS_ACCEPT],'btn btn-success','fa fa-cloud-upload',"确认通过？");
                                },
                                'deny' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("拒绝",['/delivery-comment/operation','id'=>$model['id'],'commander'=>DeliveryComment::STATUS_DENY],'btn btn-warning','fa fa-cloud-download',"确认拒绝？");
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
