<?php

use backend\models\BackendCommon;
use common\models\BizTypeEnum;
use common\models\BonusBatchDrawLog;
use common\models\BusinessApply;
use common\models\Customer;
use common\models\DistributeBalanceItem;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuStock;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var backend\models\forms\ClaimBalanceForm $model
 * @var $bizOptions
 */
$this->title = '余额扣款';
$this->params['breadcrumbs'][] = ['label' => '资金账户列表', 'url' => ['/distribute-balance/index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
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
                                    'contentBefore'=>'<legend class="text-info"><small>填写信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => DistributeBalanceItem::$claimTypeArr, 'placeholder' => '选择赔款类型...', 'columnOptions' => ['colspan' => 2]],
                                        'biz_type' => [
                                            'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' => BackendCommon::addBlankOption(BizTypeEnum::getBizTypeOperaArr(BackendCommon::getFCompanyId())),
                                            'placeholder' => '选择账户类型...',
                                            'columnOptions' => ['colspan' => 2],
                                            'options'=>[
                                                'style'=>'display:inline',
                                                'onchange'=>'
                                                $.get("/biz-type/options?biz_type="+$(this).val(),function(data){             
                                                    $("#claimbalanceform-biz_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                            ]
                                        ],
                                        'biz_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => BackendCommon::addBlankOption($bizOptions),
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'theme'=> \kartik\select2\Select2::THEME_BOOTSTRAP,
                                                'options' => ['placeholder' => '选择账户名称...'],
                                                'pluginOptions' => [
                                                ],
                                            ]
                                        ],
                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入数量...'],'columnOptions'=>['colspan'=>1]],
                                        'remark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入备注...'],'columnOptions'=>['colspan'=>4]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton('确认扣款', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>