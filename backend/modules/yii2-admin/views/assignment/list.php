<?php
use backend\assets\ICheckAsset;
use yii\helpers\Url;
use yii\helpers\VarDumper;
ICheckAsset::register($this);
$this->params['subtitle'] = '分配权限列表';
$this->params['breadcrumbs'] = [];
$this->params['breadcrumbs'][] = '权限分配';
?> 

<style>
.container-fluid .item{line-height:30px;padding-right:15px;}
</style>
<div class="container-fluid">
    <div class="row">
        <div class="box box-info">
            <div class="box-header with-border">
                            给<?php echo $name?>分配权限
            </div>
            <div class="box-body">
                <div class='col-lg-12'>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                                                        系统配置
                        </div>
                        <div class="box-body">
                            <?php foreach ($all as $key=>$val):?>
                                <div class='col-md-4 item'><input type="checkbox"  value="<?= $val?>">&nbsp;&nbsp;<label ><?= $val?></label></div>
                            <?php endforeach;?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php //echo VarDumper::dump($all);?>
<script type="text/javascript">
 
$(document).ready(function(){
	$('input[type=checkbox]').iCheck({
	    checkboxClass: 'icheckbox_square-red',
	    radioClass: 'iradio_square-red',
	    increaseArea: '20%' // optional
	  });
	<?php foreach ($assign as $val):?>
    	$('input[value="<?php echo $val?>"]').iCheck('check');
    <?php endforeach;?>
    
    
    $('input').on('ifChecked', function(event){
        var val = $(this).val();
      	$.ajax({
            "type"  : "POST",
            "url"   : "<?php echo Url::toRoute(['assign','id'=>$id]); ?>",
            "data"  : {items :[val]},
            success : function(data) {
                
            },
            error:function(e) {
            	alert('网络错误');
            }
        });
      	
    });
    $('input').on('ifUnchecked', function(event){
    	var val = $(this).val();
      	$.ajax({
            "type"  : "POST",
            "url"   : "<?php echo Url::toRoute(['revoke','id'=>$id]); ?>",
            "data"  : {items :[val]},
            success : function(data) {
                
            },
            error:function(e) {
            	alert('网络错误');
            }
        });
    });
});
	
</script>
