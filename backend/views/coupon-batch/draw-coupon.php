<?php

use common\models\Customer;
use common\models\GoodsConstantEnum;
use common\models\GoodsSkuStock;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;


/* @var backend\models\forms\DrawCouponForm $model
 * @var Customer $customerModel
 */
$this->title = '手动发放优惠券';
$this->params['breadcrumbs'][] = ['label' => '用户优惠券列表', 'url' => ['/coupon/index','CouponSearch[customer_id]'=>$customerModel['id']]];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <div class="box-title">给<?php echo "{$customerModel['nickname']}（{$customerModel['phone']}）" ?>手动发放优惠券</div>
                        <div class="box-tools pull-right">
                            <?php echo Html::a('优惠券记录',  ['/coupon/index','CouponSearch[customer_id]'=>$customerModel['id']], ['class' => 'btn btn-warning']) ?>
                        </div>
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
                                    'attributes' => [       // 3 column layout
                                        'customer_id' => ['type' => Form::INPUT_HIDDEN],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写发放信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'batch_no'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入优惠券批次号...'],'columnOptions'=>['colspan'=>5]],
                                        'num'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入发放数量...'],'columnOptions'=>['colspan'=>2]],
                                        'remark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入备注...'],'columnOptions'=>['colspan'=>5]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton('发放', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>