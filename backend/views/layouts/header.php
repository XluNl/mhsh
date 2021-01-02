<?php
use yii\helpers\Html;
use backend\models\BackendCommon;
use yii\helpers\Url;
use mdm\admin\components\AccessControl;
/* @var $this \yii\web\View */
/* @var $directoryAsset string */
?>

<header class="main-header" style="position: fixed;width: 100%;">

    <?= Html::a('<span class="logo-mini">'.BackendCommon::getCompanyName().'</span><span class="logo-lg" style="font-size:16px;">' . BackendCommon::getCompanyName() . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle " data-toggle="push-menu" role="button">
            <span class="sr-only"></span>
        </a>
      
        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-warning"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">您有&nbsp;<span class="label label-warning"><?php //echo $alert_msg_cnt;?></span>&nbsp;条新警报</li>
                        <li>
                            <!-- inner menu: contains the actual data -->
                            <?php if (!empty($alert_msg_arr)):?>
                            <ul class="menu">
                                <?php foreach ($alert_msg_arr as $val):?>
                                <li>
                                    <a href="<?php echo Url::toRoute(['/alertmsg/list'])?>">
                                        <div class="pull-left"><span class="label label-danger"><?php echo $val['base_meter_name']?></span></div>
                                        <div class="pull-right"><span class="label label-info"><?php echo date('Y-m-d H:i:s',strtotime($val['add_time']))?></span></span></div> 
                                        <div style="clear:both;height:7px;width:0"></div> 
                                        <div style="width:100%;white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <span class="label label-warning" ><?php echo $val['msg']?></span>
                                        </div>
                                    </a>
                                </li>   
                                <?php endforeach;?>
                            </ul>
                            <?php endif;?>
                        </li>
                        <li class="footer"><a href="<?php echo Url::toRoute(['/alertmsg/list'])?>">查看所有警报</a></li>
                    </ul>
                </li>
                <!-- User Account: style can be found in dropdown.less -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo  Url::toRoute(BackendCommon::getPic())?>" class="user-image" onerror="this.src='<?= $directoryAsset ?>/img/user2-160x160.jpg'" ></img>
                        <span class="hidden-xs"><?=Yii::$app->user->identity->username; ?><?php if (!empty(Yii::$app->user->identity->nickname)) echo '('.Yii::$app->user->identity->nickname.')';?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?php echo  Url::toRoute(BackendCommon::getPic())?>" class="img-circle" onerror="this.src='<?= $directoryAsset ?>/img/user2-160x160.jpg'" ></img>

                            <p>
                                                         <?php echo BackendCommon::getMark();?>
<!--                                 <small>惺惺惜惺惺</small> -->
                            </p>
                        </li>
                        <!-- Menu Body -->
<!--                         <li class="user-body"> -->
<!--                             <div class="col-xs-4 text-center"> -->
<!--                                 <a href="#">Followers</a> -->
<!--                             </div> -->
<!--                             <div class="col-xs-4 text-center"> -->
<!--                                 <a href="#">Sales</a> -->
<!--                             </div> -->
<!--                             <div class="col-xs-4 text-center"> -->
<!--                                 <a href="#">Friends</a> -->
<!--                             </div> -->
<!--                         </li> -->
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <?= Html::a('账户设置', ['/admin-user-info/index'], ['class' => 'btn btn-default btn-flat']) ?>
                                <?= Html::a('修改密码', ['/admin-user-info/change-password'], ['class' => 'btn btn-default btn-flat']) ?>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    '注销',
                                    ['/admin/user/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less -->
                <li id="home_setting">
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-wrench"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
