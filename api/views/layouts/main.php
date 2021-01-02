<?php

/* @var $this \yii\web\View */
/* @var $content string */

use api\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">
<head>
    <meta charset="<?=Yii::$app->charset?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?=Html::csrfMetaTags()?>
    <title><?=Html::encode($this->title)?></title>
    <?php $this->head()?>
</head>
<body class="tooltips">
<?php $this->beginBody()?>
<div class="wrap" >
    <?php echo \Yii::$app->view->render("topBar"); //renderFile 必须写好完整的目录      ?>
    <?php echo \Yii::$app->view->render("menu"); //renderFile 必须写好完整的目录      ?>
    <div class="page-content">
        <?php echo $content ?>
    </div>
</div>

<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>
