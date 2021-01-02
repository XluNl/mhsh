<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<div class="container-fluid">
    <h1 class="page-heading">商品批量操作</h1>
    <div class="alert alert-danger alert-bold-border fade in alert-dismissable">
        <p><strong>请按照指定的操作上传指定的文件,商品下载不包含已经删除的商品</strong>。</p>
    </div>
    <?=\Yii::$app->view->render("topnavbar", []);?>
    <div class="panel with-nav-tabs panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">商品批量操作</h3>
        </div>
        <div class="panel-body">
            <?php $form = ActiveForm::begin([
                'id' => 'goods-form',
                'options' => ['class' => 'form-horizontal', 'enctype' => 'multipart/form-data'],
                'fieldConfig' => [
                    'template' => '<div class="row">{label}<div class="col-lg-3">{input}{error}</div></div>',
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]);?>
            <?=$form->field($uploadModel, 'operate')->dropDownList([
                'modify_goods_info' => '批量修改商品信息',
                'insert_new_goods' => '批量添加新品',
                'upload_small_pic' => '批量上传缩略图（支持ZIP格式,根目录中需直接为图片）',
                'upload_big_pic' => '批量上传高清图（支持ZIP格式,根目录中需直接为图片）',
                'upload_sort_pic' => '批量上传分类图（支持ZIP格式,根目录中需直接为图片）'
            ]); ?>
            <?=$form->field($uploadModel, 'file')->fileInput() ?>
            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-11">
                    <?=Html::submitButton('点击提交', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?php ActiveForm::end();?>
        </div>
    </div>
</div>
