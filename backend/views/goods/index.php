<?php

use backend\models\BackendCommon;
use backend\utils\BackendViewUtil;
use backend\utils\BStatusCode;
use common\models\GoodsConstantEnum;
use kartik\select2\Select2;
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\web\JsExpression;use yii\widgets\Pjax;

/* @var  array $bigSortArr */
/* @var  array $smallSortArr */
/* @var  backend\models\searches\GoodsSearch $searchModel */
/* @var  $dataProvider */
?>
<style type="text/css">
    .panel-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">
    <h1 class="page-heading">商品列表</h1>
    <div class="">
        <p>
            <?= Html::a('添加新商品','/goods/modify',['class'=>'btn btn-primary']) ?>
            <?= Html::a('导入更新商品','/goods/import',['class'=>'btn btn-info']) ?>
        </p>
    </div>

    <?php  echo $this->render('_search', ['model' => $searchModel,'bigSortArr'=>$bigSortArr,'smallSortArr' => $smallSortArr]); ?>

    <div class="panel with-nav-tabs panel-primary">
        <?php  echo $this->render('big-sort-filter', ['bigSortId' => $searchModel->sort_1,'bigSortArr' => $bigSortArr]); ?>
        <div class="panel-body" style="text-align: center">
            <?php  echo $this->render('small-sort-filter', ['smallSortId' => $searchModel->sort_2,'smallSortArr' => $smallSortArr]); ?>
            <?php Pjax::begin(['id' => 'datalist']);?>
                <?php if (!empty($smallSortArr)): ?>
                    <div class="col-xs-10">
                <?php else: ?>
                    <div class="col-xs-12">
                <?php endif;?>
                    <?=GridView::widget([
                        'dataProvider' => $dataProvider,
                        'tableOptions'=>['class' => 'table table-bordered'],
                        'rowOptions' => function($model, $key, $index, $grid) {
                            return ['style'=>'background:rgb(238, 238, 238)'];
                        },
                        'columns' => [
                            'id',
                            'goods_name',
                            [
                                'attribute' => 'goods_img',
                                'format' => [
                                    'image',
                                    [
                                        'onerror' => 'ifImgNotExists(this)',
                                        'class' => 'img-circle',
                                        'width'=>'40',
                                        'height'=>'40'
                                    ]
                                ],
                                'value' => function ($model) {
                                    return $model->goods_img;
                                },
                            ],
                            'goods_describe',
                            [
                                'attribute' => 'goods_status',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return BackendViewUtil::getArrayWithLabel($model->goods_status,GoodsConstantEnum::$statusArr,GoodsConstantEnum::$statusCssArr);
                                },
                            ],
                            'display_order',
                            [
                                'header' => '商品操作',
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{update}|{delete}{detail}{video}{channel}{goodsUp}{goodsDown}|{addSku}{audit}',
                                //'headerOptions' => ['width' => '152'],
                                'buttons' =>[
                                    'update' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("修改",['/goods/modify','goods_id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                    },
                                    'delete' => function ( $url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("删除",['/goods/operation','goods_id'=>$model->id,'commander'=>GoodsConstantEnum::STATUS_DELETED],'btn btn-xs btn-danger','fa fa-trash',"确认删除？");
                                    },
                                    'detail' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("详情页",['/goods/detail','goods_id'=>$model['id']],'btn btn-xs btn-success','fa fa-pencil-square-o');
                                    },
                                    'video' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("视频",['/goods/video','goods_id'=>$model['id']],'btn btn-xs btn-primary','fa fa-file-video-o');
                                    },
                                    'channel' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("渠道",['/goods/sold-channel','goods_id'=>$model['id']],'btn btn-xs btn-info','fa fa-pencil-square-o');
                                    },
                                    'goodsUp' => function ($url, $model, $key) {
                                        if ($model->goods_status==GoodsConstantEnum::STATUS_UP){
                                            return "";
                                        }
                                        return BackendViewUtil::generateOperationATag("上架",['/goods/operation','goods_id'=>$model->id,'commander'=>GoodsConstantEnum::STATUS_UP],'btn btn-xs btn-danger','fa fa-cloud-upload',"确认上架？");
                                    },
                                    'goodsDown' => function ($url, $model, $key) {
                                        if ($model->goods_status==GoodsConstantEnum::STATUS_DOWN){
                                            return "";
                                        }
                                        return BackendViewUtil::generateOperationATag("下架",['/goods/operation','goods_id'=>$model->id,'commander'=>GoodsConstantEnum::STATUS_DOWN],'btn btn-xs btn-warning','fa fa-cloud-download',"确认下架？");
                                    },
                                    'addSku' => function ($url, $model, $key) {
                                        return BackendViewUtil::generateOperationATag("属性",['/goods-sku/modify','goods_id'=>$model['id']],'btn btn-xs btn-info','fa fa-plus');
                                    },
                                    'audit' => function ($url, $model, $key) {
                                        if ($model->goods_owner!=GoodsConstantEnum::OWNER_HA){
                                            return "";
                                        }
                                        return BackendViewUtil::generateOperationATag("审核",['/goods-sku-alliance/index','GoodsSkuAllianceSearch[goods_id]'=>$model['id']],'btn btn-xs btn-default','fa fa-plus');
                                    }
                                ],
                            ],

                        ],
                        'afterRow'=>function($model,$key, $index,$grid){
                            return \Yii::$app->view->render("_item", ['model'=>$model]);
                        }
                    ]);?>
                </div>
                <?php Pjax::end();?>
            </div>
        </div>
    </div>
