<?php

use backend\assets\AppAsset;
use dmstr\web\AdminLteAsset;
use light\widgets\LockFormAsset;
use yii\helpers\Html;
use yii\helpers\Url;
/* @var $this \yii\web\View */
/* @var $content string */
use backend\assets\BootboxAsset;
use backend\assets\LayDateAsset;
use backend\assets\LayerAsset;
use backend\models\BackendCommon;
use yii\web\View;

LayDateAsset::register($this);
LayerAsset::register($this);
BootboxAsset::register($this);
BootboxAsset::overrideSystemConfirm();
LockFormAsset::register($this);


$this->registerJs(
    $this->render('@webroot/js/main.js'),
    View::POS_HEAD
);
if (empty($this->title)){
    $this->title =  BackendCommon::getCompanyName();
}
if (Yii::$app->controller->action->id === 'login') { 
/**
 * Do not use this code in your template. Remove it. 
 * Instead, use the code  $this->layout = '//main-login'; in your controller.
 */
    echo $this->render(
        'main-login',
        ['content' => $content]
    );
} else {

    backend\assets\AppAsset::register($this);
    dmstr\web\AdminLteAsset::register($this);
    Yii::$app->assetManager->publish('@vendor/almasaeed2010/adminlte/dist');
    $directoryAsset = Yii::$app->assetManager->getPublishedUrl('@vendor/almasaeed2010/adminlte/dist');
    ?>
    <?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <link type="image/x-icon" href="<?php echo Url::toRoute("/logo.png")?>" rel="shortcut icon">
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="hold-transition skin-blue sidebar-mini" style="font-family: 微软雅黑, 'Microsoft Yahei', 'Hiragino Sans GB', tahoma, arial, 宋体;">
    <?php $this->beginBody() ?>
    <div class="wrapper">

        <?= $this->render(
            'header.php',
            ['directoryAsset' => $directoryAsset]
        ) ?>

        <?= $this->render(
            'left.php',
            ['directoryAsset' => $directoryAsset]
        )
        ?>

        <?= $this->render(
            'content.php',
            ['content' => $content, 'directoryAsset' => $directoryAsset]
        ) ?>

    </div>

    <?php $this->endBody() ?>
    </body>
    </html>
    <?php $this->endPage() ?>
<?php } ?>
