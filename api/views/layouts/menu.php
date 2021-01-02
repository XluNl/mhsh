<?php
use yii\helpers\Url;
$menus = include Yii::$app->basePath . "/config/menu.php";
?>
<div class="sidebar-left sidebar-nicescroller">
    <ul class="sidebar-menu">
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $key => $value): ?>
                <?php if (!empty($value['static'])): ?>
                    <li class="static"><?php echo $value['static'];?></li>
                <?php endif;?>
                <?php if (!empty($value['fake'])): ?>
                    <?php foreach ($value['fake'] as $_v): ?>
                        <?php
$liclass = (Yii::$app->controller->id == $_v['controller']) ? "active selected" : "";
$ulclass = (Yii::$app->controller->id == $_v['controller']) ? "visible" : "";
?>
                        <li class="<?php echo $liclass;?>">
                            <a href="<?php echo Url::toRoute($_v["url"]);?>">
                                <i class="fa <?php echo $_v['icon'];?> icon-sidebar"></i>
                                <?php if (!empty($_v['submenu'])): ?>
                                    <i class="fa fa-angle-right chevron-icon-sidebar"></i>
                                <?php endif;?>
                                <?php echo $_v['name'];?>
                                <span class="badge <?php echo $_v['label']['class'];?> span-sidebar"><?php echo $_v['label']['text'];?></span>
                            </a>
                            <?php if (!empty($_v['submenu'])): ?>
                                <ul class="submenu <?php echo $ulclass;?>">
                                    <?php foreach ($_v['submenu'] as $submenu): ?>
                                        <li <?php if (Yii::$app->controller->action->id == $submenu['action'] && $ulclass == "visible"): ?> class="active selected"<?php endif;?>>
                                            <a href="<?php echo Url::toRoute($submenu["url"]);?>"> <?php echo $submenu['name'];?></a></li>
                                    <?php endforeach;?>
                                </ul>
                            <?php endif;?>
                        </li>
                    <?php endforeach;?>
                <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
    </ul>
</div>

