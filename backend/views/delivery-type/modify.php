<?php

use backend\models\BackendCommon;
use common\models\Delivery;
use common\models\DeliveryType;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var common\models\Popularizer $model */
/* @var Delivery $deliveryModel */
$this->title = '发货团长信息保存';
$this->params['breadcrumbs'][] = ['label' => '发货团长列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-md-8 col-md-offset-2">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading"><?php echo $deliveryModel['nickname']?>(<?php echo $deliveryModel['realname'].'-'.$deliveryModel['phone']?>)配送费用保存</h3>
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
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'delivery_type' => [
                                                'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' =>GoodsConstantEnum::$deliveryTypeSelfArr,
                                            'placeholder' => '选择配送方式...',
                                            'columnOptions' => ['colspan' => 6],
                                            'options'=>array_merge([

                                            ],$model->isNewRecord?[]:['disabled'=>''])],
                                        'params'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入配送金额...'],'columnOptions'=>['colspan'=>6]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index','delivery_id'=>$deliveryModel['id']], ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>