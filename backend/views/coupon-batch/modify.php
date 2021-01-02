<?php

use backend\models\BackendCommon;
use common\models\Coupon;
use common\models\CouponBatch;
use common\models\GoodsConstantEnum;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yiichina\icheck\ICheck;

// $this->context->layout = 'sub';
/* @var  array $sortArr */
/* @var array $goodsArr */
/* @var array $skusArr */
/* @var array $ownerTypeOptions */
/* @var common\models\CouponBatch $model */
$this->title = empty($model->id)?'添加优惠券活动信息':'修改优惠券活动信息';
$this->params['breadcrumbs'][] = ['label' => '优惠券活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading"><?= $this->title;?></h3>
                    </div>
                    <div class="box-body">
                        <?php $form = ActiveForm::begin();
                        echo FormGrid::widget([
                            'model'=>$model,
                            'form'=>$form,
                            'autoGenerateColumns'=>true,
                            //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                            'rows'=>[
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>设定优惠券类型</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'coupon_type' =>[
                                            'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' => BackendCommon::addBlankOption(CouponBatch::$couponType),
                                            'placeholder' => '选择类型类型...',
                                            'columnOptions' => ['colspan' => 4],
                                            'options'=>[
                                                'selected'=>'selected',
                                                'data'=>2,
                                                'onchange'=>"
                                                    let index = $(this).val();
                                                    if(index==2){
                                                        $('.field-couponbatch-user_time_type').parent().css('display','inline');
                                                        $('#couponbatch-use_limit_type').val('".Coupon::LIMIT_TYPE_OWNER."').attr('readonly','readonly');
                                                        $('#select2-couponbatch-use_limit_type-container').text('".Coupon::$limitTypeArr[Coupon::LIMIT_TYPE_OWNER]."');
                                                        $('.field-couponbatch-goods_owner').parent().show();
                                                        $('.field-couponbatch-big_sort').parent().hide();
                                                        $('.field-couponbatch-goods_id').parent().hide();
                                                        $('.field-couponbatch-sku_id').parent().hide();
                                                        $('#couponbatch-goods_owner').trigger('select2:select');
                                                    } 
                                                ",
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>设定归属信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'owner_type' => [
                                            'type' => Form::INPUT_DROPDOWN_LIST,
                                            'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr),
                                            'placeholder' => '选择类型...',
                                            'columnOptions' => ['colspan' => 2],
                                            'options'=>[
                                                'style'=>'display:inline',
                                                'onchange'=>'
                                                $.get("/owner-type/options?owner_type="+$(this).val(),function(data){             
                                                    $("#couponbatch-owner_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                });'
                                            ]
                                        ],
                                        'owner_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>4],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => BackendCommon::addBlankOption($ownerTypeOptions),
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'theme'=> \kartik\select2\Select2::THEME_BOOTSTRAP,
                                                'options' => ['placeholder' => '选择类型...'],
                                                'pluginOptions' => [
                                                    'allowClear' => true
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>设定限制信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'use_limit_type'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => Coupon::$limitTypeArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "select2:select" => "function() { 
                                                        if($(this).val()==".Coupon::LIMIT_TYPE_ALL."){
                                                            $('.field-couponbatch-goods_owner').parent().hide();
                                                            $('.field-couponbatch-big_sort').parent().hide();
                                                            $('.field-couponbatch-goods_id').parent().hide();
                                                            $('.field-couponbatch-sku_id').parent().hide();
                                                            $('#couponbatch-goods_owner').trigger('select2:select');
                                                        }
                                                        else if($(this).val()==".Coupon::LIMIT_TYPE_OWNER."){
                                                            $('.field-couponbatch-goods_owner').parent().show();
                                                            $('.field-couponbatch-big_sort').parent().hide();
                                                            $('.field-couponbatch-goods_id').parent().hide();
                                                            $('.field-couponbatch-sku_id').parent().hide();
                                                            $('#couponbatch-goods_owner').trigger('select2:select');
                                                        }
                                                        else if($(this).val()==".Coupon::LIMIT_TYPE_SORT."){
                                                            $('.field-couponbatch-goods_owner').parent().show();
                                                            $('.field-couponbatch-big_sort').parent().show();
                                                            $('.field-couponbatch-goods_id').parent().hide();
                                                            $('.field-couponbatch-sku_id').parent().hide();
                                                            $('#couponbatch-goods_owner').trigger('select2:select');
                                                        }
                                                        else if($(this).val()==".Coupon::LIMIT_TYPE_GOODS_SKU."){
                                                            $('.field-couponbatch-goods_owner').parent().show();
                                                            $('.field-couponbatch-big_sort').parent().show();
                                                            $('.field-couponbatch-goods_id').parent().show();
                                                            $('.field-couponbatch-sku_id').parent().show();
                                                            $('#couponbatch-goods_owner').trigger('select2:select');
                                                        }
                                                     }",
                                                ],
                                            ]
                                        ],
                                        // 'use_limit_type_hidden'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动总数量...','hidden' => 'hidden'],'columnOptions'=>['colspan'=>2]],
                                        'goods_owner'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => GoodsConstantEnum::$ownerArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                'options' => ['hidden' => 'hidden'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "select2:select" => 'function() { 
                                                       $.get("/goods-sort/select-options?sort_owner="+$(this).val(),function(data){
                                                            $("#couponbatch-big_sort").html("<option value=>请选择</option>").append(data).trigger("select2:select");             
                                                            $("#couponbatch-goods_id").html("<option value=>请选择</option>").trigger("select2:select");
                                                            $("#couponbatch-sku_id").html("<option value=>请选择</option>").trigger("select2:select");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'big_sort'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $sortArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "select2:select" => 'function() { 
                                                       $.get("/goods/goods-options-by-big-sort?big_sort="+$(this).val(),function(data){
                                                            $("#couponbatch-goods_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                            $("#couponbatch-sku_id").html("<option value=>请选择</option>").trigger("select2:select");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'goods_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $goodsArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "select2:select" => 'function() { 
                                                       $.get("/goods-sku/goods-sku-options?goods_id="+$(this).val(),function(data){             
                                                            $("#couponbatch-sku_id").html("<option value=>请选择</option>").append(data).trigger("select2:select");
                                                       });
                                                     }',
                                                ],
                                            ]
                                        ],
                                        'sku_id'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => $skusArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动名称...'],'columnOptions'=>['colspan'=>6]],
                                        'coupon_name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入优惠券显示名称...'],'columnOptions'=>['colspan'=>6]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Coupon::$typeDisplayArr, 'placeholder' => '选择优惠券类型...', 'columnOptions' => ['colspan' => 2]],
                                        'startup'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入满金额...'],'columnOptions'=>['colspan'=>2]],
                                        'discount'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入减金额...'],'columnOptions'=>['colspan'=>2]],
                                        'restore' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Coupon::$restoreArr, 'placeholder' => '选择可否恢复...', 'columnOptions' => ['colspan' => 2]],
                                        'amount'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入活动总数量...'],'columnOptions'=>['colspan'=>2]],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写领取限制</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'draw_start_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择领取开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'draw_end_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择领取结束时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写用券时间</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'user_time_type' => [
                                            'type'=>Form::INPUT_RADIO_LIST,
                                            'items'=>CouponBatch::$userTimeType,
                                            'columnOptions' => ['colspan' => 2],
                                            'field' => 'user_time_type'
                                        ],
                                        'user_time_type_stat'=>[
                                            'columnOptions'=>['colspan'=>5],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择领取开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'user_time_type_end'=>[
                                            'columnOptions'=>['colspan'=>5],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择领取结束时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:59',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'user_time_days'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'N日...','id'=>'user_time_days','display'=>'none'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'draw_limit_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>CouponBatch::$drawTypeLimitArr, 'placeholder' => '选择领取优惠券限制类型...', 'columnOptions' => ['colspan' => 6]],
                                        'draw_limit_type_params'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入领取优惠券限制参数...'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'is_public' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>CouponBatch::$isPublicArr, 'placeholder' => '选择领取优惠券限制类型...', 'columnOptions' => ['colspan' => 6]],
                                        'draw_customer_type'=>[   // radio list
                                            'columnOptions'=>['colspan'=>2],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\widgets\Select2',
                                            'options'=>[
                                                'data' => CouponBatch::$drawCustomerTypeArr,
                                                'model' => $model,
                                                'language' => 'zh-CN',
                                                'size' => Select2::SMALL,
                                                // 'options' => ['placeholder' => 'Select a state ...'],
                                                'pluginOptions' => [
                                                    'allowClear' => false,
                                                ],
                                                'pluginEvents' => [
                                                    "change" => "function() { 
                                                        if($(this).val()==".CouponBatch::DRAW_CUSTOMER_TYPE_ALL."){
                                                            $('.field-couponbatch-draw_customer_phones').hide();
                                                        }
                                                        else if($(this).val()==".CouponBatch::DRAW_CUSTOMER_TYPE_WHITE."){
                                                             $('.field-couponbatch-draw_customer_phones').show();
                                                        }
                                                        else if($(this).val()==".CouponBatch::DRAW_CUSTOMER_TYPE_BLACK."){
                                                            $('.field-couponbatch-draw_customer_phones').show();
                                                        }
                                                     }",
                                                ],
                                            ]
                                        ],
                                        'is_pop' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>CouponBatch::$isPopArr, 'placeholder' => '选择是否首页弹窗...', 'columnOptions' => ['colspan' => 3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'draw_customer_phones'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入客户限制手机号名单，英文逗号分隔...'],'columnOptions'=>['colspan'=>12]],
                                    ]
                                ],
                                [
                                    'contentBefore'=>'<legend class="text-info"><small>填写其他信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'remark'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入商品属性描述信息...'],'columnOptions'=>['colspan'=>12]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-3 col-xs-2 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-2 btn   btn-warning btn-lg','id'=>'icancel']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->beginBlock('js_end_1') ?>
    $("#couponbatch-use_limit_type").trigger("change");
    $("#couponbatch-draw_customer_type").trigger("change");
    if($("#couponbatch-use_limit_type").val()=="<?=Coupon::LIMIT_TYPE_ALL?>"){
    $('.field-couponbatch-goods_owner').parent().hide();
    $('.field-couponbatch-big_sort').parent().hide();
    $('.field-couponbatch-goods_id').parent().hide();
    $('.field-couponbatch-sku_id').parent().hide();
    }
    else if($("#couponbatch-use_limit_type").val()=="<?=Coupon::LIMIT_TYPE_OWNER?>"){
    $('.field-couponbatch-goods_owner').parent().show();
    $('.field-couponbatch-big_sort').parent().hide();
    $('.field-couponbatch-goods_id').parent().hide();
    $('.field-couponbatch-sku_id').parent().hide();
    }
    else if($("#couponbatch-use_limit_type").val()=="<?=Coupon::LIMIT_TYPE_SORT?>"){
    $('.field-couponbatch-goods_owner').parent().show();
    $('.field-couponbatch-big_sort').parent().show();
    $('.field-couponbatch-goods_id').parent().hide();
    $('.field-couponbatch-sku_id').parent().hide();
    }
    else if($("#couponbatch-use_limit_type").val()=="<?=Coupon::LIMIT_TYPE_GOODS_SKU?>"){
    $('.field-couponbatch-goods_owner').parent().show();
    $('.field-couponbatch-big_sort').parent().show();
    $('.field-couponbatch-goods_id').parent().show();
    $('.field-couponbatch-sku_id').parent().show();
    }
    $('#icancel').click(function(){
        parent.layer.close(parent.layer.getFrameIndex(window.name));
    });
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end_1'], \yii\web\View::POS_READY); ?>
<script type="text/javascript">
// 初始新人优惠自定义用券时间item
function initUserTimeFeature(){
    $('.field-user_time_days').parent().css('display','none');
    // $("input[type=radio]:checked").attr('checked',false);
    $('.field-couponbatch-user_time_type_stat').parent().css('display','none');
    $('.field-couponbatch-user_time_type_end').parent().css('display','none');
}

// 编辑时 初始化选择
+function initItem(){
    initUserTimeFeature();
    var conponType = "<?= $model->coupon_type;?>";
    var user_time_type = '<?= $model->user_time_type;?>';
    if(user_time_type==1){
        $('.field-user_time_days').parent().css('display','none');
        $('.field-couponbatch-user_time_type_stat').parent().css('display','inline');
        $('.field-couponbatch-user_time_type_end').parent().css('display','inline');
    }
    if(user_time_type > 1){
        $('.field-user_time_days').parent().css('display','inline');
        $('.field-couponbatch-user_time_type_stat').parent().css('display','none');
        $('.field-couponbatch-user_time_type_end').parent().css('display','none');
    }

    
    if(conponType==2 && user_time_type!=''){
        $(":radio[name='CouponBatch[user_time_type]'][value='" + user_time_type + "']").prop("checked", "checked");
    }
}();
$(":radio").click(function(){
 var index = $(this).val()
 if(index ==1){
    $('.field-user_time_days').parent().css('display','none');
    $('.field-couponbatch-user_time_type_stat').parent().css('display','inline');
    $('.field-couponbatch-user_time_type_end').parent().css('display','inline');
 }
 if(index > 1){
    $('.field-user_time_days').parent().css('display','inline');
    $('.field-couponbatch-user_time_type_stat').parent().css('display','none');
    $('.field-couponbatch-user_time_type_end').parent().css('display','none');
 }
});
</script>
