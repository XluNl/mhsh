<?php


/* @var $this yii\web\View */
/* @var $goodsScheduleCollectionModel GoodsScheduleCollection */
/* @var $scheduleGoodsText  */

use common\models\Common;
use common\models\GoodsScheduleCollection;
use yii\bootstrap\Html;
use backend\assets\BootstrapTreeviewAsset;

BootstrapTreeviewAsset::register($this);
$title = "排期文本";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = ['label' => '商品排期列表', 'url' => ['/goods-schedule-collection/index','GoodsScheduleSearch[collection_id]'=>$goodsScheduleCollectionModel['id']]];
$this->params['breadcrumbs'][] = $title;
?>
<div class="container-fluid">
<div class="row">
	<div class="col-xs-8 col-xs-offset-2">
	<div class="box box-success box-solid">
	       <div class="box-header with-border">
               <?=$goodsScheduleCollectionModel['collection_name']?>--排期导出文本
           </div>
           <div class="box-body">
               <div class="form-group" id="copy-text">
                   <?php
                   $sort_1 = null;
                   foreach ($scheduleGoodsText as $v){
                       if ($v['sort_1']!==$sort_1){
                           $sort_1 = $v['sort_1'];
                           echo Html::tag("p","🌴----------{$v['sort_1_name']}".PHP_EOL);
                       }
                       echo Html::tag("p","🔥【{$v['goods_name']}{$v['sku_name']}】💰".Common::showAmountWithYuan($v['price'])."/份".PHP_EOL);
                   }
                   echo Html::tag("p","☎抢购时间：".PHP_EOL);
                   echo Html::tag("p","🚄到货时间：".PHP_EOL);
                   ?>
               </div>
               <div class="form-group">
                   <?= Html::a('返回', ['/goods-schedule/index','GoodsScheduleSearch[collection_id]'=>$goodsScheduleCollectionModel['id']], ['class' => 'col-xs-offset-3 col-xs-6 btn   btn-warning btn-lg']) ?>
               </div>
        </div>
    </div>
	</div>
</div>
	
</div>
<?php $this->beginBlock('js_end') ?>
    let clipboard = new Clipboard('#copy-btn', {
        text: function() {
            return $("#copy-text").html();
        }
    });
    clipboard.on('success', function(e) {
        alert("复制成功");
    });
    clipboard.on('error', function(e) {
        console.log(e);
    });

<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>