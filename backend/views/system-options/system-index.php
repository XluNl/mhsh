<?php

use backend\models\ModelViewUtils;
use backend\services\CouponBatchService;
use backend\services\CouponService;
use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use common\models\Coupon;
use common\models\CouponBatch;
use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use \yii\bootstrap\Html;
use \common\models\Common;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '配置项列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                系统配置项
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        'option_name',
                        'option_field',
                        'option_value',
                        'updated_at',
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{update}',
                            'headerOptions' => ['width' => '60'],
                            'buttons' =>[
                                'update' => function ($url, $model, $key) {
                                     return Html::button(Html::tag('i','修改',['class'=>'fa fa-edit']), [
                                        'class' => 'system_option btn btn-xs btn-primary',
                                        'data-toggle' => 'modal',
                                        'data-option_name' => $model['option_name'],
                                         'data-option_field' => $model['option_field'],
                                         'data-option_value' => $model['option_value'],
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
$modelId = 'system_option';
echo $this->render('../layouts/modal-view-h', [
    'modelType'=>'modal-view-tables',
    'modalId' => $modelId,
    'title'=>'添加管理员备注',
    'requestUrl'=>Url::to(['/system-options/system-modify']),
    'columns'=>[
        [
            'key'=>'option_name','title'=>'选项配置名称','type'=>'label',
            'content'=> Html::tag('span','',
                [
                    'id'=>ModelViewUtils::getAttrId($modelId,"option_name"),
                ])
        ],
        [
            'key'=>'option_value','title'=>'选项配置内容','type'=>'textarea',
            'content'=> Html::textarea('option_value','',
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"option_value"),
                ]))
        ],
        [
            'key'=>'option_field','title'=>'选项名','type'=>'hiddenInput',
            'content'=>Html::hiddenInput('option_field',null,
                ModelViewUtils::mergeDefaultOptions([
                    'id'=>ModelViewUtils::getAttrId($modelId,"option_field"),
                ]))
        ],
    ],
]); ?>
