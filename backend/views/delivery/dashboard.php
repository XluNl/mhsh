<?php

use backend\assets\IonIconsAsset;
use backend\components\WeightDefault;
use common\utils\DateTimeUtils;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use nullref\datatable\DataTable;
use yii\helpers\Html;
use yii\web\JsExpression;
use yiizh\echarts\EChartsAsset;
EChartsAsset::register($this);
IonIconsAsset::register($this);
$this->title = '团长数据台';
$this->params['breadcrumbs'][] = $this->title;
/**
 * @var $deliveryOptions array
 *
 */
?>
<section class="content">
    <!-- Main row -->
    <div class="row">
        <section class="col-lg-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs ">
                    <li class="header"><i class="fa fa-inbox"></i>时间区间</li>
                    <li> <?php
                        echo DateRangePicker::widget([
                            'id' => 'query_datetime',
                            'name' => 'query_datetime',
                            'attribute'=>'query_datetime',
                            'convertFormat'=>true,
                            'readonly' => true,
                            'value' => DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong(time(),false)).' - '.DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong(time(),false)),
                            'startAttribute' => 'query_from_datetime',
                            'endAttribute' => 'query_to_datetime',
                            'options' => ['class'=>'form-control','style'=>'width:400px'],
                            'pluginOptions'=>[
                                'timePicker'=>true,
                                'timePickerIncrement'=>1,
                                'timePicker24Hour'=>true,
                                'timePickerSeconds'=>true,
                                'locale'=>[
                                    'format'=>'Y-m-d H:i:s'
                                ],
                                'opens'=>'left'
                            ],
                            'pluginEvents' => [
                                "apply.daterangepicker" => "function() { searchAll(); }",
                            ]
                        ]);
                        ?></li>
                    <li ><?php echo Select2::widget([
                            'name' => 'delivery_select',
                            'id' => 'delivery_select',
                            'data' => $deliveryOptions,
                            'options' => ['placeholder' => '选择配送团长（默认全部团长）'],
                            'language' => 'zh-CN',
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ]);;?></li>
                    <!-- /. tools -->
                    <li ><?php echo Html::button("查询",['onclick'=>'searchAll();','class'=>'btn btn-info']);?></li>
                </ul>
            </div>
        </section>
    </div>
    <!-- Small boxes (Stat box) -->
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h4>总交易额</h4>
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
                    <h4>总下单用户数</h4>
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
                    <h4>产生佣金</h4>
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
                    <h4>毛利</h4>
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

    <div class="row">
        <!-- Left col -->
        <section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
                    <li class="active"><a href="#delivery-day-chart" data-toggle="tab" id="delivery-day-chart-a">交易走势图</a></li>
                    <li class="pull-left header"><i class="fa fa-inbox"></i>走势图</li>
                </ul>
                <div class="tab-content no-padding">
                    <!-- Morris chart - Sales -->
                    <div class="chart tab-pane active" id="delivery-day-chart" style="height: 300px;"></div>
                </div>
            </div>
            <!-- /.nav-tabs-custom -->
        </section>

    </div>
    <!-- Left col -->
    <div class="row">
        <!-- 分类销售占比 -->
        <section class="col-lg-12">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs pull-right">
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
                                    'url'=>'/delivery/dashboard-goods-summary',
                                    'data' => new JsExpression("
                                        function ( d ) {
                                            d.start_date = $('#query_datetime-start').val();
                                            d.end_date =  $('#query_datetime-end').val();
                                            d.delivery_id=$('#delivery_select').select2('val');;
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
                                        'data' => 'need_amount',
                                        'title' => '总金额',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'percentage',
                                        'title' => '占比',
                                        'sClass' => 'active-cell-css-class',
                                    ],
                                    [
                                        'data' => 'gross_amount',
                                        'title' => '毛利',
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

    function initDeliverySummary() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        data.delivery_id=$("#delivery_select").select2("val");
        $.post("/delivery/dashboard-delivery-summary",data,function(response){
            if (response.status===true){
                let data = response.data;
                $('#summary1-h').html(data['need_amount']);

                $('#summary2-h').html(data['customer_cnt']);

                $('#summary3-h').html(data['distribute_delivery_amount']);

                $('#summary4-h').html(data['gross_amount']);
            }
        },'json');
    }
    
    
    function initDeliveryDay() {
        // 基于准备好的dom，初始化echarts实例
        let data={};
        data.start_date=$('#query_datetime-start').val();
        data.end_date=$('#query_datetime-end').val();
        data.delivery_id=$("#delivery_select").select2("val");
        $.post("/delivery/dashboard-delivery-day",data,function(response){
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
                        data:['交易额','下单用户数','佣金金额','毛利']
                    },
                    xAxis: {
                        data: data.time
                    },
                    yAxis: {},
                    series: [
                        {
                            name: '交易额',
                            type: 'bar',
                            data: data.need_amount
                        },
                        {
                            name: '下单用户数',
                            type: 'bar',
                            data: data.customer_cnt
                        },
                        {
                            name: '佣金金额',
                            type: 'bar',
                            data: data.distribute_delivery_amount
                        },
                        {
                            name: '毛利',
                            type: 'bar',
                            data: data.gross_amount
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
    /**
     * 下载首页数据
     */
    function downloadDashboard() {
        let start_date = $('#query_datetime-start').val();
        let end_date = $('#query_datetime-end').val();
        url = 'site/download-dashboard?start_date='+start_date+'&end_date='+end_date;
        window.open(url,'_blank');
        //window.location.href = url;
    }

    function initAll() {
        initDeliverySummary();
        initDeliveryDay();
    }

    function searchAll(){
        initDeliverySummary();
        initDeliveryDay();
        reloadGoodsSummary();
    }
</script>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
initAll();

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_READY); ?>