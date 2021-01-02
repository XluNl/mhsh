<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */
$this->params['breadcrumbs'] =[];
$this->title = $name;
?>
<!-- Main content -->
<section class="content">

    <div class="error-page">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3><?= $name ?></h3>

            <p>
                <?= nl2br(Html::encode($message)) ?>
            </p>
            <form class='search-form'>
                <div class='input-group'>
                    <div class="input-group-btn">
                        <a  class="btn btn-primary" href="<?php echo Url::toRoute(['/']); ?>"><i class="fa fa-home">跳转到主页</i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>
