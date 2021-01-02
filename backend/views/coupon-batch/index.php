<?php

use backend\services\CouponBatchService;
use backend\services\CouponService;
use backend\utils\BackendViewUtil;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use \yii\bootstrap\Html;
use \common\models\Common;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CouponBatchSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/*
<?= Html::a('新增优惠券活动', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
<?= Html::a('新增优惠券活动', "javascript:void(0);", ['class' => 'btn btn-info btn-lg' ,'id'=>'addcoupon']) ?>
*/
$this->title = '优惠券活动列表';
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
                <?= Html::a('新增优惠券活动', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
                <?php  echo $this->render('filter', ['use_limit_type' => $searchModel->use_limit_type]); ?>
            </div>
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'owner_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['owner_type'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'attribute' => 'coupon_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['coupon_type'],CouponBatch::$couponType,CouponBatch::$couponTypeCssArr);
                            },
                        ],
                        [
                            'header' => '归属名称',
                            'attribute' => 'owner_name',
                        ],
                        [
                            'attribute' => 'type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['type'],Coupon::$typeArr,Coupon::$typeCssArr);
                            },
                        ],
                        [
                            'contentOptions' => ['style'=>'max-width:150px;'],
                            'format'=>'raw',
                            'attribute' => 'batch_no',
                            'value' => function ($data) {
                                return $data['batch_no'];
                            },
                        ],
                        'name',
                        'coupon_name',
                        [
                            'header' => '优惠明细',
                            'value' => function ($data) {
                                return CouponService::generateCouponDesc($data['type'],$data['startup'],$data['discount'],$data['use_limit_type'],$data['use_limit_type_params']);
                            },
                        ],
                        [
                            'attribute' => 'restore',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['restore'],Coupon::$restoreArr,Coupon::$restoreCssArr);
                            },
                        ],
                        [
                            'headerOptions' => ['width' => '155'],
                            'header' => '领取时间',
                            'attribute' => 'draw_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['draw_start_time'].'<br/>'.$data['draw_end_time'];
                            },
                        ],
                        [
                            'headerOptions' => ['width' => '155'],
                            'header' => '使用时间',
                            'attribute' => 'use_start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $data->decodeUserTimeFeature(true);
                                $tpl = $data->use_start_time .'--'.$data->use_end_time;
                                if($data->user_time_type==CouponBatch::USER_TIME_FEATURE_RANG){
                                    return $data['user_time_type_stat'].'<br/>'.$data['user_time_type_end'];
                                }
                                if($data->user_time_type > CouponBatch::USER_TIME_FEATURE_RANG){
                                    return CouponBatch::$userTimeType[$data->user_time_type].$data['user_time_days'].'日内';
                                }
                                return "";
                            },
                        ],
                        [
                            'header' => '领取规则',
                            'value' => function ($data) {
                                return CouponBatchService::generateDrawDesc($data['draw_limit_type'] ,$data['draw_limit_type_params']);
                            },
                        ],
                        'amount',
                        'draw_amount',
                        'used_count',
                        [
                            'header' => '占比',
                            'value' => function ($data) {
                                return Common::calcPercentWithUnit($data['used_count'],$data['amount']);
                            },
                        ],
                        [
                            'attribute' => 'is_public',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_public'],CouponBatch::$isPublicArr,CouponBatch::$isPublicCssArr);
                            },
                        ],
                        [
                            'attribute' => 'draw_customer_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['draw_customer_type'],CouponBatch::$drawCustomerTypeArr,CouponBatch::$drawCustomerTypeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CouponBatch::$statusArr,CouponBatch::$statusCssArr);
                            },
                        ],
                        'operator_name',
                        'updated_at',
                        [
                            'attribute' => 'is_pop',
                            'format' => 'raw',
                            'value' => function ($data){

                                return SwitchInput::widget([
                                    'model' => $data,
                                    'attribute' => 'is_pop',
                                    'pluginOptions' => [
                                        'size' => 'small',
                                        'onText'=>CouponBatch::$isPopArr[CouponBatch::IS_POP_TRUE],
                                        'offText'=>CouponBatch::$isPopArr[CouponBatch::IS_POP_FALSE]
                                    ],
                                    'options' => [
                                        'id'=>"is_pop_{$data['id']}",
                                    ],
                                    'pluginEvents' =>[
                                        "switchChange.bootstrapSwitch" => 'function($event) { 
                                            let commander = $(this).is(":checked")?1:0;
                                            $.get("/coupon-batch/pop-operation?commander="+commander+"&id='.$data['id'].'",function(data){  
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
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}<br/>{delete}{active}<br/>{disable}{detail}{discard}',
                            //'headerOptions' => ['width' => '152'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/coupon-batch/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'delete' => function ( $url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("删除",['/coupon-batch/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_DELETED],'btn btn-xs  btn-danger','fa fa-trash',"确认删除？");
                                },
                                'active' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/coupon-batch/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_ACTIVE],'btn btn-xs btn-warning','fa fa-cloud-upload',"确认启用？");
                                },
                                'disable' => function ($url, $model, $key) {
                                    if ($model->status==CouponBatch::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("停用",['/coupon-batch/operation','id'=>$model->id,'commander'=>CouponBatch::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-cloud-download',"确认停用？");
                                },
                                'detail' => function ($url, $model, $key) {

                                    return BackendViewUtil::generateOperationATag("记录",['/coupon/index','CouponSearch[batch]'=>$model['id']],'btn btn-xs btn-info','fa fa-file-text');
                                },
                                'discard' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("作废批次",['/coupon-batch/discard-all','id'=>$model->id],'btn btn-xs btn-danger','fa fa-file-text',"确认作废所有优惠券？");
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
 $("#addcoupon").on('click',function(d) {
     layer.open({
          type: 2,
          area: ['70%', '80%'],
          fixed: false, //不固定
          title:'添加',
          maxmin: true,
          content: ['<?php echo Url::toRoute('modify');?>']
        });
     });
</script>


