<?php
 use yii\helpers\Url;
?>
<div class="panel-heading">
            <ul class="nav nav-pills nav-danger">
                <li <?php if ($orderFilter == 'common'):?>class="active" <?php endif;?>>
                    <a href="<?=Url::to(['order/list', 'order_filter' => 'common']);?>" style="padding: 5px 10px;">
                        订单列表（不含补货）
                    </a>
                </li>
                <li <?php if ($orderFilter == 'replenishment'):?>class="active" <?php endif;?>>
                    <a href="<?=Url::to(['order/list', 'order_filter' => 'replenishment']);?>" style="padding: 5px 10px;">
                        订单列表（补货订单）
                    </a>
                </li>
                <li <?php if ($orderFilter == 'invalid'):?>class="active" <?php endif;?>>
                    <a href="<?=Url::to(['order/list', 'order_filter' => 'invalid']);?>" style="padding: 5px 10px;">
                        订单列表（无效订单）
                    </a>
                </li>
                <li <?php if ($orderFilter == 'paid'):?>class="active" <?php endif;?>>
                    <a href="<?=Url::to(['order/list', 'order_filter' => 'paid']);?>" style="padding: 5px 10px;">
                        订单列表（已支付订单）
                    </a>
                </li>
                <li <?php if ($orderFilter == 'unpaid'):?>class="active" <?php endif;?>>
                    <a href="<?=Url::to(['order/list', 'order_filter' => 'unpaid']);?>" style="padding: 5px 10px;">
                        订单列表（未支付订单）
                    </a>
                </li>
            </ul>
</div>