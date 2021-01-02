<?php
 use yii\helpers\Url;
use yii\helpers\Html;
$action = Yii::$app->controller->action->id;
if (!isset($filter)){
    $filter = "all";
}
?>
<div class="alert alert-warning alert-bold-border fade in alert-dismissable">
	 <p><button class="btn btn-primary" onclick="location='<?php echo Url::toRoute('/goods/modify'); ?>'">添加新商品</button></p>
</div>
<nav class="navbar square navbar-primary" role="navigation">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-7">
                <ul class="nav navbar-nav">
                    <li <?php if ($action == "lists"&&$filter=='all'): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['goods/lists','filter'=>'all']);?>">
                            <span class="fa fa-th-list"></span>&nbsp;商品列表
                        </a>
                    </li>
                    <li <?php if ($action == "lists"&&$filter=='new'): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['goods/lists','filter'=>'new']);?>">
                            <span class="fa fa-th-list"></span>&nbsp;新增商品列表
                        </a>
                    </li>
                    <li <?php if ($action == "lists"&&$filter=='delete'): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['goods/lists','filter'=>'delete']);?>">
                            <span class="fa fa-th-list"></span>&nbsp;已经删除商品
                        </a>
                    </li>
                    <li <?php if ($action == "download"): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['goods/download']);?>">
                            <span class="fa fa-cloud-download"></span>&nbsp;全部商品下载
                        </a>
                    </li>
                    <li <?php if ($action == "upload"): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['goods/upload']);?>">
                            <span class="fa fa-cloud-upload"></span>&nbsp;批量上传
                        </a>
                    </li>
                </ul>
            </div>
        </div>
</nav>