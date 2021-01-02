<?php

/* @var $this \yii\web\View */
/* @var $content string */

use official\assets\AppAsset;
use yii\helpers\Html;

AppAsset::register($this);
$this->registerJs(
    $this->render('@webroot/js/common.js'),
    \yii\web\View::POS_HEAD
);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">
<head>
    <meta charset="<?=Yii::$app->charset?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=0" />
    <meta name="format-detection" content="telephone=no" />
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <?=Html::csrfMetaTags()?>
    <title><?=Html::encode($this->title)?></title>
    <?php $this->head()?>
</head>
<body>

<?php $this->beginBody()?>
<?php echo $content ?>
<?php $this->endBody()?>
</body>
</html>
<?php $this->endPage()?>
