<?php

use common\utils\DateTimeUtils;
use common\widgets\AMap;
use kartik\helpers\Html;
use kartik\widgets\DateTimePicker;
use yii\widgets\Pjax;
use backend\assets\ICheckAsset;
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
        width:270px;
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
                                <h5 class="page-heading">团长订单统计</h5>
                            </div>
                            <div id="route-item" class="box-body">
                                <div class="form-inline input-group">
                                    <span class="input-group-btn">
                                        <label>业务时间</label>
                                    </span>
                                    <?= DateTimePicker::widget([
                                        'name' => 'biz_date',
                                        'id'=>'biz_date',
                                        'type' => DateTimePicker::TYPE_INPUT,
                                        'value' => DateTimeUtils::formatYearAndMonthAndDay(time(),false),
                                        'options' => ['placeholder' => '选择业务时间...','readonly' => true],
                                        'convertFormat' => true,
                                        'pluginOptions' => [
                                            'format' => 'yyyy-MM-dd',
                                            'todayHighlight' => true,
                                            'todayBtn'=>true,
                                            'minView'=>'month',
                                            'autoclose' => true,
                                        ]
                                    ]);?>
                                    <span class="input-group-btn">
                                        <?= Html::button("查询",['class'=>'btn btn-default','onclick'=>'searchDeliveryOrder()'])?>
                                    </span>

                                </div>
                                <?php Pjax::begin([
                                    'id' => 'delivery-list',
                                ]); ?>
                                <?php Pjax::end();?>
                            </div>
                            <div class="box-footer">
                                <div>
                                    <?= Html::button("团长订单",['class'=>'btn btn-default','onclick'=>'downloadDeliveryOrder()'])?>
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
    let pointArr = [];
    let nowPoint = undefined;
    let map = new AMap.Map('map', {
        resizeEnable: true,
        zoom:11,//级别
        //center: [120.15515, 30.27415],//中心点坐标
        viewMode:'2D'//使用3D视图
    });
    searchDeliveryOrder();

    function searchDeliveryOrder() {
        let date = $('#biz_date').val();
        $.pjax.reload({url:"/delivery/delivery-list-map?date="+date,timeout:"10000",push:false,replace:false,container:"#delivery-list",data:{},type:'get',dateType:'html'});
    }
    $('#delivery-list').on('pjax:success',function(data, status, xhr, options){
        /* 初始化 */
        console.log(data);
        addMarkers(deliveryOrderList);
    })
    $('#delivery-list').on('pjax:error',function(xhr, textStatus, error, options){
        /* 初始化 */
        console.log(error);
    })


    function centerDelivery(deliveryId) {
        if (nowPoint!==undefined){
            // 设置点标记的动画效果，点标记的动画效果，默认值
            nowPoint.setAnimation('AMAP_ANIMATION_NONE');
        }
        if (pointArr.length>0){
            for (let i = 0; i < pointArr.length; i++) {
                if (pointArr[i].getExtData()==deliveryId){
                    nowPoint = pointArr[i];
                    // 设置点标记的动画效果，此处为弹跳效果
                    nowPoint.setAnimation('AMAP_ANIMATION_BOUNCE');
                    map.setCenter(nowPoint.getPosition());
                }
            }
        }
    }

    function addMarkers(deliveryOrderList) {
        map.clearInfoWindow();
        map.remove(pointArr);
        pointArr = [];
        nowPoint = undefined;
        if (deliveryOrderList!==undefined){
            for (let i = 0; i < deliveryOrderList.length; i++) {
                let markerTmp = new AMap.Marker({
                    position: new AMap.LngLat(deliveryOrderList[i]['lng'], deliveryOrderList[i]['lat']),
                    title: deliveryOrderList[i]['delivery_nickname'],
                    extData:deliveryOrderList[i]['delivery_id']
                });
                //鼠标点击marker弹出自定义的信息窗体
                let infoWindow = new AMap.InfoWindow({
                    isCustom: true,  //使用自定义窗体
                    closeWhenClickMap:true, //控制是否在鼠标点击地图后关闭信息窗体
                    content: createInfoWindow(deliveryOrderList[i]),
                    offset: new AMap.Pixel(16, -45)
                });
                // 设置label标签
                // label默认蓝框白底左上角显示，样式className为：amap-marker-label
                markerTmp.setLabel({
                    offset: new AMap.Pixel(0, 0),  //设置文本标注偏移量
                    content: "<div class='info'>"+deliveryOrderList[i]['delivery_nickname']+"</div>", //设置文本标注内容
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

    function createInfoWindow(deliveryOrderItem) {
        let html ='<div class="box box-success">\n' +
            '    <div class="box-header with-border">\n' +
            '        <h3 class="box-title">团长：'+deliveryOrderItem['delivery_nickname']+'</h3>\n' +
            '        <div class="box-tools pull-right"> \n' +
            '            <button type="button" class="btn btn-box-tool" onclick="closeInfoWindow()"><i class="fa fa-times"></i>\n' +
            '            </button>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '    <div class="box-body">\n' +
            '        <div class="container-fluid">\n' +
            '            <div class="row"><strong>订单：</strong>'+deliveryOrderItem['order_count']+'单</div>\n' +
            '            <div class="row"><strong>客户：</strong>'+deliveryOrderItem['customer_count']+'人</div>\n' +
            '            <div class="row"><strong>金额：</strong>'+deliveryOrderItem['real_amount']+'元</div>\n' +
            '            <div class="row"><strong>售后：</strong>'+deliveryOrderItem['customer_service_count']+'单</div>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '</div>';
        return html;
    }

    function closeInfoWindow() {
        map.clearInfoWindow();
    }


    function downloadDeliveryOrder() {
        let url = '/download/delivery-order-list?date='+$('#biz_date').val();
        window.open(url);
    }

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>

