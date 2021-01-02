<?php

use backend\assets\IonIconsAsset;
use backend\components\WeightDefault;
use backend\models\BackendCommon;
use common\utils\DateTimeUtils;
use common\widgets\AMap;
use kartik\daterange\DateRangePicker;
use kartik\tabs\TabsX;
use nullref\datatable\DataTable;
use yii\bootstrap\Html;
use yii\web\JsExpression;
use yiizh\echarts\EChartsAsset;

EChartsAsset::register($this);
IonIconsAsset::register($this);
$this->title = '控制台';
$this->params['breadcrumbs'][] = $this->title;

TabsX::widget();
AMap::widget(['plugin' => 'AMap.MarkerClusterer']);
?>
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h4>服务商</h4>
                    <h3 id="summary1-h">&nbsp</h3>
                    <p id="summary1-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
                <a href="#" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h4>商品销售总量</h4>
                    <h3 id="summary2-h">&nbsp</h3>
                    <p id="summary2-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="#" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h4>总用户数</h4>
                    <h3 id="summary3-h">&nbsp</h3>
                    <p id="summary3-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
                <a href="#" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h4>累计销售额</h4>
                    <h3 id="summary4-h">&nbsp</h3>
                    <p id="summary4-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="#" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- Main row -->
    <div class="row">
        <section class="col-lg-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs pull-right">
                    <li ><?php echo Html::button("导出",['onclick'=>'downloadDashboard();','class'=>'btn btn-info']);?></li>
                    <li> <?php
                        echo DateRangePicker::widget([
                            'id' => 'query_datetime',
                            'name' => 'query_datetime',
                            'attribute'=>'query_datetime',
                            'convertFormat'=>true,
                            'readonly' => true,
                            'value' => DateTimeUtils::formatYearAndMonthAndDay(DateTimeUtils::startOfMonthLong(time(),false),false).' - '.DateTimeUtils::formatYearAndMonthAndDay(time(),false),
                            'startAttribute' => 'query_from_datetime',
                            'endAttribute' => 'query_to_datetime',
                            'options' => ['class'=>'form-control','style'=>'width:200px'],
                            'pluginOptions'=>[
                                'locale'=>[
                                    'format'=>'Y-m-d'
                                ],
                                'opens'=>'left'
                            ],
                            'pluginEvents' => [
                                "apply.daterangepicker" => "function() { initDeliveryDay();initOrderDay();initSortSummary(); reloadGoodsSummary(); reloadDeliverySummary();initOrderDeliveryDay(); }",
                            ]
                        ]);
                        ?></li>
                        <!-- /. tools -->
                    <li class="header"><i class="fa fa-inbox"></i>时间区间</li>
                </ul>
            </div>
        </section>
    </div>
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
                    <li><a href="#order-day-chart" data-toggle="tab" id="order-day-chart-a">交易走势图</a></li>
                    <li class="active"><a href="#delivery-day-chart" data-toggle="tab" id="delivery-day-chart-a">团长走势图</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox"></i>走势图</li>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div class="chart tab-pane active" id="delivery-day-chart" style="height: 300px;"></div>
                    <div class="chart tab-pane" id="order-day-chart" style="height: 300px;"></div>
                </div>
            </div>
            <!-- /.nav-tabs-custom -->
        </section>


    </div>
    <!-- Left col -->
    <div class="row">
        <!-- 分类销售占比 -->
        <section class="col-lg-5">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
                    <li class="active"><a href="#sort-summary-chart" data-toggle="tab" id="sort-summary-chart-a">分类销售占比</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox"></i>分类销售占比</li>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div class="chart tab-pane active"  id="sort-summary-chart"  style="height: 300px;"></div>
                </div>
            </div>
            <!-- /.nav-tabs-custom -->
        </section>
        <!-- 分类销售占比 -->
        <section class="col-lg-7">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
                    <li><a href="#delivery-summary-table" data-toggle="tab" id="delivery-summary-table-a">团长销售统计</a></li>
                    <li class="active"><a href="#goods-summary-table" data-toggle="tab" id="goods-summary-table-a">商品销售统计</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox"></i>销售统计</li>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div class="chart tab-pane active" id="goods-summary-table" style="height: 300px;">
                        <div class="container-fluid">
                            <?= DataTable::widget([
                                'id' => 'goods-summary-datatable',
                                'scrollY' => '170px',
                                'scrollCollapse' => true,
                                'paging' => false,
                                'ajax' => [
                                    'url'=>'/site/goods-summary',
                                    'data' => new JsExpression("
                                        function ( d ) {
                                            d.start_date = $('#query_datetime-start').val();
                                            d.end_date =  $('#query_datetime-end').val();
                                        }
                                    "),
                                ],
                                'language' => WeightDefault::$datatableLanguage,
                                'tableOptions' => [
                                    'class' => 'table',
                                ],
                                'columns' => [
                                    [
                                        'data' => 'id',
                                        'title' => '序号',
                                        'sClass' => 'text-center whiteSpace',
                                        'render' => new JsExpression("
                                             function(data,type,row,meta) {
                                                return meta.row + 1 + meta.settings._iDisplayStart;
                                             }
                                        "),
                                    ],
                                    [
                                        'data' => 'goods_name',
                                        'title' => '商品名',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'num',
                                        'title' => '销量',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'percentage',
                                        'title' => '占比',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <div class="chart tab-pane" id="delivery-summary-table" style="height: 300px;">
                        <div class="container-fluid">
                            <?= DataTable::widget([
                                'id' => 'delivery-summary-datatable',
                                'scrollY' => '170px',
                                'scrollCollapse' => true,
                                'paging' => false,
                                'ajax' => [
                                    'url'=>'/site/delivery-summary',
                                    'data' => new JsExpression("
                                        function ( d ) {
                                            d.start_date = $('#query_datetime-start').val();
                                            d.end_date =  $('#query_datetime-end').val();
                                        }
                                    "),
                                ],
                                'language' => WeightDefault::$datatableLanguage,
                                'tableOptions' => [
                                    'class' => 'table',
                                ],
                                'columns' => [
                                    [
                                        'data' => 'id',
                                        'title' => '序号',
                                        'sClass' => 'text-center whiteSpace',
                                        'render' => new JsExpression("
                                             function(data,type,row,meta) {
                                                return meta.row + 1 + meta.settings._iDisplayStart;
                                             }
                                        "),
                                    ],
                                    [
                                        'data' => 'delivery_name',
                                        'title' => '团长名',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'amount',
                                        'title' => '销售金额',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'percentage',
                                        'title' => '占比',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.nav-tabs-custom -->
        </section>
    </div>
    <!-- Left col -->
    <div class="row">
        <section class="col-lg-12">
            <!-- Map box -->
            <div class="box box-solid bg-light-blue-gradient">
<!--                --><?php //if(BackendCommon::isSuperCompany(BackendCommon::getFCompanyId())): ?>
                    <div class="box-header">
                        <!-- tools box -->
                        <i class="fa fa-map-marker"></i>
                        <h3 class="box-title">
                            注册用户
                        </h3>
                    </div>
                    <div class="box-body">
                        <div id="user-map" style="height: 500px; width: 100%;"></div>
                    </div>
<!--                --><?php //endif; ?>
                <!-- /.box-body-->
                <div class="box-footer no-border">
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <div id="sparkline-customer-count-chart" style="height: 180px"></div>
                            <div class="knob-label  ">下单用户</div>
                        </div>
                        <!-- ./col -->
                        <div class="col-xs-3 text-center">
                            <div id="sparkline-order-amount-chart" style="height: 180px"></div>
                            <div class="knob-label">客单价</div>
                        </div>
                        <!-- ./col -->
                        <div class="col-xs-3 text-center">
                            <div id="sparkline-delivery-count-chart"  style="height: 180px"></div>
                            <div class="knob-label">新增配送团长</div>
                        </div>
                        <!-- ./col -->
                        <div class="col-xs-3 text-center">
                            <div id="sparkline-popularizer-count-chart"  style="height: 180px"></div>
                            <div class="knob-label">新增分享团长</div>
                        </div>
                        <!-- ./col -->
                    </div>
                    <!-- /.row -->
                </div>
            </div>
            <!-- /.box -->
        </section>
    </div>
</section>
<script type="text/javascript">
    function initDeliveryDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/site/delivery-day",data,function(response){
            if (response.status===true){
                let deliveryDayChart = echarts.init(document.getElementById('delivery-day-chart'));
                let data = response.data;
                // 指定图表的配置项和数据
                let option = {
                    title: {
                        text: '团长走势'
                    },
                    tooltip: {},
                    legend: {
                        data:['配送团长下单数','分享团长下单数','配送团长佣金','分享团长佣金']
                    },
                    xAxis: {
                        data: data.time
                    },
                    yAxis: {},
                    series: [
                        {
                            name: '配送团长下单数',
                            type: 'bar',
                            data: data.delivery_cnt
                        },
                        {
                            name: '分享团长下单数',
                            type: 'bar',
                            data: data.popularizer_cnt
                        },
                        {
                            name: '配送团长佣金',
                            type: 'bar',
                            data: data.distribute_delivery_amount
                        },
                        {
                            name: '分享团长佣金',
                            type: 'bar',
                            data: data.distribute_popularizer_amount
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                deliveryDayChart.setOption(option);
                $("#delivery-day-chart-a").click(function(){
                    setTimeout(function () {
                        deliveryDayChart.resize();
                    },100);
                });
            }
        },'json');
    }

    function initOrderDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/site/order-day",data,function(response){
            if (response.status===true){
                let orderDayChart = echarts.init(document.getElementById('order-day-chart'));
                let data = response.data;
                // 指定图表的配置项和数据
                let option = {
                    title: {
                        text: '交易走势'
                    },
                    tooltip: {},
                    legend: {
                        data:['销售额','订单数','优惠金额','售后订单']
                    },
                    xAxis: {
                        data: data.time
                    },
                    yAxis: {},
                    series: [
                        {
                            name: '销售额',
                            type: 'bar',
                            data: data.need_amount
                        },
                        {
                            name: '订单数',
                            type: 'bar',
                            data: data.order_count
                        },
                        {
                            name: '优惠金额',
                            type: 'bar',
                            data: data.discount_amount
                        },
                        {
                            name: '售后订单',
                            type: 'bar',
                            data: data.customer_service_count
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                orderDayChart.setOption(option);
                $("#order-day-chart-a").click(function(){
                    setTimeout(function () {
                        orderDayChart.resize();
                    },100);
                });
            }
        },'json');
    }

    function initGoodsSummary() {
        $("#goods-summary-table-a").click(function(){
            setTimeout(function () {
                $('#goods-summary-datatable').DataTable().columns.adjust();
            },100);
        });
    }

    function reloadGoodsSummary() {
        $('#goods-summary-datatable').DataTable().ajax.reload();
    }

    function initDeliverySummary() {
        $("#delivery-summary-table-a").click(function(){
            setTimeout(function () {
                $('#delivery-summary-datatable').DataTable().columns.adjust();
            },100);
        });
    }
    function reloadDeliverySummary() {
        $('#delivery-summary-datatable').DataTable().ajax.reload();
    }

    function initSortSummary() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/site/sort-summary",data,function(response){
            if (response.status===true){
                let sortSummaryChart = echarts.init(document.getElementById('sort-summary-chart'));
                let data = response.data;
                // 指定图表的配置项和数据
                let option = {
                    title : {
                        text: '分类销售占比',
                        x:'center'
                    },
                    tooltip : {
                        trigger: 'item',
                        formatter: "{a} <br/>{b} : {c} ({d}%)"
                    },
                    legend: {
                        type: 'scroll',
                        orient: 'vertical',
                        right: 10,
                        top: 20,
                        bottom: 20,
                        data: data.legendData,

                        selected: data.selected
                    },
                    series : [
                        {
                            name: '分类销售占比',
                            type: 'pie',
                            radius : '55%',
                            center: ['40%', '50%'],
                            data: data.seriesData,
                            itemStyle: {
                                emphasis: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                sortSummaryChart.setOption(option);
            }
        },'json');
    }

    function showUserInfo() {
        let map = new AMap.Map('user-map', {
            resizeEnable: true,
            zoom:9,//级别
            //center: [120.15515, 30.27415],//中心点坐标
            viewMode:'2D'//使用3D视图
        });
        $.get("/site/user-info-summary",function(response){
            if (response.status===true){
                let data = response.data;
                let markers = [];
                if (data.user_infos!=undefined&&data.user_infos.length>0){
                    for (let i = 0; i < data.user_infos.length; i += 1) {
                        markers.push(new AMap.Marker({
                            position: data.user_infos[i].lnglat,
                            title:data.user_infos[i].nickname,
                            offset: new AMap.Pixel(-15, -15)
                        }))
                        <?php if (BackendCommon::getFCompanyId()=="1"):?>
                        for (let j = 0; j < 7; j++) {
                            markers.push(new AMap.Marker({
                                position: data.user_infos[i].lnglat,
                                title:data.user_infos[i].nickname,
                                offset: new AMap.Pixel(-15, -15)
                            }))
                        }
                        <?php endif;?>
                    }
                }
                cluster = new AMap.MarkerClusterer(map, markers, {gridSize: 80});
                map.setFitView();
            }
        },'json');
    }


    function initOrderDeliveryDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/site/order-delivery-day",data,function(response){
            if (response.status===true){
                let data = response.data;

                /**
                 * 下单人数
                 */
                let customerCountChart = echarts.init(document.getElementById('sparkline-customer-count-chart'));
                // 指定图表的配置项和数据
                let customerCountChartOption = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: {
                        show : false,
                        type: 'category',
                        boundaryGap: false,
                        scale:true,
                        data:  data.time_text,
                    },
                    yAxis: {
                        type: 'value',
                        show : false,
                        scale:true,
                    },
                    series: [
                        {
                            name: '下单用户',
                            type: 'line',
                            data: data.customer_count,
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                        { offset: 0, color: "#F0FFFF" },
                                        { offset: 1, color: "#ebf4f9" }
                                    ])
                                }
                            }, //填充区域样式
                            itemStyle : {
                                normal : {
                                    color : 'rgb(0,136,212)',
                                    borderColor : 'rgba(0,136,212,0.2)',
                                    borderWidth : 5
                                }
                            },
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                customerCountChart.setOption(customerCountChartOption);


                /**
                 * 客单价
                 */
                let orderAmountChart = echarts.init(document.getElementById('sparkline-order-amount-chart'));
                // 指定图表的配置项和数据
                let orderAmountChartOption = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: {
                        show : false,
                        type: 'category',
                        boundaryGap: false,
                        scale:true,
                        data:  data.time_text,
                    },
                    yAxis: {
                        type: 'value',
                        show : false,
                        scale:true,
                    },
                    series: [
                        {
                            name: '客单价',
                            type: 'line',
                            data: data.order_amount,
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                        { offset: 0, color: "#F0FFFF" },
                                        { offset: 1, color: "#ebf4f9" }
                                    ])
                                }
                            }, //填充区域样式
                            itemStyle : {
                                normal : {
                                    color : 'rgb(0,136,212)',
                                    borderColor : 'rgba(0,136,212,0.2)',
                                    borderWidth : 5
                                }
                            },
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                orderAmountChart.setOption(orderAmountChartOption);


                /**
                 * 新增配送团长
                 */
                let deliveryCountChart = echarts.init(document.getElementById('sparkline-delivery-count-chart'));
                // 指定图表的配置项和数据
                let deliveryCountChartOption = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: {
                        show : false,
                        type: 'category',
                        boundaryGap: false,
                        scale:true,
                        data:  data.time_text,
                    },
                    yAxis: {
                        type: 'value',
                        show : false,
                        scale:true,
                    },
                    series: [
                        {
                            name: '新增配送团长',
                            type: 'line',
                            data: data.delivery_count,
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                        { offset: 0, color: "#F0FFFF" },
                                        { offset: 1, color: "#ebf4f9" }
                                    ])
                                }
                            }, //填充区域样式
                            itemStyle : {
                                normal : {
                                    color : 'rgb(0,136,212)',
                                    borderColor : 'rgba(0,136,212,0.2)',
                                    borderWidth : 5
                                }
                            },
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                deliveryCountChart.setOption(deliveryCountChartOption);

                /**
                 * 新增分享团长
                 */
                let popularizerCountChart = echarts.init(document.getElementById('sparkline-popularizer-count-chart'));
                // 指定图表的配置项和数据
                let popularizerCountChartOption = {
                    tooltip: {
                        trigger: 'axis'
                    },
                    xAxis: {
                        show : false,
                        type: 'category',
                        boundaryGap: false,
                        scale:true,
                        data:  data.time_text,
                    },
                    yAxis: {
                        type: 'value',
                        show : false,
                        scale:true,
                    },
                    series: [
                        {
                            name: '新增分享团长',
                            type: 'line',
                            data: data.popularizer_count,
                            areaStyle: {
                                normal: {
                                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                        { offset: 0, color: "#F0FFFF" },
                                        { offset: 1, color: "#ebf4f9" }
                                    ])
                                }
                            }, //填充区域样式
                            itemStyle : {
                                normal : {
                                    color : 'rgb(0,136,212)',
                                    borderColor : 'rgba(0,136,212,0.2)',
                                    borderWidth : 5
                                }
                            },
                        }
                    ]
                };
                // 使用刚指定的配置项和数据显示图表。
                popularizerCountChart.setOption(popularizerCountChartOption);


            }
        },'json');
    }

    /**
     * 下载首页数据
     */
    function downloadDashboard() {
        let start_date = $('#query_datetime-start').val();
        let end_date = $('#query_datetime-end').val();
        url = '/site/download-dashboard?start_date='+start_date+'&end_date='+end_date;
        window.open(url,'_blank');
        //window.location.href = url;
    }
</script>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
$.get("/site/summary",function(response){
    if (response.status===true){
        let data = response.data;
        $('#summary1-h').html(data['delivery']['allCount']);
        $('#summary1-p').html(
            "配送:"+data['delivery']['deliveryCount']
            +"&nbsp&nbsp&nbsp分享:"+data['delivery']['popularizerCount']
            +"&nbsp&nbsp&nbsp联盟商户:"+data['delivery']['allianceCount']);

        $('#summary2-h').html(data['order_goods']);

        $('#summary3-h').html(data['customer']['customerCount']);
        $('#summary3-p').html("已下单:"+data['customer']['customerOrderCount']);

        $('#summary4-h').html(data['order_need_amount']);
    }
},'json');




initDeliveryDay();
initOrderDay();
initSortSummary();
initGoodsSummary();
initDeliverySummary();
showUserInfo();
initOrderDeliveryDay();

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>