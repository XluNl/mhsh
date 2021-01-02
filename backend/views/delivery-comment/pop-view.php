<?php

/* @var $this yii\web\View */

use kartik\helpers\Html;

/* @var $images string */
/* @var $content string */

$images = explode(',',$images);
?>

<div class="box box-success">
    <div class="box-body">
        <div class="row">
            <div class="col-md-offset-1 col-md-10">
                <?php foreach ($images as $image): ?>
                    <?= Html::img($image,['style'=>'max-width:150px']); ?>
                <?php endforeach;?>
            </div>
            <div class="col-md-offset-1 col-md-10">
                <?= Html::tag('p',$content); ?>
            </div>
        </div>
    </div>
</div>
