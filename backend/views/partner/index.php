<?php

use backend\assets\IonIconsAsset;
use backend\components\WeightDefault;
use common\models\Delivery;
use common\utils\DateTimeUtils;
use kartik\daterange\DateRangePicker;
use kartik\tabs\TabsX;
use nullref\datatable\DataTable;
use yii\bootstrap\Html;
use yii\web\JsExpression;
use yiizh\echarts\EChartsAsset;
use common\widgets\AMap;
use yii\helpers\Url;
EChartsAsset::register($this);
IonIconsAsset::register($this);
$this->title = '合伙人统计';
$this->params['breadcrumbs'][] = $this->title;

//TabsX::widget();
//AMap::widget(['plugin' => 'AMap.MarkerClusterer']);
?>
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h4>合伙人数量</h4>
                    <h3 id="summary1-h">&nbsp</h3>
                    <p id="summary1-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-bag"></i>
                </div>
<!--                <a href="javascript:void(0);" class="small-box-footer">-->
                <div class="small-box-footer"><i class="fa">配送团长：<span id="delivery"></span></i>&nbsp&nbsp&nbsp<i class="fa">未认证人数：<span id="unAuthCount"></span></i></div>
<!--                </a>-->
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h4>粉丝数量</h4>
                    <h3 id="summary2-h">&nbsp</h3>
                    <p id="summary2-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-stats-bars"></i>
                </div>
                <a href="<?php echo Url::toRoute(['partner', 'DeliverySearch[auth]'=>Delivery::AUTH_STATUS_AUTH]);?>" class="small-box-footer">较昨日新增：<span id="nowUserCount"></span> &nbsp&nbsp&nbsp 查看详情<i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h4>商品数</h4>
                    <h3 id="summary3-h">&nbsp</h3>
                    <p id="summary3-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-add"></i>
                </div>
