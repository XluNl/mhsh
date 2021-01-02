<?php

/* @var $this \yii\web\View */
/* @var $content string */

use inner\assets\Bootstrap;
use yii\helpers\Html;

Bootstrap::register($this);
?>
<?php $this->beginPage()?>

<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">
<head>
    <meta charset="<?=Yii::$app->charset?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=0" />
    <meta name="format-detection" content="telephone=no" />
    <?=Html::csrfMetaTags()?>
    <title><?=Html::encode($this->title)?></title>
    <?php $this->head()?>
</head>
<body class="tooltips" style="background-color:#171818; padding:0px;margin-bottom:0px;">
<?php $this->beginBody()?>
<div class="wrapper" style="background-color:#171818;">

    <div class="page-content" style="background-color:#171818;">
        <?php echo $content ?>
    </div>
</div>
<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>