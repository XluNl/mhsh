<?php
 use yii\helpers\Url;
$action = Yii::$app->controller->action->id;
?>
<nav class="navbar square navbar-primary" role="navigation">
        <div class="container-fluid">
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-7">
                <ul class="nav navbar-nav">
                    <li   <?php if ($action == "list"): ?>class="active"<?php endif;?>>
                        <a href="<?php echo Url::toRoute(array('payment/list','payment_id'=>$model->id));?>">
                            <span class="fa fa-th-list"></span>&nbsp;支付方式列表
                        </a>
                    </li>
                    &nbsp;
                    <li <?php if ($action == "goodsamount"): ?>class="active"<?php endif;?>>
                        <a href="<?=Url::to(['payment/modify']);?>">
                            <span class="fa fa-plus"></span>&nbsp;添加支付方式
                        </a>
                    </li>
                </ul>
            </div>
        </div>
</nav>