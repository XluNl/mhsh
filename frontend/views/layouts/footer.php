<?php

use yii\helpers\Url;

$controller = Yii::$app->controller->id;
$action = Yii::$app->controller->action->id;
?>
<!--底部导航菜单-->
<footer>
	<ul>
		<li class="home <?php if($controller=="site"&&$action=="mall"):?> on<?php endif;?> "><a href="<?= Url::to("/site/mall")?>"> <i class="icon"></i>
				<h3 class="text">首页</h3>
		</a></li>
		<li  class="shoping_cart <?php if($controller=="order"&&$action=="cart"):?>on<?php endif;?> shopping-cart"><a
			href="<?= Url::to("/restaurant/order/cart")?>"> <i class="icon"></i>
				<h3 class="text">购物车</h3>
				<div class="goods_number <?php if(!empty($cart['goods_total'])&&$cart['goods_total']>0):?>on<?php endif;?>">
					<span><?php echo !empty($cart['goods_total'])&&$cart['goods_total']>0?$cart['goods_total']:0?></span>
				</div> <!--当购物车有商品时用on开启-->
		</a></li>
		<li   class="order_management <?php if($controller=="order"&&$action=="index"):?>on<?php endif;?>"><a
			href="<?= Url::to("/restaurant/order/index")?>"> <i class="icon"></i>
				<h3 class="text">订单管理</h3>
		</a></li>
		<li class="personal_center <?php if($controller=="user"&&$action=="index"):?>on<?php endif;?>"><a
			href="<?= Url::to("/restaurant/user/index")?>"> <i class="icon"></i>
				<h3 class="text">个人中心</h3>
		</a></li>
	</ul>
</footer>