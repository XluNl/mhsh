<?php

use yii\helpers\Html;
use yii\helpers\Url;

?>
<div class="top-navbar">
        <div class="top-navbar-inner">
            <div class="logo-brand">
                <a href="<?php echo Url::toRoute('/site/index');?>">
                <?php echo Html::img("@web/images/logo.jpg", array('alt' => 'LOGO'));?></a>
            </div>
            <div class="top-nav-content no-left-sidebar">
                <ul class="nav-user navbar-right">
                <?php if (!Yii::$app->user->isGuest): ?>
                    <li class="dropdown">
                        <a href="#fakelink" class="dropdown-toggle" data-toggle="dropdown">
                            <?php echo Html::img('@web/images/avatar/avatar.jpg', array('alt' => 'Avatar', 'class' => 'avatar img-circle'));?>
                                欢迎您, <strong><?php echo Yii::$app->user->identity->username;?></strong>
                        </a>
                        <ul class="dropdown-menu square primary margin-list-rounded with-triangle">
                            <li><a href="<?php echo Url::toRoute("user/default/index");?>">我的主页</a></li>
                            <li><a href="#fakelink">修改密码</a></li>
                            <li class="divider"></li>
                            <li><a href="lock-screen.html">锁屏</a></li>
                            <li><a href="<?php echo Url::toRoute('/account/logout');?>">退出登录</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="dropdown">
                        <a href="#fakelink" class="dropdown-toggle" data-toggle="dropdown">
                            <?php echo Html::img('@web/images/avatar/avatar.jpg', array('alt' => 'Avatar', 'class' => 'avatar img-circle'));?>
                                欢迎您, <strong>Guest</strong>
                        </a>
                        <ul class="dropdown-menu square primary margin-list-rounded with-triangle">
                            <li><a href="<?php echo Url::toRoute('account/login');?>">点击登录</a></li>
                            <li><a href="<?php echo Url::toRoute('account/reg');?>">点击注册</a></li>
                        </ul>
                    </li>
                <?php endif;?>
                </ul>
                <div class="collapse navbar-collapse" id="main-fixed-nav">
                    <ul class="nav navbar-nav navbar-left">
                        <li class="dropdown">
                            <a href="#fakelink" class="dropdown-toggle" data-toggle="dropdown">
                                <span class="badge badge-danger icon-count">0</span>
                                <i class="fa fa-bell"></i>
                            </a>
                            <ul class="dropdown-menu square with-triangle">
                                <li>
                                    <div class="nav-dropdown-heading">
                                        通知
                                    </div><!-- /.nav-dropdown-heading -->
                                    <div class="nav-dropdown-content scroll-nav-dropdown">
                                        <ul>
                                            <li><a href="#fakelink">
                                                <img src="assets/img/avatar/avatar-10.jpg" class="absolute-left-content img-circle" alt="Avatar">
                                                        <strong>Carl Rodriguez</strong> joined your weekend party
                                                        <span class="small-caps">April 01, 2014</span>
                                                    </a></li>
                                                </ul>
                                    </div><!-- /.nav-dropdown-content scroll-nav-dropdown -->
                                    <button class="btn btn-primary btn-square btn-block">See all notifications</button>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </div>