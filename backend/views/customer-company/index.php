<?php

use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\UserInfo;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;

/* @var $this yii\web\View
 * @var $searchModel
 */
$this->title = '客户';
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
                        'nickname',
                        'realname',
//                        'phone',
                        [
                            'attribute' => 'phone',
                            'value' => function ($data) {
                                if (!$data['phone']){
                                    return '';
                                }
                                return $data['phone'];
                            },
                        ],
//                        'em_phone',
                        [
                            'attribute' => 'em_phone',
                            'value' => function ($data) {
                                if (!$data['em_phone']){
                                    return '';
                                }
                                return $data['em_phone'];
                            },
                        ],
                        'occupation',
                        [
                            'header' => '地址',
                            'value' => function ($data) {
                                return $data['province_text'].$data['city_text'].$data['county_text'].$data['community'].$data['address'];
                            },
                        ],
                        [
                            'attribute' => 'is_customer',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_customer'],UserInfo::$roleRegisterArr,UserInfo::$roleRegisterCssArr);
                            },
                        ],
                        [
                            'attribute' => 'is_popularizer',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_popularizer'],UserInfo::$roleRegisterArr,UserInfo::$roleRegisterCssArr);
                            },
                        ],
                        [
                            'attribute' => 'is_delivery',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_delivery'],UserInfo::$roleRegisterArr,UserInfo::$roleRegisterCssArr);
                            },
                        ],
                        [
                            'attribute' => 'is_alliance',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_alliance'],UserInfo::$roleRegisterArr,UserInfo::$roleRegisterCssArr);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        'created_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
//                            'template' => '{update}|{enable}{disable}{invite}{coupon}{draw}',
                            'template' => '{log}{invite}{coupon}{draw}',
                            'headerOptions' => ['width' => '335'],
                            'buttons' =>[
//                                'update' => function ($url, $model, $key) {
//                                    return BackendViewUtil::generateOperationATag("修改",['/user-info/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
//                                },
//                                'enable' => function ($url, $model, $key) {
//                                    if ($model->status==CommonStatus::STATUS_ACTIVE){
//                                        return "";
//                                    }
//                                    return BackendViewUtil::generateOperationATag("启用",['/user-info/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-cloud-upload',"确认启用？");
//                                },
//                                'disable' => function ($url, $model, $key) {
//                                    if ($model->status==CommonStatus::STATUS_DISABLED){
//                                        return "";
//                                    }
//                                    return BackendViewUtil::generateOperationATag("禁用",['/user-info/operation','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-warning','fa fa-cloud-download',"确认禁用？");
//                                },
                                'log' => function ($url, $model, $key) {
                                    if ($model->is_customer!=CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("用户记录",['/customer-company/log','user_id'=>$model['user_id']],'btn btn-xs btn-success','fa fa-money');
                                },
                                'invite' => function ($url, $model, $key) {
                                    if ($model->is_customer!=CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("邀请",['/customer-invitation/index','user_info_id'=>$model['id']],'btn btn-xs btn-success','fa fa-money');
                                },
                                'coupon' => function ($url, $model, $key) {
                                    if (StringUtils::isBlank($model->customer_id)){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("优惠券",['/coupon/index','CouponSearch[customer_id]'=>$model['customer_id']],'btn btn-xs btn-info','fa fa-bookmark-o');
                                },
                                'draw' => function ($url, $model, $key) {
                                    if (StringUtils::isBlank($model->customer_id)){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("发放优惠券",['/coupon-batch/draw-coupon','customer_id'=>$model['customer_id']],'btn btn-xs btn-warning','fa fa-bookmark-o');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
