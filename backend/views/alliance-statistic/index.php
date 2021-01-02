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
                    <h4>联盟商</h4>
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
                    <h4>下单客户</h4>
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
                    <h4>商品销售总量</h4>
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
                                "apply.daterangepicker" => "function() { initOrderDay();initSortSummary(); reloadGoodsSummary(); reloadDeliverySummary(); }",
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
                    <li class="active"><a href="#order-day-chart" data-toggle="tab" id="order-day-chart-a">交易走势图</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox"></i>走势图</li>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div class="chart tab-pane active" id="order-day-chart" style="height: 300px;"></div>
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
                    <li><a href="#alliance-summary-table" data-toggle="tab" id="alliance-summary-table-a">联盟点销售统计</a></li>
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
                                    'url'=>'/alliance-statistic/goods-summary',
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
                    <div class="chart tab-pane" id="alliance-summary-table" style="height: 300px;">
                        <div class="container-fluid">
                            <?= DataTable::widget([
                                'id' => 'alliance-summary-datatable',
                                'scrollY' => '170px',
                                'scrollCollapse' => true,
                                'paging' => false,
                                'ajax' => [
                                    'url'=>'/alliance-statistic/alliance-summary',
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
                                        'data' => 'alliance_name',
                                        'title' => '联盟点名',
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
<script type="text/javascript">

    function initOrderDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/alliance-statistic/order-day",data,function(response){
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
                        data:['销售额','下单团点数','优惠金额','退款金额']
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
                            name: '下单团点数',
                            type: 'bar',
                            data: data.delivery_count
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
        $("#alliance-summary-table-a").click(function(){
            setTimeout(function () {
                $('#alliance-summary-datatable').DataTable().columns.adjust();
            },100);
        });
    }
    function reloadDeliverySummary() {
        $('#alliance-summary-datatable').DataTable().ajax.reload();
    }

    function initSortSummary() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        $.post("/alliance-statistic/sort-summary",data,function(response){
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


    /**
     * 下载首页数据
     */
    function downloadDashboard() {
        let start_date = $('#query_datetime-start').val();
        let end_date = $('#query_datetime-end').val();
        url = '/alliance-statistic/download-dashboard?start_date='+start_date+'&end_date='+end_date;
        window.open(url,'_blank');
        //window.location.href = url;
    }
</script>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
$.get("/alliance-statistic/summary",function(response){
    if (response.status===true){
        let data = response.data;
        $('#summary1-h').html(data['allianceCount']);
        $('#summary1-p').html(
            "交易联盟商:"+data['allianceOrderCount']);

        $('#summary2-h').html(data['customerOrderCount']);
        $('#summary2-p').html(
            "配送团长:"+data['deliveryCount']
            +"&nbsp&nbsp&nbsp客户数:"+data['customerCount']);

        $('#summary3-h').html(data['orderGoodsSum']);
        $('#summary3-p').html("单品总数:"+data['orderGoodsCount']);

        $('#summary4-h').html(data['orderAmount']);
    }
},'json');


initOrderDay();
initSortSummary();
initGoodsSummary();
initDeliverySummary();

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>