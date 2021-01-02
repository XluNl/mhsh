<?php

use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var common\models\StorageBind $model */
$this->title = '绑定仓库';
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-6 col-xs-offset-3">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">选择绑定仓库</h3>
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
                                    'contentBefore'=>'<legend class="text-info"><small>选择绑定仓库</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'storage_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $model->storageArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton( '绑定', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-6 btn btn-primary btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
