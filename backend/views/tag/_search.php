<?php

use backend\models\BackendCommon;
use common\models\Tag;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\searches\TagSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'action' => ['index'],
                    'method' => 'get',
                    'id' => 'tagSearchForm',
                ]);

                echo FormGrid::widget([
                    'model' => $model,
                    'form' => $form,
                    'autoGenerateColumns' => true,
                    'rows' => [
                        [
                            'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                            'columns' => 12,
                            'autoGenerateColumns' => false, // override columns setting
                            'attributes' => [       // 3 column layout
                                'group_id' => [
                                    'type' => Form::INPUT_DROPDOWN_LIST,
                                    'items' => BackendCommon::addBlankOption(Tag::$groupArr),
                                    'placeholder' => '选择组...',
                                    'columnOptions' => ['colspan' => 2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'onchange'=>'
                                                $.get("/tag/options?group_id="+$(this).val(),function(data){             
                                                    $("#tagsearch-tag_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                    ]
                                ],
                                'tag_id'=>[   // radio list
                                    'columnOptions'=>['colspan'=>2],
                                    'type'=>Form::INPUT_WIDGET,
                                    'widgetClass'=>'\kartik\widgets\Select2',

                                    'options'=>[
                                        'data' => BackendCommon::addBlankOption($model->tagOptions),
                                        'model' => $model,
                                        'language' => 'zh-CN',
                                        'theme'=> Select2::THEME_BOOTSTRAP,
                                        'options' => ['placeholder' => '选择标签...'],
                                        'pluginOptions' => [
                                            'allowClear' => true
                                        ],
                                    ]
                                ],
                                'biz_name' => ['type' => Form::INPUT_TEXT, 'options' => ['placeholder' => '输入业务名称...'], 'columnOptions' => ['colspan' => 3    ]],
                            ]
                        ],
                    ]
                ]);
                ?>

            </div>
            <div>
                <div class="form-group">
                    <?= Html::submitButton('查询', ['class' => 'col-xs-offset-9 btn btn-primary']) ?>
                    <?= Html::resetButton('重置', ['class' => ' btn btn-default']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
