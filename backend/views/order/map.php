
<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use backend\assets\LayDateAsset;
use yii\web\JqueryAsset;
LayDateAsset::register($this);
JqueryAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
        body, html{width: 100%;height: 100%;margin:0;font-family:"微软雅黑";}
        #header{
            width: 100%;
            height: 60px;
            background-color: #0075c7;
        }
        #header img{
            height:100%;
        }
        .content {
            position: absolute;
            width: 100%;
            border-bottom: 1px solid #ccc;
            top: 60px;
            bottom: 0;
        }
        .content .menu{
            min-width: 280px;
            float: left;
            border-right: 1px solid #e9e9e9;
            height: 100%;text-align: center;
            padding-top: 30px;
        }
        .content .allmap{
            height: 100%;
            border-left: 1px solid #e9e9e9;
            overflow: hidden;
            height: 100%;
            display: block;
            position: relative;
            zoom: 1;
            z-index: 999;
        }
        #storage_list .active{
            color: #FF0000;;
        }
    </style>
    <script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=shkjkGZWAXagqwa7cGGiPT4rnjgeAPiS"></script>
    <?=Html::jsFile("@web/js/baidumap.js"); ?>
    <title>"总仓"实时地图</title>
</head>

<body>
<?php $this->beginBody() ?>
<div id="header"><?//=Html::img("@web/images/logo-big.png");?></div>
<div class="content">
    <div class="menu">
        <input name="date" class="form-control" id="date"  value="<?=$date;?>" placeholder="选择日期"/>
        <button class="" onclick="get_map_by_date()">点击查看</button>
        <h3>今日下单商家数<strong><?=$restaurantOrderCount;?></strong></h3>
        <hr/>
        <h5>仓库列表</h5>
        <ul id="storage_list" style="text-align:left">
            <?php foreach ($storageModels as $storageModel):?>
                <li id="li-storage-<?=$storageModel['id'];?>" onclick="show_map_by_storage(<?=$storageModel['id'];?>)">
                    <?=$storageModel['name'];?> --(<?=count($storageModel['restaurantModels']);?>)
                </li>
            <?php endforeach;?>
        </ul>
        <hr/>
        <button onclick="download_order('goodsAmount')">商品仓库细分单</button>
        <br/>
        <br/>
        <button onclick="download_order('goodsAmountHB')">商品仓库细分单（合并）</button>
    </div>
    <div id="allmap" class="allmap"></div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_end') ?>
    var mp = new BMap.Map("allmap");

    var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
    var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
    var top_right_navigation = new BMap.NavigationControl({anchor: BMAP_ANCHOR_TOP_RIGHT, type:BMAP_NAVIGATION_CONTROL_SMALL});
    mp.addControl(top_left_control);
    mp.addControl(top_left_navigation);
    mp.addControl(top_right_navigation);
    mp.enableScrollWheelZoom();



    var storagePoint = null;
    var storageMarker = null;

    var activeStorageId = 0;
    var pointArray = new Array();
    var overlayArray = new Array();
    var storageModels = <?=Json::encode($storageModels);?>;
    show_map_by_storage(0);
    function show_map_by_storage(storageId){
        $("#li-storage-"+activeStorageId).removeClass("active");
        $("#li-storage-"+storageId).addClass("active");
        activeStorageId = storageId;

        storagePoint = new BMap.Point(storageModels[storageId]['lng'],storageModels[storageId]['lat']);
        mp.centerAndZoom(storagePoint, 12);
        if (storageMarker != null){
            mp.removeOverlay(storageMarker);
        }
        storageMarker = new BMap.Marker(storagePoint);
        mp.addOverlay(storageMarker);

        for(var i = 0;i<overlayArray.length;i++){
            mp.removeOverlay(overlayArray[i]);
        }
        var j = 0;
        pointArray = new Array();
        overlayArray = new Array();
        var storageModel = storageModels[storageId];
        var restaurantModels = storageModel['restaurantModels'];
        for (id in restaurantModels){
            var restaurantModel = restaurantModels[id];
            var name = restaurantModel['name'];
            var address = restaurantModel['address'];
            var lng = restaurantModel['lng'];
            var lat = restaurantModel['lat'];
            var point = new BMap.Point(lng, lat);
            pointArray[j] = point;
            var txt = name, mouseoverTxt = txt + ";地址：" + address ;
            var myCompOverlay = new ComplexCustomOverlay(point, txt,mouseoverTxt);
            overlayArray[j] = myCompOverlay;
            j++;
            mp.addOverlay(myCompOverlay);
        }
        pointArray.push(storagePoint);
        mp.setViewport(pointArray);

    }
    function download_order(kind){
        var date = document.getElementById("date").value;
        var url = "<?=Url::toRoute(['order/download']);?>";
        window.location.href=url+"?date="+date+"&kind="+kind;
    }
    laydate.render({elem: '#date'});
    function get_map_by_date(){
        var date = document.getElementById("date").value;
        var url = "<?=Url::toRoute(['order/map']);?>";
        window.location.href=url+"?date="+date;
    }
    <?php $this->endBlock()?>
    </script>
    <?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>