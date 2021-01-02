<?php

use backend\models\BackendCommon;
use backend\models\forms\DownloadQueryForm;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\utils\DateTimeUtils;
use common\widgets\AMap;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DateTimePicker;
use yii\widgets\Pjax;
use backend\assets\ICheckAsset;
use yiichina\icheck\ICheck;

ICheckAsset::register($this);

/* @var common\models\Route $model */
$this->title = '路线信息';
$this->params['breadcrumbs'][] = ['label' => '路线信息列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    #map{
        width: 100%;
        height: 800px;
    }
    #route-view{
        position:absolute;
        width:275px;
        /*max-height: 355px;*/
        border:1px solid #1dff00;
        right:0px;
        top:0px;
        z-index:999;
        background-color: #ffffff;
    }
    #route-item{
        max-height: 300px;
        overflow: auto;
    }

    #select-route-item{
        max-height: 200px;
        overflow: auto;
    }

</style>
<?php  AMap::widget();?>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="page-heading">路线信息修改</h3>
                </div>
                <div class="box-body">
                    <div id="map">
                        <div id="route-view" class="box box-info box-solid">
                            <div class="box-header with-border">
                                <h5 class="page-heading">配送路线</h5>
                            </div>
                            <div id="route-item" class="box-body">
                                <?php Pjax::begin([
                                    'id' => 'route-list',
                                ]); ?>
                                <?php Pjax::end();?>
                            </div>
                            <div class="box-footer">
                                <div class="form-inline form-group">
                                    <?php
                                    $form = ActiveForm::begin([
                                        'type' => ActiveForm::TYPE_VERTICAL,
                                        'action' => ['index'],
                                        'method' => 'get',
                                        'id' => 'downloadQueryForm',
                                    ]);
                                    echo FormGrid::widget([
                                        'model' => new DownloadQueryForm(),
                                        'form' => $form,
                                        'autoGenerateColumns' => true,
                                        //'rowOptions'=>['class'=>'col-md-offset-1 col-md-10'],
                                        'rows' => [
                                            [
                                                'contentBefore' => '<legend class="text-info"><small>填写查询条件</small></legend>',
                                                'columns' => 12,
                                                'autoGenerateColumns' => false, // override columns setting
                                                'attributes'=>[
                                                    'order_owner'=>[   // radio list
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
                                                            // 'model' => $model,
                                                            'items' => BackendCommon::addBlankOption(GoodsConstantEnum::$ownerArr,[''=>'全部']),
                                                        ]
                                                    ],
                                                ]
                                            ],
                                            [
                                                'columns' => 12,
                                                'autoGenerateColumns' => false, // override columns setting
                                                'attributes' => [       // 3 column layout
                                                    'order_time_start'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入下单时间开始...','readonly' => true],'columnOptions'=>['colspan'=>12]],
                                                    'order_time_end'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入下单时间截止...','readonly' => true],'columnOptions'=>['colspan'=>12]],
                                                ]
                                            ],
                                            [
                                                'columns' => 12,
                                                'autoGenerateColumns' => false, // override columns setting
                                                'attributes' => [       // 3 column layout
                                                    'biz_date'=>[
                                                        'type'=>Form::INPUT_TEXT,
                                                        'options'=>[
                                                            'value'=>DateTimeUtils::formatYearAndMonthAndDay(time(),false),
                                                            'placeholder'=>'输入分拣时间...',
                                                            'readonly' => true
                                                        ],
                                                        'columnOptions'=>['colspan'=>12]],
                                                ]
                                            ],
                                        ]
                                    ]);
                                    ?>
                                    <?php ActiveForm::end(); ?>
                                </div>
                                <div>
                                    <?= Html::button("司机路线",['class'=>'btn btn-default','onclick'=>'downloadRouteSummary()'])?>
                                    <?= Html::button("团长订单",['class'=>'btn btn-default','onclick'=>'downloadDeliveryGoods()'])?>
                                    <?= Html::button("装车单",['class'=>'btn btn-default','onclick'=>'downloadRouteGoods()'])?>
                                    <?= Html::button("订单明细",['class'=>'btn btn-default','onclick'=>'downloadOrderList()'])?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
