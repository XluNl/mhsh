<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Payment2;
?>
<div class="container-fluid">
	<h1 class="page-heading">支付方式添加或修改</h1>
    <?=\Yii::$app->view->render("topnavbar", ['model'=>$model]);?>
	<div class="the-box">
		<div class="row">
			<div class="col-lg-6 col-lg-offset-3">
                <?php $form = ActiveForm::begin([]);?>
                <?php echo $form->field($model, "pay_name"); ?>
                    <?php echo $form->field($model, "pay_account"); ?>
                    <?php echo $form->field($model, "pay_type")->dropDownList(Payment2::$pay_type_list); ?>
                    <?php echo $form->field($model, "pay_status")->dropDownList(Payment2::$pay_status_list); ?>
                    <?php echo $form->field($model, "pay_describe")->textarea(array('row' => 3)); ?>
                    <?php echo $form->field($model, "pay_class"); ?>
                <?=Html::submitButton('点击提交', ['class'=>'btn btn-primary','name' =>'submit-button'])?>
                <?php ActiveForm::end();?>
            </div>
		</div>
	</div>
</div>