</div>
<div id="storageSkuBind" class="fade modal" role="dialog" >
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">绑定</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-10 col-xs-offset-1">
                        <div class="box ">
                            <div class="box-body">
                                <div class="form-horizontal">
                                    <input id="storageSkuBindSkuId" type="hidden"/>
                                    <input id="storageSkuBindGoodsId" type="hidden"/>
                                    <div class="form-group">
                                        <label class="control-label col-lg-4" for="storageSkuBindStorageSort">仓库分类</label>
                                        <div class="col-lg-8">
                                            <?php echo Select2::widget([
                                                'name' => 'storageSkuBindStorageSort',
                                                'id' => 'storageSkuBindStorageSort',
                                                'data' => [''=>'全部分类'],
                                                'options' => ['placeholder' => '选择仓库分类'],
                                                'language' => 'zh-CN',
                                                'pluginOptions' => [
                                                    'ajax'=>[
                                                        'url'=>['/storage-sku-mapping/sort-search'],
                                                        'dataType'=>'json',
                                                        'delay'=>250,
                                                        'data'=>new JsExpression("
                                                            function (params) {
                                                                return {
                                                                  storageSkuName: params.term,
                                                                };
                                                         }"),
                                                        'processResults'=>new JsExpression("
                                                            function (data) {
                                                                return {
                                                                  results: data.data
                                                                };
                                                         }"),
                                                        'cache'=>true,
                                                    ],
                                                ],
                                            ]);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-4" for="storageSkuBindStorageSkuIdSelect">仓库商品</label>
                                        <div class="col-lg-8">
                                            <?php echo Select2::widget([
                                                'name' => 'storageSkuBindStorageSkuIdSelect',
                                                'id' => 'storageSkuBindStorageSkuIdSelect',
                                                'data' => [],
                                                'options' => ['placeholder' => '选择仓库商品'],
                                                'language' => 'zh-CN',
                                                'pluginOptions' => [
                                                    'ajax'=>[
                                                        'url'=>['/storage-sku-mapping/sku-search'],
                                                        'dataType'=>'json',
                                                        'delay'=>250,
                                                        'data'=>new JsExpression("
                                                            function (params) {
                                                                return {
                                                                  storageSkuName: params.term,
                                                                  storageSortId: $('#storageSkuBindStorageSort option:checked').val(),
                                                                };
                                                         }"),
                                                        'processResults'=>new JsExpression("
                                                            function (data) {
                                                                if(data.status==false){
                                                                    showStorageSkuError(data);
                                                                    return {
                                                                      results: []
                                                                    };
                                                                }
                                                                return {
                                                                  results: data.data
                                                                };
                                                         }"),
                                                        'cache'=>true,
                                                    ],
                                                    //'minimumInputLength'=>1,
                                                ],
                                            ]);?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-4" for="storageSkuBindStorageSkuNum">售卖:仓库(1:N)</label>
                                        <div class="col-lg-8">
                                            <input required id="storageSkuBindStorageSkuNum" class="form-control"
                                                   name="storageSkuBindStorageSkuNum"/></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-lg-4" for="storageSkuBindStorageExpectSoldNum">预计售卖仓库数量</label>
                                        <div class="col-lg-8">
                                            <input required id="storageSkuBindStorageExpectSoldNum" class="form-control"
                                                   name="storageSkuBindStorageExpectSoldNum"/></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" onclick="storageSkuBindSubmit();">提交</button>
            </div>
        </div>
    </div>
</div>

<?php $this->beginBlock('js_end') ?>
    function showStorageSkuError(data) {
        if (data.code!==<?=BStatusCode::STORAGE_UN_BIND?>){
            bootbox.alert(data.error);
            return;
        }
        bootbox.dialog({
            title: '绑定仓库',
            message: data.error,
            size: 'large',
            buttons: {
                ok: {
                    label: "前去绑定",
                    className: 'btn-info',
                    callback: function(){
                        window.location.href="<?=Url::toRoute(['/storage-bind/index']);?>";
                    }
                }
            }
        });
    }

    $("."+"storageSkuBind").click(function(){
        let storageSkuBindSkuId= $(this).attr("data-storageSkuBindSkuId");
        $('#storageSkuBindSkuId').val(storageSkuBindSkuId);
        let storageSkuBindGoodsId= $(this).attr("data-storageSkuBindGoodsId");
        $('#storageSkuBindGoodsId').val(storageSkuBindGoodsId);
        let storageSkuBindStorageSkuNum= $(this).attr("data-storageSkuBindStorageSkuNum");
        $('#storageSkuBindStorageSkuNum').val(storageSkuBindStorageSkuNum);

        $("#storageSkuBind").modal("show");

        let storageSkuBindStorageSkuName= $(this).attr("data-storageSkuBindStorageSkuName");
        let storageSkuBindStorageSkuId= $(this).attr("data-storageSkuBindStorageSkuId");
        if (storageSkuBindStorageSkuId!=null){
            $("#storageSkuBindStorageSkuIdSelect").html("<option value='"+storageSkuBindStorageSkuId+"'>"+storageSkuBindStorageSkuName+"</option>").trigger("select2:select");
        }
        else{
            $("#storageSkuBindStorageSkuIdSelect").html("").trigger("select2:unselect");
        }
        return false;
    });
    function storageSkuBindSubmit() {
        let storageSkuBindSkuId = $('#storageSkuBindSkuId').val();
        let storageSkuBindGoodsId = $('#storageSkuBindGoodsId').val();

        let storageSkuBindStorageSkuNum = $('#storageSkuBindStorageSkuNum').val();
        if(!isRealNum(storageSkuBindStorageSkuNum)){
            bootbox.alert('售卖:仓库(1:N)必须是数字');
            return;
        }
        let storageSkuBindStorageSkuId = $('#storageSkuBindStorageSkuIdSelect option:checked').val();
        if(storageSkuBindStorageSkuId==undefined){
            bootbox.alert('仓库商品必选');
            return;
        }
        let storageExpectSoldNum = $('#storageSkuBindStorageExpectSoldNum').val();
        if(!isRealNum(storageExpectSoldNum)){
            bootbox.alert('预计售卖仓库数量必须是数字');
            return;
        }
        $.getJSON(`/storage-sku-mapping/bind?skuId=${storageSkuBindSkuId}&goodsId=${storageSkuBindGoodsId}&storageSkuNum=${storageSkuBindStorageSkuNum}&storageSkuId=${storageSkuBindStorageSkuId}&expectSoldNum=${storageExpectSoldNum}`,function(data){
            if(data.status===true){
                bootbox.alert('绑定成功');
                $("#storageSkuBind").modal("hide");
                window.location.reload();
            }
            else
            {
                bootbox.alert(data.error);
            }
        });
    }
    function isRealNum(val){
        // isNaN()函数 把空串 空格 以及NUll 按照0来处理 所以先去除，

        if(val === "" || val ==null){
            return false;
        }
        if(!isNaN(val)){
            //对于空数组和只有一个数值成员的数组或全是数字组成的字符串，isNaN返回false，例如：'123'、[]、[2]、['123'],isNaN返回false,
            //所以如果不需要val包含这些特殊情况，则这个判断改写为if(!isNaN(val) && typeof val === 'number' )
            return true;
        }

        else{
            return false;
        }
    }
<?php $this->endBlock()?>
<?php $this->registerJs($this->blocks['js_end'], \yii\web\View::POS_END); ?>
