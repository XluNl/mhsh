<?php

use backend\models\BackendCommon;
use common\models\Alliance;
use common\models\Banner;
use common\models\Delivery;
use common\models\GoodsConstantEnum;
use common\utils\StringUtils;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\Url;

/* @var common\models\Banner $model */
$this->title = 'Banner信息保存';
$this->params['breadcrumbs'][] = ['label' => 'Banner列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success box-solid">
                    <div class="box-header with-border">
                        <h3 class="page-heading">异业联盟商户信息保存</h3>
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
                                    'contentBefore'=>'<legend class="text-info"><small>填写基本信息</small></legend>',
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Banner::$typeArr, 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 3]],
                                        'sub_type' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' =>Banner::$subTypeArr, 'placeholder' => '选择类型...', 'columnOptions' => ['colspan' => 3]],
                                        'name'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入名称...'],'columnOptions'=>['colspan'=>3]],
                                        'display_order'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入排序...'],'columnOptions'=>['colspan'=>3]],
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'online_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示开始时间','readonly'=>true],
                                                'convertFormat' => true,
                                                'pluginOptions' => [
                                                    'format' => 'yyyy-MM-dd HH:mm:00',
                                                    'todayHighlight' => true,
                                                    'autoclose'=>true,
                                                ]
                                            ]
                                        ],
                                        'offline_time'=>[
                                            'columnOptions'=>['colspan'=>6],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\kartik\datetime\DateTimePicker',
                                            'options'=>[
                                                'model' => $model,
                                                'options' => ['placeholder' => '选择展示结束时间','readonly'=>true],
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
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'images'=>[   // radio list
                                            'columnOptions'=>['colspan'=>3],
                                            'type'=>Form::INPUT_WIDGET,
                                            'widgetClass'=>'\manks\FileInput',
                                            'options'=>[
                                                'clientOptions' => [
                                                    'pick' => [
                                                        'multiple' => false,
                                                    ],
                                                ],
                                            ],
                                        ]
                                    ]
                                ],
                                [
                                    'columns'=>12,
                                    'autoGenerateColumns'=>false, // override columns setting
                                    'attributes'=>[       // 3 column layout
                                        'messages'=>['type'=>Form::INPUT_TEXTAREA, 'options'=>['placeholder'=>'输入额外信息...'],'columnOptions'=>['colspan'=>12]],
                                    ]
                                ],
                            ]
                        ]);
                        ?>
                        <div class="form-group highlight-addon field-banner-url-skip" id="urlskip">
                            <label class="control-label has-star" for="banner-detail">URL跳转链家</label><span style="font-size:9px;color:red;">&nbsp;&nbsp;<*注意小程序跳转链接需授权></span>
                            <input type="text" class="form-control" value="<?php if($model->sub_type==Banner::SUB_TYPE_URL_JUMP) echo $model['link_info'];?>" name="Banner[url_skip]">
                        </div>
                        <div class="form-group highlight-addon field-banner-detail" id="schedule_one">
                            <label class="control-label has-star" for="banner-detail">添加一个排期</label>
                            <?= Html::a('添加','javascript:void(0);',['id'=>'add_schedule','class'=>'btn btn-primary add_schedule']) ?>
                            <table class="table table-bordered">
                              <thead>
                                <tr>
                                  <th>排期名称</th>
                                  <th>排期ID</th>
                                  <th>商品名</th>
                                  <th>属性名</th>
                                  <th>图片</th>
                                  <th>操作</th>
                                </tr>
                              </thead>
                              <tbody id="schedule_one_body">
                                <?php if ($model->sub_type==Banner::SUB_TYPE_SCHEDULE_DETAIL && $model['link_info_restore']): ?>
                                    <tr>
                                      <td>
                                        <?= $model['link_info_restore']['schedule_name'];?>
                                        <input type="hidden" name="Banner[schedule_one]" value="<?= $model['link_info_restore']['id'];?>">
                                      </td>
                                      <td><?= $model['link_info_restore']['id'];?></td>
                                      <td><?= $model['link_info_restore']['goods']['goods_name'];?></td>
                                      <td><?= $model['link_info_restore']['goodsSku']['sku_name'];?></td>
                                      <td><img src="<?= $model['link_info_restore']['goods']['goods_img'];?>" width="50" height="50" alt=""></td>
                                      <td>
                                        <?= Html::a('删除','javascript:void(0);', ['value'=>$model['link_info_restore']['id'],'class'=>'btn btn-danger btn-xs schedule_del']);?>
                                      </td>
                                    </tr>
                                <?php endif ?>
                              </tbody>
                            </table>
                        </div>
                        <div class="form-group highlight-addon field-banner-detail-list" id="schedule_list">
                            <label class="control-label has-star" for="banner-detail-list">添加多个排期</label>
                            <?= Html::a('添加','javascript:void(0);',['id'=>'add_schedule_list','class'=>'btn btn-primary add_schedule']) ?>
                            <table class="table table-bordered">
                              <thead>
                                <tr>
                                  <th>排期名称</th>
                                  <th>排期ID</th>
                                  <th>商品名</th>
                                  <th>属性名</th>
                                  <th>图片</th>
                                  <th>操作</th>
                                </tr>
                              </thead>
                              <tbody id="schedule_list_body">
                                <?php if ($model->sub_type==Banner::SUB_TYPE_SCHEDULE_LIST && $model['link_info_restore']): ?>
                                    <?php foreach ($model['link_info_restore'] as $key => $value): ?>
                                        <tr>
                                          <td>
                                            <?= $value['schedule_name'];?>
                                            <input type="hidden" name="Banner[schedule_mut][]" value="<?= $value['id'];?>">
                                          </td>
                                          <td><?= $value['id'];?></td>
                                          <td><?= $value['goods']['goods_name'];?></td>
                                          <td><?= $value['goodsSku']['sku_name'];?></td>
                                          <td><img src="<?= $value['goods']['goods_img'];?>" width="50" height="50" alt=""></td>
                                          <td>
                                            <?= Html::a('删除','javascript:void(0);', ['value'=>$value['id'],'class'=>'btn btn-danger btn-xs schedule_del']);?>
                                          </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
                              </tbody>
                            </table>
                        </div>
                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['data-loading-text'=>'提交中，请稍后','class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
                            <?= Html::a('返回', ['index'], ['class' => 'col-xs-offset-2 col-xs-4 btn   btn-warning btn-lg']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    <?php $this->beginBlock('js_end') ?>
    laydate.render({elem: '#alliance-business_start', type: 'time'});
    laydate.render({elem: '#alliance-business_end', type: 'time'});

    $('.add_schedule').on('click', function () {
        var type = $(this).attr('value');
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
            ]);?>" + '&GoodsScheduleSearch[owner_type]=' + $("#banner-type").val()
        });
    });

    function initElement() {
        $("#urlskip").css('display', 'none');
        $("#schedule_one").css('display', 'none');
        $("#schedule_list").css('display', 'none');
    }

    function initSubType() {
        window.sub_type = <?= $model->sub_type;?>;
        window.schedule_mut = [];
        <?php if ($model['sub_type']==Banner::SUB_TYPE_SCHEDULE_LIST): ?>
        window.schedule_mut = <?= StringUtils::isNotEmpty($model['link_info']) ? $model['link_info']:json_encode([]);?>;
        <?php endif;?>
        switchElem(window.sub_type);
    }

    function switchElem(index) {
        initElement();
        if (index == <?= Banner::SUB_TYPE_DEFAULT;?>) {

        } else if (index == <?= Banner::SUB_TYPE_URL_JUMP;?>) {
            $("#urlskip").css('display', 'block');
        } else if (index == <?= Banner::SUB_TYPE_SCHEDULE_DETAIL;?>) {
            $("#schedule_one").css('display', 'block');
        } else if (index == <?= Banner::SUB_TYPE_SCHEDULE_LIST;?>) {
            $("#schedule_list").css('display', 'block');
        }
    }

    $("#banner-sub_type").on('change', function (e) {
        let index = $(this).val();
        window.sub_type = index;
        switchElem(index);
    });

    function say(goods_info) {
        let schedule_id = goods_info.id;
        if (window.sub_type == <?= Banner::SUB_TYPE_SCHEDULE_LIST;?>) {
            if (window.schedule_mut && window.schedule_mut.indexOf(schedule_id.toString()) > -1) {
                layer.msg(`排期-${schedule_id}-已存在!`, {icon: 2});
                return;
            }
        }
        let elem = "#schedule_one_body";
        let query_schedule = 'Banner[schedule_one]';
        if (window.sub_type == <?= Banner::SUB_TYPE_SCHEDULE_LIST;?>) {
            window.schedule_mut.push(schedule_id.toString());
            elem = "#schedule_list_body";
            query_schedule = 'Banner[schedule_mut][]';
        }
        let html = `<tr>
                <td>
                  ${goods_info.schedule_name}
                  <input type="hidden" name="${query_schedule}" value="${schedule_id}">
                </td>
                <td>${schedule_id}</td>
                <td>${goods_info.name}</td>
                <td>${goods_info.sku_name}</td>
                <td><img src="${goods_info.img}" width="50" height="50" alt=""></td>
                <td><a class="btn btn-danger btn-xs schedule_del" value="${goods_info.id}" href="javascript:void(0);">删除</a></td>
              </tr>`;
        // 单个排期只允许添加一个
        if (window.sub_type == <?= Banner::SUB_TYPE_SCHEDULE_DETAIL;?>) {
            $(elem).empty().append(html);
        } else {
            $(elem).append(html);
        }
    }

    function removeElem(elem) {
        let item = $(elem).parent().parent();
        item.fadeOut(1000, function () {
            $(this).remove();
        });
    }

    $("#schedule_list_body").on('click', '.schedule_del', function () {
        if (window.sub_type == <?= Banner::SUB_TYPE_SCHEDULE_LIST;?>) {
            let schedule_id = $(this).attr('value');
            let index = window.schedule_mut.indexOf(schedule_id.toString());
            window.schedule_mut.splice(index, 1);
        }
        removeElem(this);
    });
    $("#schedule_one_body").on('click', '.schedule_del', function () {
        removeElem(this);
    });
    initSubType();
    <?php $this->endBlock()?>
    <?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
</script>
