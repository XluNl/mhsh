<?php

use yii\helpers\Url;
use yii\widgets\LinkPager;
use backend\assets\JqueryDateTimePickerAsset;
JqueryDateTimePickerAsset::register($this);
$this->params['subtitle'] = '历史记录列表';
$this->params['breadcrumbs'][] = '历史记录列表';
?> 
<div class="container-fluid">
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <span>历史记录</span>
                <div class="pull-right">
                      <div class="input-append date inline">
                    <span>起始时间：</span>
                    <input class="span2" size="12" type="text" id="user_log_search_start_time"  style="width: 150px;" value="<?php echo $start_time;?>">
                  </div>   
                  <div class="input-append date inline" >
                    <span>结束时间：</span>
                    <input class="span2" size="12" type="text" id="user_log_search_end_time" style="width: 150px;"  value="<?php echo $end_time;?>">
                  </div>  
                  <div class="input-append date inline">
                    <button class="btn btn-danger btn-flat btn-sm" id="search_btn" onclick="userLogSearch()"> 查询</button>
                  </div>
                </div>                  
                </div>                
            
            <div class="box-body">
                <table class="table table-bordered table-striped" style="text-align: center;">
                    <thead >
                        <tr>
                            <td>编号</td>
                            <td>时间</td>
                            <td>模块</td>
                            <td>IP</td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $noid = 1 + $pages->pageSize*$pages->page;?>
                    <?php foreach ($models as $key => $model): ?>
                        <tr>
                            <td><strong><span class="span span-info"><?php echo $noid++; ?></span></strong></td>
                            <td><?php echo date("Y-m-d H:i:s", strtotime($model["create_time"])); ?></td>
                            <td><?php echo $model["remark"]; ?></td>
                            <td><?php echo $model["ip"]; ?></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
            </div>
            <div class="box-footer clearfix"><?php echo LinkPager::widget(array('pagination' => $pages)); ?></div>
        </div>
    </div>
</div>
<script>
<?php $this->beginBlock('js_end') ?>
// 首页设置框
$('#home_setting').hide();
// 首页设置框结束
$.datetimepicker.setLocale('ch');//设置中文
$('#user_log_search_start_time').datetimepicker({
  format:"Y-m-d H:i", //格式化日期
  yearStart:2001, //起始日期
  yearEnd: 2050, //结束日期
  timepicker:true,//关闭时间选择
  todayButton:true //打开今天按钮
});
$('#user_log_search_end_time').datetimepicker({
  format:"Y-m-d H:i", //格式化日期
  yearStart:2001, //起始日期
  yearEnd: 2050, //结束日期
  timepicker:true,//关闭时间选择
  todayButton:true //打开今天按钮
});
function userLogSearch(){
	var start_time = $('#user_log_search_start_time').val();
	var end_time = $('#user_log_search_end_time').val();
	location.href= "<?php echo Url::toRoute(['userlog/list'])?>"+'?start_time='+start_time +'&end_time='+end_time;
}
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
<!-- js部分结束 -->