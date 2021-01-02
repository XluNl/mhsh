<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\Common;
use common\models\Captcha;
?>
<div class="container-fluid">
	<h1 class="page-heading">验证码列表</h1>

	<div class="alert alert-warning alert-bold-border fade in alert-dismissable">
		<p><strong>验证码列表</strong>。</p>
	</div>
	<div class="the-box">
					
					<div class="panel-body">
    	<div class="row">
    	   <div class="col-md-2 col-md-offset-2">
                 <input type="text" class="form-control" value="<?=$keyword;?>" id="keyword" placeholder="输入搜索关键字"/>
           </div>
           <div class="col-md-3">
                 <button class="btn btn-info" onclick="search()"><i class="fa fa-search"></i>&nbsp;点击搜索</button>
           </div>
    	</div>
    	</div>
		<div class="row">
				<div class="col-lg-12">
					<?=GridView::widget(['dataProvider' => $dataProvider,
                    	'columns' => [
                    	    'id',
                    	    'data',
                    	    'code',
                    	    [
                        	    'attribute'=>'status',
                        	    'value' => function ($model) {
                        	        return Captcha::$statusArr[$model->status];
                        	    },
                    	    ],
                    	    [
                        	    'attribute'=>'sort',
                        	    'value' => function ($model) {
                        	        return Captcha::$sortArr[$model->sort];
                        	    },
                    	    ],
                    	    'recode',
                    	    'remark',
                    	    'ip',
                    	    'fail_num',
                            'created_at'
                    	],
                    ]);?>
				</div>
			</div>
	</div>
</div>

<script type="text/javascript">
	function search(){
		var url = "<?php echo Url::toRoute(['captcha/list']); ?>?";
		var keyword = $("#keyword").val();
		if (keyword !== undefined) {
			url = url + "&keyword="+keyword;
		}
		window.location.href = url;
	}
</script>