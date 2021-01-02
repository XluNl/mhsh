<?php 
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\GroupRoom;
use common\models\Common;
use common\models\Order;
$this->context->layout = 'sub';
?>

 <style type="text/css">
 .form-group{
 	margin-bottom: 0px;
 }
 </style>

<section class="content"></div>
	<div class="container-fluid">
		<div class="panel panel-default panel-no-border" style="margin-top: 20px;">
			<div class="the-box">
				<div class="row">
					<div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                      <h4>商品信息</h4>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback " style="display: flex;align-items: center;">
                    	<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>拼团编号:</label><label><?= $roomInfo['id'];?></label>
	                    </div>
	                    <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>团购状态:</label><label><?= GroupRoom::$groupRoomStatus[$roomInfo['status']];?></label>
	                    </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback " style="display: flex;align-items: center;">
                    	<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>开团时间:</label><label><?= $roomInfo['created_at'];?></label>
	                    </div>
	                    <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>结束时间:</label><label><?= $roomInfo['littleTime']['actualEndTime'];?></label>
	                    </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback " style="display: flex;align-items: center;">
                    	<div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>拼团人数:<?= $roomInfo['number'];?></label>
	                    </div>
	                    <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback ">
	                      <label>拼主昵称:</label><label><?= $teamInfo['user']['nickname'];?></label>
	                    </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                      <h4>商品信息</h4>
                      		<div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
		                 </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <table class="table table-bordered">
                          <thead>
                            <tr>
                              <th>商品ID</th>
                              <th>商品名称</th>
                              <th>商品价格(元)</th>
                              <th>商品库存</th>
                            </tr>
                          </thead>
                          <tbody id="skubody">
                              <tr>
                               <td><?= $goodsInfo['schedule']['goods_id']?></td>
                               <td><?= $goodsInfo['schedule']['goods']['goods_name']?></td>
                               <td><?= Common::showAmount($goodsInfo['schedule']['price'])?></td>
                               <td><?= $goodsInfo['schedule']['goodsSku']['sku_stock']?></td>
                              </tr>
                          </tbody>
                        </table>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                      <h4>参团信息</h4>
                      		<div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
		                 </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <table class="table table-bordered">
                          <thead>
                            <tr>
                              <th>用户昵称</th>
                              <th>用户手机号</th>
                              <th>参团时间</th>
                              <th>商品规格</th>
                              <th>购买数量</th>
                              <th>订单编号</th>
                              <th>订单状态</th>
                            </tr>
                          </thead>
                          <tbody id="skubody">
							<?php foreach ($orders as $key => $value): ?>
								<tr>
									<td><?= $value['customerInfo']['user']['nickname']?></td>
									<td><?= $value['customerInfo']['user']['phone']?></td>
									<td><?= $value['created_at']?></td>
									<td><?= $goodsInfo['schedule']['goodsSku']['sku_describe']?></td>
									<td><?= $value['order']['goods_num']?></td>
									<td><?= $value['order_no']?></td>
									<td><?= Order::$order_status_list[$value['order']['pay_status']]?></td>
								</tr>
							<?php endforeach ?>
                          </tbody>
                        </table>
                    </div>
			    </div>
			</div>
		</div>
	</div>
</div>