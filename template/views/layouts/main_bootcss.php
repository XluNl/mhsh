<?php

/* @var $this \yii\web\View */
/* @var $content string */

use template\assets\LayerMobileAsset;
use template\assets\NewBootstrap;
use template\assets\WeuiJSAsset;
use yii\helpers\Html;

NewBootstrap::register($this);
LayerMobileAsset::register($this);
WeuiJSAsset::register($this);
?>
<?php $this->beginPage()?>
<!DOCTYPE html>
<html lang="<?=Yii::$app->language?>">
<head>
    <meta charset="<?=Yii::$app->charset?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, minimum-scale=1.0, user-scalable=0" />
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
