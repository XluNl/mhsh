<?php

use backend\utils\BootstrapFileInputConfigUtil;
use yii\helpers\Url;
use yiichina\icheck\ICheck;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var  array $bigSortArr */
/* @var array $smallSortArr */
/* @var common\models\Goods $model */
$this->title = '保存商品视频信息';
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->goods_name, 'url' => ['modify', 'goods_id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="page-heading">商品视频信息修改</h3>
                </div>
                <div class="box-body">
                    <?php $form = ActiveForm::begin();
                    echo FormGrid::widget([
                        'model'=>$model,
                        'form'=>$form,
                        'autoGenerateColumns'=>true,
                        //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                        'rows'=>[
                            [
                                'columns'=>12,
                                'autoGenerateColumns'=>false, // override columns setting
                                'attributes'=>[       // 3 column layout
                                    'goods_video'=>[   // radio list
                                        'columnOptions'=>['colspan'=>12],
                                        'type'=>Form::INPUT_WIDGET,
                                        'widgetClass'=>'\kartik\file\FileInput',
                                        'options' => [
                                            'options' => [
                                                'multiple' => false,
                                                'accept' => 'video/*'
                                            ],
                                            'pluginOptions'=>BootstrapFileInputConfigUtil::createInitConfigString(
                                                $model->goods_video,
                                                [
                                                    'BootstrapFileUpload[fileAttrName]'=>'Goods[goods_video]',
                                                    'goods_id'=>$model->id,
                                                ],
                                                [
                                                    'allowedFileTypes'=>["video"],
                                                    'uploadUrl'=>Url::to(['/goods/video-file-upload']),
                                                    'maxFileCount'=> 1,
                                                    // 'maxTotalFileCount'=> 2,
                                                    'validateInitialCount'=>true,
                                                ],
                                                Url::to(['/goods/video-file-remove'])
                                            ),
                                        ],
                                    ],
                                ]
                            ],
                        ]
                    ]);
                    ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>