<?php $this->beginBlock('js_end') ?>
laydate.render({elem: '#downloadqueryform-order_time_start',type: 'datetime'});
laydate.render({elem: '#downloadqueryform-order_time_end',type: 'datetime'});
laydate.render({elem: '#downloadqueryform-biz_date',type: 'date'});
    let pointArr = [];
    let map = new AMap.Map('map', {
        resizeEnable: true,
        zoom:11,//级别
        //center: [120.15515, 30.27415],//中心点坐标
        viewMode:'2D'//使用3D视图
    });
    initRouteList();

    function initRouteList() {
        $.pjax.reload({url:"/route/route-list",timeout:"10000",push:false,replace:false,container:"#route-list",data:{},type:'get',dateType:'html'});
    }
    $('#route-list').on('pjax:success',function(data, status, xhr, options){
        /* 初始化 */
        console.log(data);
    })
    $('#route-list').on('pjax:error',function(xhr, textStatus, error, options){
        /* 初始化 */
        console.log(error);
    })
    function showDelivery(obj) {
        $.getJSON("/route/delivery-list?route_id="+obj.id,function(res){
            addMarkers(res.data,routes,obj.id)
        });
    }

    function addMarkers(list,routes,selectRouteId) {
        map.clearInfoWindow();
        map.remove(pointArr);
        pointArr = [];
        if (list!==undefined){
            for (let i = 0; i < list.length; i++) {
                let markerTmp = new AMap.Marker({
                    position: new AMap.LngLat(list[i]['lng'], list[i]['lat']),
                    title: list[i]['nickname']
                });
                //鼠标点击marker弹出自定义的信息窗体
                let infoWindow = new AMap.InfoWindow({
                    isCustom: true,  //使用自定义窗体
                    closeWhenClickMap:true, //控制是否在鼠标点击地图后关闭信息窗体
                    content: createInfoWindow(list[i],routes,selectRouteId),
                    offset: new AMap.Pixel(16, -45)
                });
                // 设置label标签
                // label默认蓝框白底左上角显示，样式className为：amap-marker-label
                markerTmp.setLabel({
                    offset: new AMap.Pixel(0, 0),  //设置文本标注偏移量
                    content: "<div class='info'>"+list[i]['nickname']+"</div>", //设置文本标注内容
                    direction: 'right' //设置文本标注方位
                });
                AMap.event.addListener(markerTmp, 'click', function (event) {
                    map.clearInfoWindow();
                    infoWindow.open(map, markerTmp.getPosition());
                    console.log(event);
                });
                pointArr.push(markerTmp);
                map.add(markerTmp);
            }
        }
        map.setFitView();
    }

    function createInfoWindow(delivery,routes,selectRouteId) {
        let html ='<div class="box box-success">\n' +
            '    <div class="box-header with-border">\n' +
            '        <h3 class="box-title">团长：'+delivery['nickname']+'</h3>\n' +
            '        <div class="box-tools pull-right">\n' +
            '            <button type="button" class="btn btn-box-tool" onclick="closeInfoWindow()"><i class="fa fa-times"></i></button>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="box-body">\n' +
            '        <div><strong>地址：</strong>'+delivery['community']+delivery['address']+'</div>\n' +
            '        <div><strong>司机选择：</strong>\n' +
            '        <div id="select-route-item">\n';
            for (let i = 0; i < routes.length; i++) {
                let route = routes[i];
                //排除所有团长这个节点
                if (route['id']==='-1'){
                    continue;
                }
                html+= createRadioInput(route['id'],route['nickname'],delivery['id'],route['id']===selectRouteId);
            }
            html+= '        </div>\n' +
            '    </div>\n' +
            '</div>';
        return html;
    }

    function createRadioInput(routeId,routeName,deliveryId,checked) {
        if (checked){
            return '<br/><label><input type="radio"  name="delivery-radio-'+deliveryId+'" delivery-id="'+deliveryId+'" value="'+routeId+'" onclick="updateRouteDelivery(this)" checked>'+routeName+'</label>';
        }
        else{
            return '<br/><label><input type="radio"  name="delivery-radio-'+deliveryId+'" delivery-id="'+deliveryId+'" value="'+routeId+'" onclick="updateRouteDelivery(this)">'+routeName+'</label>';
        }

    }
    function showCheckBox() {
        $('input[type=radio]').iCheck({
            checkboxClass: 'icheckbox_square-red',
            radioClass: 'iradio_square-red',
            increaseArea: '20%' // optional
        });
    }
    
    function closeInfoWindow() {
        map.clearInfoWindow();
    }

    function updateRouteDelivery(obj) {
        let routeId = $(obj).val();
        let deliveryId = $(obj).attr('delivery-id');
        $.getJSON("/route/update-route-delivery?route_id="+routeId+"&delivery_id="+deliveryId,function(res){
            if (res===undefined||res.status===false){
                bootbox.alert("分配失败，请重试");
            }
            initRouteList();
        });
    }
    
    function getParams() {
        let bizDate = $('#downloadqueryform-biz_date').val();
        if (bizDate===undefined||bizDate===''){
            bootbox.alert("业务日期不能为空");
            throw SyntaxError();
        }
        let url = '?date='+bizDate;
        let order_time_start= $('#downloadqueryform-order_time_start').val();
        if (order_time_start!==undefined||order_time_start!==''){
            url += "&order_time_start="+order_time_start;
        }

        let order_time_end= $('#downloadqueryform-order_time_end').val();
        if (order_time_end!==undefined||order_time_end!==''){
            url += "&order_time_end="+order_time_end;
        }
        let order_owner= $("input[name='DownloadQueryForm[order_owner]']:checked").val();
        if (order_owner!==undefined||order_owner!==''){
            url += "&order_owner="+order_owner;
        }
        return url;
    }
    
    function downloadRouteSummary() {
        let url = '/download/route-summary-list'+getParams();
        window.open(url);
    }

    function downloadDeliveryGoods() {
        let url = '/download/delivery-goods-list'+getParams();
        window.open(url);
    }

    function downloadOrderList() {
        let url = '/download/order-list'+getParams();
        window.open(url);
    }

    function downloadRouteGoods() {
        let url = '/download/route-goods-list'+getParams();
        window.open(url);
    }
    
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>

