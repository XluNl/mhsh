<?php
use common\models\Payment2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;

?>
<div class="container-fluid">
	<h1 class="page-heading">支付方式管理</h1>
	 <?=\Yii::$app->view->render("topnavbar", ['model'=>$model]);?>
	<div class="panel with-nav-tabs panel-primary">
		<div class="panel-heading">
		</div>
		<div class="panel-body">
			
		   <div class="panel-body">
				<div class="tab-content">
									<div class="tab-pane fade in active" id="pay-list">
									<br/>
										<div class="row">
											<div class="col-sm-5 col-md-4">
												<div class="list-group success square no-border">
													<?php foreach ($payments as $payment):?>
												  	<a href="<?php echo Url::toRoute(array('payment/list','payment_id'=>$payment->id));?>" class="list-group-item <?php if($payment->id == $model->id):?>active<?php endif;?>" style="font-weight:bolder;">
												  		<?php echo $payment->pay_name;?> 
												  		<?php if($payment->pay_status):?>
												  			<span class="badge badge-success">
												  				已启用
												  			</span>
												  		<?php else:?>
												  			<span class="badge badge-danger">
												  				已停用
												  			</span>
												  		<?php endif;?>
												  	</a>
													<?php endforeach;?>
												</div>
											</div>
											<div class="col-sm-7 col-md-8">
												<h2>
													支付方式--<strong><?php echo $model->pay_name;?></strong> 
												</h2>
												<div class="panel panel-transparent panel-square">
													<div id="pay-detail" class="panel-body">
														<div class="panel-heading"></div>
													  	<div class="panel-body">	
															<p><?php echo $model->pay_describe;?></p>
													 	 </div><!-- /.panel-body -->
													  	<div class="panel-footer">
															<ul class="attachment-list">
																<li><small>支付方式</small>&nbsp;&nbsp;--&nbsp;&nbsp;<a style="font-weight:bold;"><?php echo Payment2::$pay_type_list["{$model->pay_type}"];?></a></li>

																<li><small>支付状态</small>&nbsp;&nbsp;--&nbsp;&nbsp;<a style="font-weight:bold;"><?php if($model->pay_status):?>已启用<?php else:?>已停用<?php endif;?></a></li>

																<li><small>收款账户</small>&nbsp;&nbsp;--&nbsp;&nbsp;<a style="font-weight:bold;"><?php echo empty($model->pay_account) ? "未填写":$model->pay_account;?></a></li>

																<!--<li><small>添加时间</small>&nbsp;&nbsp;--&nbsp;&nbsp;<a style="font-weight:bold;"><?php /*echo date("Y-m-d H:i:s",$model->pay_addtime);*/?></a></li>-->
																
															</ul>
															<a href="<?php echo Url::toRoute(array('payment/modify','payment_id'=>$model->id));?>" class="btn btn-danger"><i class="fa fa-edit"></i>编辑</a>
															<?php if($model->pay_status):?>\
																<a href="<?php echo Url::toRoute(array('payment/status','payment_id'=>$model->id,'pay_status'=>'0'));?>" class="btn btn-danger"><i class="fa fa-gear"></i>停用</a>
															<?php else:?>
																<a href="<?php echo Url::toRoute(array('payment/status','payment_id'=>$model->id,'pay_status'=>'1'));?>" class="btn btn-danger"><i class="fa fa-gear"></i>启用</a>
															<?php endif;?>
													  	</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
			</div>
		</div>
	</div>
</div>

