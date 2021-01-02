<?php
use yii\helpers\Url;
?>
<div class="container-fluid">
	<h1 class="page-heading">操作成功</h1>
	<div class="the-box">
		<div class="row">
			<div class="col-md-3 col-md-offset-4 text-center">
				<i class="fa fa-check icon-lg icon-circle icon-primary"></i>
				<br/><br/>
				<h4><strong>操作成功</strong></h4>
				<p><strong><?=empty($message) ? "" : $message;?></strong></p>
				<p>如有疑问请联系开发人员</p>
				<br/><br/>
				<a href="<?=empty($url) ? Url::toRoute('/site/index') : $url;?>" class="btn btn-primary btn-block">点击确定</a>
			</div>
		</div>
	</div>
</div>