<!--                <a href="#" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>-->
                <div class="small-box-footer"><i class="fa">后台商品：<span id="GoodsCount"></span></i>&nbsp&nbsp&nbsp<i class="fa">新建商品：<span id="partnerNewCount"></span></i></div>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-red">
                <div class="inner">
                    <h4>总销售额</h4>
                    <h3 id="summary4-h">&nbsp</h3>
                    <p id="summary4-p">&nbsp</p>
                </div>
                <div class="icon">
                    <i class="ion ion-pie-graph"></i>
                </div>
                <a href="javascript:void(0);" class="small-box-footer"> <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
    </div>
    <!-- Main row -->
    <div class="row">
        <section class="col-lg-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs pull-right">
                    <li><?php echo Html::button("导出",['onclick'=>'downloadDashboard();','class'=>'btn btn-info']);?></li>
                    <li><?php
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
                    <li><a href="#order-day-chart" data-toggle="tab" id="order-day-chart-a">订单走势图</a></li>
                    <li class="active"><a href="#delivery-day-chart" data-toggle="tab" id="delivery-day-chart-a">交易走势图</a></li>
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
                    <li><a href="#delivery-summary-table" data-toggle="tab" id="delivery-summary-table-a">排期统计</a></li>
                    <li><a href="#partner-summary-table" data-toggle="tab" id="partner-summary-table-a">合伙人销售统计</a></li>
                    <li class="active"><a href="#goods-summary-table" data-toggle="tab" id="goods-summary-table-a">商品统计</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox">合伙人统计</i></li>
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
                                    'url'=>'/partner/partner-goods',
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
                                        'data' => 'nickname',
                                        'title' => '合伙人名称',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'count',
                                        'title' => '商品数',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'add',
                                        'title' => '后台添加商品',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'up',
                                        'title' => '新建商品',
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
                                    'url'=>'/partner/partner-schedule',
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
                                        'data' => 'nickname',
                                        'title' => '合伙人名',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'count',
                                        'title' => '创建排期数',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'proceed',
                                        'title' => '进行中',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'expire',
                                        'title' => '到期',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'not_start',
                                        'title' => '未开始',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'order_count',
                                        'title' => '有销量',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                ],
                            ]) ?>
                        </div>
                    </div>
                    <div class="chart tab-pane" id="partner-summary-table" style="height: 300px;">
                        <div class="container-fluid">
                            <?= DataTable::widget([
                                'id' => 'partner-summary-datatable',
                                'scrollY' => '170px',
                                'scrollCollapse' => true,
                                'paging' => false,
                                'ajax' => [
                                    'url'=>'/partner/partner-summary',
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
                                        'title' => '合伙人名',
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
</section>
<script>
    function initDeliveryDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/partner/partner-day", data, function(response){
            if (response.status===true){
                let deliveryDayChart = echarts.init(document.getElementById('delivery-day-chart'));
                let data = response.data;
                // 指定图表的配置项和数据
                let option = {
                    title: {
                        text: '交易走势'
                    },
                    tooltip: {},
                    legend: {
                        data:['交易额','优惠金额','退款金额']
                    },
                    xAxis: {
                        data: data.time
                    },
                    yAxis: {},
                    series: [
                        {
                            name: '交易额',
                            type: 'bar',
                            data: data.deal_amount
                        },
                        {
                            name: '优惠金额',
                            type: 'bar',
                            data: data.discount_amount
                        },
                        {
                            name: '退款金额',
                            type: 'bar',
                            data: data.refund_amount
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
        }, 'json');
    }

    function initOrderDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/partner/partner-order-day", data, function(response){
            if (response.status===true){
                let orderDayChart = echarts.init(document.getElementById('order-day-chart'));
                let data = response.data;
                // 指定图表的配置项和数据
                let option = {
                    title: {
                        text: '订单走势'
                    },
                    tooltip: {},
                    legend: {
                        data:['订单数','付款订单','成功订单','售后订单']
                    },
                    xAxis: {
                        data: data.time
                    },
                    yAxis: {},
                    series: [
                        {
                            name: '订单数',
                            type: 'bar',
                            data: data.count_order
                        },
                        {
                            name: '付款订单',
                            type: 'bar',
                            data: data.count_pay_order
                        },
                        {
                            name: '成功订单',
                            type: 'bar',
                            data: data.count_completion_order
                        },
                        {
                            name: '售后订单',
                            type: 'bar',
                            data: data.count_customer_service
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
        }, 'json');
    }

    function initSortSummary() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/partner/partner-sort-summary",data,function(response){
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

    function initPartnerSummary() {
        $("#partner-summary-table-a").click(function(){
            setTimeout(function () {
                $('#partner-summary-datatable').DataTable().columns.adjust();
            },100);
        });
    }
    function reloadPartnerSummary() {
        $('#partner-summary-datatable').DataTable().ajax.reload();
    }
</script>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
    // 头部统计信息
    $.get("/partner/summary",function(response){
        if (response.status===true){
            let data = response.data;
            // 合伙人数量
            $('#summary1-h').html(data['partner']['partnerCount']);
            $('#delivery').html(data['partner']['deliveryCount']);
            $('#unAuthCount').html(data['partner']['unAuthCount']);
            // 粉丝数量
            $('#summary2-h').html(data['partnerFans']['userCount']);
            $('#nowUserCount').html(data['partnerFans']['nowUserCount']);
            // 商品数
            $('#summary3-h').html(data['partnerGoods']['partnerGoodsCount']);
            $('#GoodsCount').html(data['partnerGoods']['GoodsCount']);
            $('#partnerNewCount').html(data['partnerGoods']['partnerNewCount']);
            // 总销售额
            $('#summary4-h').html(data['partnerOrder']);
        }
    },'json');

    // 订单走势图
    initDeliveryDay();
    // 交易走势图
    initOrderDay();
    // 饼状图
    initSortSummary();
    initDeliveryDay();
    initOrderDay();
    initSortSummary();
    initGoodsSummary();
    initDeliverySummary();
    initPartnerSummary();
    initOrderDeliveryDay();
<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>