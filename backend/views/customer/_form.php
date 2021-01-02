<?php

use backend\services\RegionService;
use yii\helpers\Html;
use kartik\builder\Form;
use kartik\builder\FormGrid;
use backend\models\BackendCommon;
use kartik\form\ActiveForm;
/* @var $this yii\web\View */
/* @var $model common\models\Customer */
/* @var $form yii\widgets\ActiveForm */
?>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=shkjkGZWAXagqwa7cGGiPT4rnjgeAPiS"></script>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-8 col-xs-offset-2">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="page-heading">用户添加或修改</h3>
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
                                'nickname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入昵称...'],'columnOptions'=>['colspan'=>4]],
                                'realname'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入姓名...'],'columnOptions'=>['colspan'=>4]],
                                'phone'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入手机号...'],'columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                        [
                            'columns'=>12,
                            'autoGenerateColumns'=>false, // override columns setting
                            'attributes'=>[       // 3 column layout
                                'province_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById(0),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择省份',
                                        'onchange'=>'
                                $.get("/region/region?id='.'"+$(this).val(),function(data){             
                                     $("#customer-city_id").html("<option value=>请选择城市</option>");
                                     $("#customer-county_id").html("<option value=>请选择地区</option>");
                                     $("#customer-city_id").append(data);
                                });'
                                    ]],
                                'city_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById($model->province_id),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择城市',
                                        'onchange'=>'
                                $.get("/region/region?id='.'"+$(this).val(),function(data){             
                                     $("#customer-county_id").html("<option value=>请选择地区</option>");
                                     $("#customer-county_id").append(data);
                                });'
                                    ]],
                                'county_id'=>['type'=>Form::INPUT_DROPDOWN_LIST,
                                    'items'=>RegionService::getRegionById($model->city_id),
                                    'columnOptions'=>['colspan'=>2],
                                    'options'=>[
                                        'style'=>'display:inline',
                                        'prompt'=>'请选择地区',
                                    ]],
                                'address'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入具体地址...'],'columnOptions'=>['colspan'=>6]],
                            ]
                        ],
                        [
                            'columns'=>12,
                            'autoGenerateColumns'=>false, // override columns setting
                            'attributes'=>[       // 3 column layout
                                'status'=>['type'=>Form::INPUT_DROPDOWN_LIST, 'items'=>BackendCommon::addBlankOption(\common\models\Customer::$StatusArr), 'placeholder'=>'选择...','columnOptions'=>['colspan'=>4]],
                                'lat'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入纬度...'],'columnOptions'=>['colspan'=>4]],
                                'lng'=>['type'=>Form::INPUT_TEXT, 'options'=>['placeholder'=>'输入经度...'],'columnOptions'=>['colspan'=>4]],
                            ]
                        ],
                    ]
                ]);

                ?>
                    <div class="form-group">
                        <?= Html::Button('经纬度转位置',['onclick'=>'getAddress();','class' => 'btn btn-info']);?>
                        <?= Html::Button('获取当前位置',['onclick'=>'getCurrent();','class' => 'btn btn-success']);?>
                    </div>
                    <div id="map" style="height:500px"></div>
                    <div class="form-group">
                        <?= Html::submitButton($model->isNewRecord ?'新增':'修改', ['class' => 'col-xs-offset-1 col-xs-4 btn btn-primary btn-lg']) ?>
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
    let marker = null;
    let map = new BMap.Map("map");
    let geoc = new BMap.Geocoder();
    var initPoint = new BMap.Point(119.9877088046,29.2137124648);
    map.centerAndZoom(initPoint,12);
    map.enableScrollWheelZoom();   //启用滚轮放大缩小，默认禁用
    map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用

    map.addEventListener("click",function(e){
        //alert(e.point.lng + "," + e.point.lat);
        if (marker!=null){
            map.removeOverlay(marker);
        }
        point = new BMap.Point(e.point.lng,e.point.lat);
        marker = new BMap.Marker(point);
        map.addOverlay(marker);
        $('#customer-lng').val(e.point.lng);
        $('#customer-lat').val(e.point.lat);
    });
    function getAddress(){
        if($('#customer-lng').val()==""||$('#customer-lat').val()==""){
            alert("请先输入经纬度");
            return false;
        }
        var pt = new BMap.Point($('#customer-lng').val(),$('#customer-lat').val());
        geoc.getLocation(pt, function(rs){
            var addComp = rs.addressComponents;
            //alert(addComp.province + ", " + addComp.city + ", " + addComp.district + ", " + addComp.street + ", " + addComp.streetNumber);
            $('#customer-address').val(addComp.city+ addComp.district+ addComp.street+ addComp.streetNumber);
            console.log(addComp);
        });
        return false;
    }

    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function(r){
        if(this.getStatus() == BMAP_STATUS_SUCCESS){
            if (marker!=null){
                map.removeOverlay(marker);
            }
            marker = new BMap.Marker(r.point);
            map.addOverlay(marker);
            map.panTo(r.point);
            //alert('您的位置：'+r.point.lng+','+r.point.lat);
            console.log('您的位置：'+r.point.lng+','+r.point.lat);
        }
        else {
            alert('failed'+this.getStatus());
        }
    },{enableHighAccuracy: true});

    function getCurrent() {
        geolocation.getCurrentPosition(function(r){
            if(this.getStatus() == BMAP_STATUS_SUCCESS){
                if (marker!=null){
                    map.removeOverlay(marker);
                }
                marker = new BMap.Marker(r.point);
                map.addOverlay(marker);
                map.panTo(r.point);
                //alert('您的位置：'+r.point.lng+','+r.point.lat);
                console.log('您的位置：'+r.point.lng+','+r.point.lat);
                $('#customer-lng').val(r.point.lng);
                $('#customer-lat').val(r.point.lat);
                getAddress();
            }
            else {
                alert('failed'+this.getStatus());
            }
        },{enableHighAccuracy: true});
    }

<?php $this->endBlock()?>
</script>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>

