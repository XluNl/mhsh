<?php

use backend\models\ModelViewUtils;
use backend\services\GoodsDisplayDomainService;
use backend\utils\BackendViewUtil;
use common\models\Alliance;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuAlliance;
use common\utils\StringUtils;
use \yii\bootstrap\Html;
use yii\grid\GridView;
use \common\models\Common;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\GoodsSkuAllianceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '联盟商品审核列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?php  echo $this->render('filter', ['audit_status' => $searchModel->audit_status]); ?>
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'goods_owner_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['goods_owner_type'],GoodsConstantEnum::$ownerArr,GoodsConstantEnum::$ownerCssArr);
                            },
                        ],
                        [
                            'attribute' => 'display_channel',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['display_channel'],GoodsConstantEnum::$scheduleDisplayChannelArr,GoodsConstantEnum::$scheduleDisplayChannelCssArr);
                            },
                        ],
                        [
                            'header' => '联盟商户/团长自营',
                            'format' => 'raw',
                            'value' => function ($data) {
                                if ($data->goods_owner_type==GoodsConstantEnum::OWNER_HA && key_exists('alliance',$data->relatedRecords)){
                                    return Html::a("{$data['alliance']['nickname']}<br/>{$data['alliance']['realname']}({$data['alliance']['phone']})",['/alliance/index','AllianceSearch[id]'=>$data['goods_owner_id']]);
                                }
                                if ($data->goods_owner_type==GoodsConstantEnum::OWNER_DELIVERY && key_exists('delivery',$data->relatedRecords)){
                                    return Html::a("{$data['delivery']['nickname']}<br/>{$data['delivery']['realname']}({$data['delivery']['phone']})",['/delivery/index','DeliverySearch[id]'=>$data['goods_owner_id']]);
                                }
                                return "";
                            },
                        ],
                        'goods_name',
                        [
                            'attribute' => 'goods_type',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['goods_type'],GoodsConstantEnum::$typeArr,GoodsConstantEnum::$typeCssArr);
                            },
                        ],
                        [
                            'attribute' => 'goods_img',
                            'format' => [
                                'image',
                                [
                                    'onerror' => 'ifImgNotExists(this)',
                                    'class' => 'img-circle',
                                    'width'=>'100',
                                    'height'=>'100'
                                ]
                            ],
                            'value' => function ($data) {
                                return $data['goods_img_text'];
                            },
                        ],
                        'sku_unit',
                        'sku_describe',
                        'sku_stock',
                        [
                            'header' => '价格',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $text = "采购价:".Common::showAmountWithYuan($data['purchase_price']);
                                $text =$text.'<br/>'. "划线价:".Common::showAmountWithYuan($data['reference_price']);
                                if ($data['goods_owner_type']==GoodsConstantEnum::OWNER_DELIVERY){
                                    $text =$text.'<br/>'. "划线价:".Common::showAmountWithYuan($data['sale_price']);
                                }
                                return $text;
                            },
                        ],

                        [
                            'header' => '有效期',
                            'value' => function ($data) {
                                return $data['production_date'].'~'.$data['expired_date'];
                            },
                        ],
                        [
                            'attribute' => 'audit_status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['audit_status'],GoodsSkuAlliance::$auditStatusArr,GoodsSkuAlliance::$auditStatusCssArr);
                            },
                        ],
                        [
                            'header' => '备注',
                            'format' => 'raw',
                            'value' => function ($data) {
                                $text = "";
                                if ($data['goods_owner_type']==GoodsConstantEnum::OWNER_DELIVERY){
                                    $text = $text."一级分润比例:".Common::showPercentWithUnit($data['one_level_rate'])."<br/>";
                                    $text = $text."二级分润比例:".Common::showPercentWithUnit($data['two_level_rate'])."<br/>";
                                    $text = $text."一级分享团长比例:".Common::showPercentWithUnit($data['share_rate_1'])."<br/>";
                                    $text = $text."公司分润比例:".Common::showPercentWithUnit($data['company_rate']);
                                }
                                if (StringUtils::isNotBlank($data['expect_offline_time'])){
                                    $text = $text."预计截单日期:".$data['expect_offline_time']."<br/>";
                                }
                                if (StringUtils::isNotBlank($data['expect_arrive_time'])){
                                    $text = $text."预计送达日期:".$data['expect_arrive_time']."<br/>";
                                }
                                return $text;
                            },
                        ],
                        'audit_result',
                        'operator_name',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{accept}{deny}',
                            'headerOptions' => ['width' => '152'],
                            'buttons' =>[
                                'accept' => function ( $url, $model, $key) {
                                    if ($model['audit_status']!=GoodsSkuAlliance::AUDIT_STATUS_WAITING){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核通过',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-success btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' => '同意',
                                        'data-id' => $model['id'],
                                        'data-commander'=>GoodsSkuAlliance::AUDIT_STATUS_ACCEPT,
                                    ]);
                                },
                                'deny' => function ( $url, $model, $key) {
                                    if ($model['audit_status']!=GoodsSkuAlliance::AUDIT_STATUS_WAITING){
                                        return "";
                                    }
                                    return Html::button(Html::tag('i','审核拒绝',['class'=>'fa fa-plus']), [
                                        'class' => 'audit_note btn btn-danger btn-xs',
                                        'data-toggle' => 'modal',
                                        'data-audit_note' =>'',
                                        'data-id' => $model['id'],
                                        'data-commander'=>GoodsSkuAlliance::AUDIT_STATUS_DENY,
                                    ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php
$modelId = 'audit_note';
echo $this->render('../layouts/modal-view-h', [
    'modelType'=>'modal-view-rows',
    'modalId' => $modelId,
    'title'=>'添加审核备注',
    'requestUrl'=>Url::to(['/goods-sku-alliance/operation']),
    'columns'=>[
        [
            'key'=>'audit_note','title'=>'备注','type'=>'textarea',
            'content'=> Html::textarea('audit_note','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"audit_note"),
                ]))
        ],
        [
            'key'=>'id','title'=>'联盟商品审核id','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('id',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"id"),
                ]))
        ],
        [
            'key'=>'commander','title'=>'审核结果','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('commander',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"commander"),
                ]))
        ],
    ],
]); ?>