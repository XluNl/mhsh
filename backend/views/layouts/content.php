<?php

use backend\assets\JqueryTotalStorageAsset;
use dmstr\widgets\Alert;
use yii\widgets\Breadcrumbs;

JqueryTotalStorageAsset::register($this);
?>
<style>
    .content-header>.breadcrumb {
    	font-size: 18px;
    }
</style>
<div class="content-wrapper" style="margin-top: 50px;">
    <section class="content-header">
        

        <?php if (isset($this->blocks['content-header'])) { ?>
            <h1><?= $this->blocks['content-header'] ?></h1>
        <?php } else { ?>
            <h1>
                <?php
                if (isset($this->params['subtitle'])) {
                    echo \yii\helpers\Html::encode($this->params['subtitle']);
                }
                else if ($this->title !== null) {
                    echo \yii\helpers\Html::encode($this->title);
                } else {
                    echo \yii\helpers\Inflector::camel2words(
                        \yii\helpers\Inflector::id2camel($this->context->module->id)
                    );
                    echo ($this->context->module->id !== \Yii::$app->id) ? '<small>Module</small>' : '';
                } ?>
            </h1>
        <?php } ?>

        <?= Breadcrumbs::widget([
            'homeLink'=>[
                'label' => '主 页',
                'url' => Yii::$app->homeUrl
            ],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </section>

    <section class="content">
        <?= Alert::widget() ?>
        <?= $content ?>
    </section>
</div>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>版本</b> 0.1
    </div>
    <strong>Copyright &copy; 2019-2020</strong> <?php echo Yii::$app->name?>
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
        <li><a href="#control-sidebar-settings-tab" data-toggle="tab"></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        
        <!-- Settings tab content -->
        <div class="tab-pane active" id="control-sidebar-settings-tab">
            
                <h3 class="control-sidebar-heading" style="font-weight: bold;">背景设置</h3>
                <!--背景设置  -->
                <div class="form-group">
                    <label class="control-sidebar-subheading" id="home_page_right_tab_scroll">
                        左侧栏收缩
                        <input type="checkbox" class="pull-right" id="home_page_right_tab_scroll_checkbox" />
                    </label>
                </div>



                <div class="form-group">
                    <label class="control-sidebar-subheading">
                        我的背景
                    </label>
                </div>
                <ul class="list-unstyled clearfix">
                    <li style="float:left; width: 33.33333%; padding: 5px;">
                        <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-blue" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4);" class="clearfix full-opacity-hover">
                            <div>
                               <span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9;">                                   
                               </span>
                               <span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span>
                            </div>
                            <div>
                               <span style="display:block; width: 20%; float: left; height: 20px; background: #222d32;"></span>
                               <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                            </div>
                        </a>
                        <p class="text-center no-margin">蓝黑</p>
                    </li>
                <li style="float:left; width: 33.33333%; padding: 5px;">
                    <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-purple" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                    <div>
                    <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span>
                    <span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span>
                    </div>
                    <div>
                    <span style="display:block; width: 20%; float: left; height: 20px; background: #222d32;"></span>
                    <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                    </div>
                    </a>
                    <p class="text-center no-margin">紫黑</p>
                </li>
                <li style="float:left; width: 33.33333%; padding: 5px;">
                    <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-black" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                    <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                    <span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe;"></span>
                    <span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe;"></span>
                    </div>
                    <div>
                    <span style="display:block; width: 20%; float: left; height: 20px; background: #222;"></span>
                    <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                    </div></a>
                    <p class="text-center no-margin">白黑</p>
                </li>
                     <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-green" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span>
                     <span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #222d32;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin">绿黑</p>
                 </li>
                     <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-red" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span>
                     <span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #222d32;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin">红黑</p>
                 </li>
                 <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-yellow" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span>
                     <span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #222d32;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin">黄黑</p>
                 </li>
                 <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-blue-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9;"></span>
                     <span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px">蓝白</p>
                 </li>
                     <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-black-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix">
                     <span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe;"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe;">
                     </span>
                     </div>
                     <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px">黑白</p>
                 </li>
                     <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-purple-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span>
                     <span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px">紫白</p>
                 </li>
                 <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-green-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span>
                     <span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px">绿白</p>
                 </li>
                 <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-red-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span>
                     <span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span>
                     </div>
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px">红白</p>
                 </li>
                 <li style="float:left; width: 33.33333%; padding: 5px;">
                     <a href="javascript:void(0);"onclick="changeBackground(this)" data-skin="skin-yellow-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                     <div>
                     <span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;">
                     </span></div><div>
                     <span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc;"></span>
                     <span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7;"></span>
                     </div>
                     </a>
                     <p class="text-center no-margin" style="font-size: 12px;">黄白</p>
                 </li>
                </ul>



                <!--背景设置结束  -->
        </div>
    </div>
</aside>
<!-- <div class='control-sidebar-bg'>dfsdf</div> -->
<script>
<?php $this->beginBlock('js_end') ?> 
    initPage();
    // 初始化工作
    function initPage(){
        var local_profile_info = $.totalStorage('local_profile_info');//获取本地缓存数据
        if(local_profile_info == null){//无缓存，初始化阶段,设置默认配置
            var local_profile_info =  {};
            local_profile_info.is_left_sidebar_collapse = false;
            local_profile_info.background_info = 'skin-blue';
            $.totalStorage('local_profile_info',local_profile_info);
        }
        showInfoThroughLocalData(local_profile_info);
    }
    // 初始化工作结束
    // check事件
        
    // 按照本地缓存数据进行首页信息显示
    function showInfoThroughLocalData(profile_info){
        ;
        // 背景颜色
        var add_class = profile_info.background_info;
        $('body').attr("class","sidebar-mini");//先清空之前class属性值
        $('body').addClass(add_class);        
        // 左侧是否折叠
        var is_left_sidebar_collapse = profile_info.is_left_sidebar_collapse;
        if(is_left_sidebar_collapse){
          $('body').addClass('sidebar-collapse');
          $('#home_page_right_tab_scroll_checkbox').prop('checked',true);
        }
        else{
          $('body').removeClass('sidebar-collapse');
          $('#home_page_right_tab_scroll_checkbox').prop('checked',false);
        }
        // 右侧设置栏
    
    }
    // 按照本地缓存数据进行首页信息显示结束
    //设置左侧sidebar是否折叠 
    $('#home_page_right_tab_scroll').click(function() {
     // var local_profile_info = $.totalStorage('local_profile_info');  //读取本地缓存数据    
     if($('#home_page_right_tab_scroll_checkbox').is(':checked')){
        $('body').addClass('sidebar-collapse');
        // local_profile_info.is_left_sidebar_collapse = true;          
        //更新本地缓存数据 sidebar状态
        saveToLocalhost(true,'is_left_sidebar_collapse');
        // $.totalStorage('local_profile_info', local_profile_info);
     }  
     else{
        $('body').removeClass('sidebar-collapse');
        // local_profile_info.is_left_sidebar_collapse = false;        
        //更新本地缓存数据 sidebar状态
        saveToLocalhost(false,'is_left_sidebar_collapse');
        // $.totalStorage('local_profile_info', local_profile_info);
     }     
    });
    //设置左侧sidebar是否折叠结束 
    // 设置背景颜色
    function changeBackground(val){
        var obj = $(val);
        var add_class = obj.attr('data-skin');
        $('body').attr("class","sidebar-mini");//先清空之前class属性值
        $('body').addClass(add_class);
        saveToLocalhost(add_class,'background_info');
    }
    // 设置背景颜色结束
    // 记录在本地
    function saveToLocalhost(profile_info,type){
        var local_profile_info = $.totalStorage('local_profile_info');
        if(local_profile_info == null){
            local_profile_info = {};
        }
        switch(type){
            case 'background_info': local_profile_info.background_info = profile_info;break;
            case 'is_left_sidebar_collapse':local_profile_info.is_left_sidebar_collapse = profile_info;break;
            default:breal;
        }
        $.totalStorage('local_profile_info', local_profile_info);
    }
    // changeHomePageLayout();
    // 改变主页图表显示布局结束
    
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
<!-- js部分结束 -->