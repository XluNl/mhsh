<?php
use yii\helpers\Url;
use yii\widgets\LinkPager;
use common\models\Sggoods;
use common\models\Common;
?>

<div class="container-fluid">
	<h1 class="page-heading">微信支付记录</h1>
	<div class="panel with-nav-tabs panel-primary">
		<div class="panel-heading">
		</div>
		<div class="panel-body">
			<div class="panel-body">
			    <div class="row">
			        <div class="col-md-3 col-md-offset-3">
			              <input type="text" class="form-control" value="<?=$keyword;?>" id="keyword" placeholder="输入搜索关键字"/>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-info" onclick="search()"><i class="fa fa-search"></i>&nbsp;点击搜索</button>
                    </div>
			    </div>
			    </div>
		   <div class="panel-body">
				<div class="row">
					<div class="table-responsive">
												<table class="table table-th-block table-default">
													<thead>
														<tr>
															<th>序号</th>
															<th>订单号</th>
															<th>订单创建时间</th>
															<th>微信支付订单号</th>
															<th>支付费用</th>
															<th>收货餐厅</th>
															<th>收货人</th>
															<th>用户标识符</th>
															<th>支付方式</th>
															<th>支付时间</th>
															<th>操作</th>
														</tr>
													</thead>
													<tbody>
														<?php foreach ($models as $key => $model): ?>
														<tr>
															<td><strong><span class="span span-info"><?php echo $model["id"]; ?></span></strong></td>
															<td><?php echo $model["order_no"]; ?></td>
															<td><?php echo date("Y-m-d H:i:s", $model["create_time"]); ?></td>
															<td><?php echo $model["transaction_id"]; ?></td>
															<td><?php echo Common::showAmount($model["total_fee"]*10); ?>元</td>
															<td><?php echo $model["accept_restaurant"]; ?></td>
															<td><?php echo $model["accept_name"]; ?></td>
															<td><?php echo $model["openid"]; ?></td>
															<td><?php echo $model["bank_type"]; ?></td>
															<td><?php echo $model["time_end"]; ?></td>
															<td>
															 <button onclick="location='<?php echo Url::toRoute(['/order/detail','order_no'=>$model['order_no']]); ?>'" class="btn btn-warning btn-xs"><i class="fa fa-cloud"></i>查看该订单</button>
															</td>
														</tr>
														<?php endforeach;?>
													</tbody>
												</table>
											</div>
											<?php echo LinkPager::widget(array('pagination' => $pages)); ?>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>

function  search(){
	var url = "<?php echo Url::toRoute('payment/wxlist'); ?>?";
	var keyword = $("#keyword").val();
 
	if (keyword !== undefined) {
		url = url + "&keyword="+keyword;
	}
 
	window.location.href = url;
}
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
