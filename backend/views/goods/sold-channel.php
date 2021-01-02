<?php


/* @var $this yii\web\View */
/* @var $model backend\models\forms\GoodsSoldChannelForm
 * @var $deliveryNames array
 * @var $goodsModel Goods
 */

use common\models\Goods;
use common\models\GoodsConstantEnum;
use kartik\widgets\Select2;
use yii\bootstrap\Html;
use backend\assets\BootstrapTreeviewAsset;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yiichina\icheck\ICheck;

BootstrapTreeviewAsset::register($this);
$title = "修改投放渠道";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = ['label' => '商品列表', 'url' => ['/goods/index']];
$this->params['breadcrumbs'][] = $title;
?>
<div class="container-fluid">
<div class="row">
	<div class="col-xs-6 col-xs-offset-3">
	<div class="box box-success box-solid">
	       <div class="box-header with-border">
               修改<?= $goodsModel['goods_name']?>的售卖渠道
           </div>
           <div class="box-body">
    		<?php $form = ActiveForm::begin([
                'options'=>['enctype'=>'multipart/form-data',
                'type'=>ActiveForm::TYPE_VERTICAL],
            ]); ?>
               <?php echo FormGrid::widget([
                   'model'=>$model,
                   'form'=>$form,
                   'autoGenerateColumns'=>true,
                   'rowOptions'=>['class'=>'col-md-offset-1'],
                   'rows'=>[
                       [
                           'columns'=>12,
                           'autoGenerateColumns'=>false, // override columns setting
                           'attributes'=>[       // 3 column layout
                               'sold_channel_type'=>[   // radio list
                                   'columnOptions'=>['colspan'=>12],
                                   'type'=>Form::INPUT_WIDGET,
                                   'widgetClass'=>'\yiichina\icheck\ICheck',
                                   'options'=>[
                                       'type' => ICheck::TYPE_RADIO_LIST,
                                       'skin' => ICheck::SKIN_SQUARE,
                                       'color' => ICheck::COLOR_GREEN,
                                       'clientOptions'=>[
                                           'labelHover'=>false,
                                           'cursor'=>true,
                                       ],
                                       'options'=>[
                                           'class'=>'label-group',
                                           'separator'=>'',
                                           'template'=>'<span class="check">{input}{label}</span>',
                                           'labelOptions'=>['style'=>'display:inline']
                                       ],
                                       'model' => $model,
                                       'items' => Goods::$goodsSoldChannelTypeArr,
                                   ]
                               ],
                           ]
                       ],
                       [
                           'columns'=>12,
                           'autoGenerateColumns'=>false, // override columns setting
                           'attributes'=>[       // 3 column layout
                               'sold_channel_ids'=>[   // radio list
                                   'columnOptions'=>['colspan'=>12],
                                   'type'=>Form::INPUT_WIDGET,
                                   'widgetClass'=>'\kartik\widgets\Select2',
                                   'options'=>[
                                       'data' => $deliveryNames,

                                       'model' => $model,
                                       'language' => 'zh-CN',
                                       'size' => Select2::SMALL,
                                       // 'options' => ['placeholder' => 'Select a state ...'],
                                       'pluginOptions' => [
                                           'allowClear' => true,
                                           'multiple' => true,
                                       ],
                                   ]
                               ],
                           ]
                       ],
                   ]
               ]); ?>
        	    <div class="form-group">
        	        <?= Html::submitButton('保存', ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-primary btn-lg ']) ?>
            	    <?= Html::a('返回', ['/goods/index', 'GoodsSearch[sort_1]' => $goodsModel->sort_1, 'GoodsSearch[sort_2]' => $goodsModel->sort_2, 'GoodsSearch[goods_name]' => $goodsModel->goods_name,'GoodsSearch[goods_owner]' => $goodsModel->goods_owner], ['class' => 'col-xs-offset-2 col-xs-3 btn   btn-warning btn-lg']) ?>
        	    </div>
        	<?php ActiveForm::end(); ?>
        </div>
    </div>
	</div>
</div>
	
</div>
<?php $this->beginBlock('js_end_1') ?>
$("#goodssoldchannelform-sold_channel_type").on('ifChanged', function(event){
    let channelType = $("input[name='GoodsSoldChannelForm[sold_channel_type]']:checked").val();
    console.log(channelType);
    if(channelType==<?= Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT?>){
        $(".field-goodssoldchannelform-sold_channel_ids").hide();
    }
    else if(channelType==<?= Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY?>){
        $(".field-goodssoldchannelform-sold_channel_ids").show();
    }
});
if(<?= $model->sold_channel_type== Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT?"true":"false"?>){
    $(".field-goodssoldchannelform-sold_channel_ids").hide();
}
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end_1'], \yii\web\View::POS_READY); ?>