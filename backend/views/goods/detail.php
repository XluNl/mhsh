<?php

use yii\helpers\Html;
use yii\redactor\widgets\Redactor;
use yii\widgets\ActiveForm;
?>
<div class="container-fluid">
    <h1 class="page-heading">商品详情页修改</h1>

    <div class="the-box">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3">
                <?php $form = ActiveForm::begin([]);?>
                <?php echo $form->field($model, "goods_id")->hiddenInput()->label(""); ?>
                <?= $form->field($model, 'goods_detail')->widget(Redactor::className(), [
                    'clientOptions' => [
                        //'imageManagerJson' => ['/redactor/upload/image-json'],
                        //'imageUpload' => ['/redactor/upload/image'],
                        //'fileUpload' => ['/redactor/upload/file'],

                        'lang' => 'zh_cn',
                        //'plugins' => ['clips', 'fontcolor','imagemanager']
                    ]
                ])?>
                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
                    <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-warning btn-lg']) ?>
                </div>
                <?php ActiveForm::end();?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>