<?php
backend\assets\AppAsset::register($this);
backend\assets\LayerAsset::register($this);
backend\assets\JFormValidateAsset::register($this);
use backend\assets\JqueryTotalStorageAsset;
use dmstr\widgets\Alert;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

JqueryTotalStorageAsset::register($this);
?>
<style>
    .content-header>.breadcrumb {
    	font-size: 18px;
    }
    .content{
    	background: white;
    }
    .box-success{
    	background: white;
    }
</style>

<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<?php $this->head() ?>
	<title><?= Html::encode($this->title) ?></title>
</head>
<body>
<?php $this->beginBody(); ?>
	<div class="content-wrapper" style="">
    <section class="content" style="padding:10px 30px;">
        <?= Alert::widget() ?>
        <?= $content ?>
    </section>
</div>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>