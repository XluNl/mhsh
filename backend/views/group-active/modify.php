<?php

use common\models\Common;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

// backend\assets\JFormValidateAsset::register($this);
// $this->context->layout = 'sub';
$this->title = '编辑活动信息';
$this->params['breadcrumbs'][] = ['label' => '活动列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style type="text/css">
    .form-group {
        margin-bottom: 0px;
    }
</style>
<div class="container-fluid">
    <div class="panel panel-default panel-no-border" style="margin-top: 20px;">
        <div class="the-box">
            <div class="row">
                <?php $form = ActiveForm::begin([
                    'id' => 'addGroupFrom',
                    'options' => ['class' => 'input_mask', 'autocomplete' => 'off'],
                ]) ?>
                <input type="hidden" id="collectionId" value="<?= $model['schedule']['collection_id']?>">
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                    <h4>归属信息</h4>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback "
                     style="display:flex;align-items: center;">
                    <?= $form->field($model, 'owner_type')->widget(Select2::classname(), [
                        'data' => GoodsConstantEnum::$ownerArr,
                        'options' => ['placeholder' => '选择归属 ...'],
                        'pluginOptions' => [
                        ],
                    ])->label(""); ?>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                    <h4>商品信息</h4>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback "
                     style="display:flex;align-items: center;">
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback" id="hideImg"
                         style="display:<?= StringUtils::isBlank($model['id']) ? 'none' : 'block'; ?>">
                        <img id="goods_img"
                             src="<?= Common::generateAbsoluteUrl(StringUtils::filterFirstNotBlank($model['schedule']['goodsSku']['sku_img'],$model['schedule']['goods']['goods_img'])); ?>"
                             width="100px" height="100px">
                    </div>
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                        <a class="btn btn-primary" id="add_goods" href="javascript:void(0);">添加商品</a>
                    </div>
                </div>
                <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback "
                     style="display: flex;align-items: center;">
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                        <label>商品名称:</label><label
                                id="goods_name"><?= $model['schedule']['goods']['goods_name']; ?></label>
                    </div>
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                        <label>商品ID:</label><label id="goods_id"><?= $model['schedule']['goods']['id']; ?></label>
                        <?php echo $form->field($model, 'schedule_id')->hiddenInput(['class' => 'form-control has-feedback-left'])->label(false); ?>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback "
                     style="display: flex;align-items: center;">
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <label>商品描述:</label><label
                                id="goods_describe"><?= $model['schedule']['goods']['goods_describe'] ?? '' ?></label>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                    <h4>拼团规则</h4>
                    <?php echo $form->field($model, 'rule')->hiddenInput(['class' => 'form-control has-feedback-left'])->label(false); ?>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>商品规格</th>
                                <th>商品库存</th>
                                <th>人数</th>
                                <th>排期价格<button class="btn btn-primary btn-xs" onclick="jumpSchedule();return false;">修改</button></th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                            </tr>
                            </thead>
                            <tbody id="skubody">
                            <tr>
                                <td>
                                    <!--<input disabled type="checkbox" name="" checked class="" style="width: 20px;">-->
                                    <label id="sku_name"><?= $model['schedule']['goodsSku']['sku_name'] ?></label>
                                </td>
                                <td>
                                    <label id="stock"><?= $model['schedule']['schedule_stock'] ?></label>
                                </td>
                                <td>1</td>
                                <td>
                                    <input id="price" type="number" class="form-control" disabled
                                           value="<?= Common::showAmount($model['schedule']['price']); ?>">
                                </td>
                                <td>2</td>
                                <td><input type="number" step="0.01" min="0.01" class="form-control"
                                           name="GroupActive[rule_desc][2]"
                                           value="<?= $model->rule_desc['price2'] ?? ''; ?>"></td>
                                <td>3</td>
                                <td><input type="number" step="0.01" min="0.01" class="form-control"
                                           name="GroupActive[rule_desc][3]"
                                           value="<?= $model->rule_desc['price3'] ?? ''; ?>"></td>
                                <td>4</td>
                                <td><input type="number" step="0.01" min="0.01" class="form-control"
                                           name="GroupActive[rule_desc][4]"
                                           value="<?= $model->rule_desc['price4'] ?? ''; ?>"></td>
                                <td>5</td>
                                <td><input type="number" step="0.01" min="0.01" class="form-control"
                                           name="GroupActive[rule_desc][5]"
                                           value="<?= $model->rule_desc['price5'] ?? ''; ?>"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                    <h4>拼团时间</h4>
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback " style="display: flex;">
                        <?php echo $form->field($model, 'continued')->textInput(['class' => 'form-control has-feedback-left'])->label(false); ?>
                        <label style="line-height: 35px;margin-left: 15px;">分钟</label>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                    <h4>活动时间段<button class="btn btn-primary btn-xs" onclick="jumpSchedule();return false;">修改</button></h4>
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                        <input type="text" disabled id="online_time" value="<?= $model['schedule']['online_time'] ?>"
                               class="form-control">
                    </div>
                    <div class="col-md-1 col-sm-1 col-xs-12 form-group has-feedback "
                         style="display: flex;align-items: center;justify-content: center;padding: 0px;width: 30px;line-height:35px;">
                        <label>至</label></div>
                    <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                        <input type="text" disabled id="offline_time" value="<?= $model['schedule']['offline_time'] ?>"
                               class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12"
                         style="display:flex;justify-content:flex-end;margin-top: 0px;">
                        <input type="hidden" name="active_no" value="<?= $model['active_no'] ?? ''; ?>"/>
                        <button type="submit" class="btn btn btn-primary">立即保存</button>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#icancel').click(function () {
        parent.layer.close(parent.layer.getFrameIndex(window.name));
    });

    $('#add_goods').on('click', function () {
        let type = $(this).attr('value');
        layer.open({
            type: 2,
            area: ['70%', '80%'],
            fixed: false,
            title: '添加',
            maxmin: true,
            content: "<?= Url::toRoute(['/goods-schedule/schedule-goods-select-modal',
                'GoodsScheduleSearch[schedule_status]' => GoodsConstantEnum::STATUS_UP,
                'GoodsScheduleSearch[goods_status]' => GoodsConstantEnum::STATUS_UP,
                'GoodsScheduleSearch[sku_status]' => GoodsConstantEnum::STATUS_UP,
                'GoodsScheduleSearch[schedule_display_channel]' => GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_GROUP,
            ]);?>"+"&GoodsScheduleSearch[owner_type]="+$("#groupactive-owner_type").select2("val")
        });
    });

    $("tbody").on('click', ':checkbox', function () {
        console.log($(this).val());
        $("#groupactive-rules").val($(this).val());
    });

    function say(goods_info) {
        let layIndex = layer.load(1);
        let schedule_id = goods_info.id;
        $.get("/goods-schedule/detail", {
            'schedule_id': schedule_id,
        }, function (response, status, jqxhr) {
            if (response.status) {
                let data = response.data;
                $("#hideImg").css('display', 'block');
                $("#goods_img").removeAttr('src').attr('src', goods_info.img);
                $("#goods_id").empty().html(goods_info.goods_id);
                $("#goods_describe").empty().html(goods_info.desc);
                $("#goods_name").empty().html(goods_info.name);
                $("#sku_name").empty().html(goods_info.sku_name);

                $("#groupactive-schedule_id").val('').val(data.id);
                $("#collectionId").val('').val(data.collection_id);
                $("#online_time").val('').val(data.online_time);
                $("#price").val('').val(data.price/1000.0);
                $("#offline_time").val('').val(data.offline_time);
                $("#schedule_stock").empty().html(data.schedule_stock);
            } else {
                layer.msg(response.error, {icon: 2});
            }
            layer.close(layIndex);
        }, 'json').fail(function () {
            layer.alert("网络错误", {icon: 2});
            layer.close(layIndex);
        });
        /*$.get("/goods-schedule/detail?schedule_id="+schedule_id,function(response,status){
            debugger;
            if (status!=='success'){
                layer.msg("网络错误", {icon: 2});
            }
            if(response.success){
                let data = response.data;
                $("#hideImg").css('display','block');
                $("#goods_img").removeAttr('src').attr('src',goods_info.img);
                $("#goods_id").empty().html(goods_info.goods_id);
                $("#goods_describe").empty().html(goods_info.desc);
                $("#goods_name").empty().html(goods_info.name);

                $("#groupactive-schedule_id").val('').val(data.id);
                $("#online_time").val('').val(data.online_time);
                $("#offline_time").val('').val(data.offline_time);
                $("#schedule_stock").empty().html(data.schedule_stock);
            }else{
                layer.msg(response.error, {icon: 2});
            }
            layer.close(layIndex);
        });*/
    }
    function jumpSchedule() {
        let scheduleId =  $("#groupactive-schedule_id").val();
        let collectionId =  $("#collectionId").val();
        if (scheduleId===''||collectionId===''){
            layer.msg("请先选择商品");
            return;
        }
        window.open('<?=Url::to(['goods-schedule/modify'])?>'+'?schedule_id='+scheduleId+'&collection_id='+collectionId);

    }
</script>