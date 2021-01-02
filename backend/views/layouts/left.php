<?php 
use yii\helpers\Url;
use backend\models\BackendCommon;
use mdm\admin\components\MenuHelper;
use yii\helpers\ArrayHelper;
use mdm\admin\components\Helper;
?>
<aside class="main-sidebar" style="position: fixed;">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                 <img src="<?php echo  Url::toRoute(BackendCommon::getPic())?>" class="img-circle" onerror="this.src='<?= $directoryAsset ?>/img/user2-160x160.jpg'" ></img>
            </div>
            <div class="pull-left info">
                <p style="overflow:hidden;width:160px;white-space:nowrap;text-overflow:clip;text-overflow: ellipsis;"><?=Yii::$app->user->identity->username; ?><?php if (!empty(Yii::$app->user->identity->nickname)) echo '('.Yii::$app->user->identity->nickname.')';?></p>
                <a href="#"><i class="fa fa-circle text-success"></i><?php echo BackendCommon::getRoleName();?></a>
            </div>
        </div>

        <!-- search form -->
<!--         <form action="#" method="get" class="sidebar-form"> -->
<!--             <div class="input-group"> -->
<!--                 <input type="text" name="q" class="form-control" placeholder="Search..."/> -->
<!--               <span class="input-group-btn"> -->
<!--                 <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i> -->
<!--                 </button> -->
<!--               </span> -->
<!--             </div> -->
<!--         </form> -->
        <!-- /.search form -->
        <?php 
            $allItems =
            [
                    ['label' => '菜单', 'options' => ['class' => 'header']],
                    //['label' => 'Gii', 'icon' => 'fa fa-file-code-o', 'url' => ['/gii']],
                    //['label' => 'Debug', 'icon' => 'fa fa-dashboard', 'url' => ['/debug']],
                    //['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
                    ['label' => '首页', 'icon' => 'fa fa-home', 'url' => [Yii::$app->homeUrl]],
                    [
                        'label' => '系统配置',
                        'icon' => 'fa fa-cogs',
                        'url' => ['#'],
                        'items' => [
                            ['label' => '型号管理', 'icon' => 'fa fa-dashboard', 'url' => ['/metermodels/list']],
                            ['label' => '区域管理', 'icon' => 'fa fa-sort-amount-asc', 'url' => ['/area/index']],
                            ['label' => '仪表参数管理', 'icon' => 'fa fa-pencil-square', 'url' => ['/fieldtypes/list']],
                            [
                                'label' => '能源配置',
                                'icon' => 'fa fa-wrench',
                                'url' => ['#'],
                                'items' => [
                                    ['label' => '能源管理', 'icon' => 'fa fa-flash', 'url' => ['/energys/list']],
                                    ['label' => '价格设置', 'icon' => 'fa fa-cog', 'url' => ['/pricetypes/list']],
                                    ['label' => '换算单位设置', 'icon' => 'fa fa-building', 'url' => ['/energyconversioncoefficienttypes/list']],
                                ],
                            ],                       
                        ],
                    ],
                    ['label' => '权限管理', 'icon' => 'fa fa-code-fork', 'url' => ['/admin/user'],
                        'items' => [
                            ['label' => '用户管理', 'icon' => 'fa fa-user', 'url' => ['/admin/user/list']],
                        ],
                    ],
                    [
                        'label' => '数据中心',
                        'icon' => 'fa fa-database',
                        'url' => ['/data/area'],
                        'items' => [
                        ],
                    ],
                    ['label' => '报表中心', 'icon' => 'fa fa-file-text', 'url' => ['/report/reporthomepage']
 
                    ],
                    [
                     'label' => '用能分析', 'icon' => 'fa fa-question-circle', 'url' => ['#'],
                     'items'=>[
                        ['label' => '能耗换算', 'icon' => 'fa fa-dashboard', 'url' => ['/energyanalyze/energyconversion']],
                        ['label' => '能耗预测', 'icon' => 'fa fa-dashboard', 'url' => ['/energyanalyze/energypredict']],
                        ['label' => '运行异常', 'icon' => 'fa fa-dashboard', 'url' => ['/energyanalyze/energyabnormalrunning']]
                     ]
                    ],
                    ['label' => '系统日志', 'icon' => 'fa fa-fax', 'url' => ['/userlog/list']],
                    ['label' => '检修记录', 'icon' => 'fa fa-fax', 'url' => ['/maintenance/list']],
                    ['label' => '公司审批', 'icon' => 'fa fa-fax', 'url' => ['/company/list']],
                    ['label' => 'APP商店', 'icon' => 'fa fa-shopping-cart', 'url' => ['/appstore/appstandard']],
                ];
            $callback = function ($menu) {
                //$data = eval($menu['data']);
                return [
                    'label' => $menu['name'],
                    'url' => [$menu['route']],
                    'icon' => $menu['data'],
                    'items' => $menu['children']
                ];
            };
            $items = MenuHelper::getAssignedMenu(Yii::$app->user->id,null,$callback,false); 
            $header = [['label' => '菜单', 'options' => ['class' => 'header']]];
            $items = ArrayHelper::merge($header, $items);
            //$items = Helper::filter($allItems);
             
        ?>
        <?php  
            
         ?>
        <?= backend\components\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree',"data-widget"=>"tree"],
                'items' => $items,
            ]
        ) ?>

    </section>

</aside>
