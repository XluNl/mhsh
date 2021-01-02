<?php
use kartik\grid\GridView;
use \yii\bootstrap\Html;
use \common\models\Common;
$this->context->layout = 'sub';
?>
<style type="text/css">
    .btn-info{
        margin-left: 10px;
    }
</style>
<div class="container-fluid">
    <div style="margin-left: -15px;margin-right: -15px;">
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border" style="display: flex;flex-direction: row-reverse;margin-bottom: 10px;">
                <?= Html::a('清除3天前数据', ['clear','tag'=>1,'query'=>$params], ['class' => 'btn btn-info']) ?>
                <?= Html::a('仅保留今日数据', ['clear','tag'=>2,'query'=>$params], ['class' => 'btn btn-info']) ?>
            </div>
            <div class="box-body" style="text-align: center;">
                <?= GridView::widget([
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;font-size: 13px;'
                    ],
                    'layout'=>"{items}\n{pager}",
                    'tableOptions' => ['style' => 'font-size:12px'],
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['attribute' => 'id','options' => ['style' => 'width:3%']],
                        ['attribute' => 'env','options' => ['style' => 'width:3%']],
                        ['attribute' => 'app_id', 'options' => ['style' => 'width:4%']],
                        ['attribute' => 'module', 'options' => ['style' => 'width:4%']],
                        ['attribute' => 'controller', 'options' => ['style' => 'width:4%']],
                        ['attribute' => 'action', 'options' => ['style' => 'width:4%']],
                        [
                            'attribute' => 'request',
                            'options' => ['style' => 'width:25%'],
                            'contentOptions' => [
                                'style'=>'word-wrap:break-word;word-break:break-all'
                            ],
                            'format' => 'html',
                            'value'=>function($data){
                                $tmpdata = json_decode($data['request'],true);
                                $url = $tmpdata['url'];
                                $parmes = $tmpdata['params'];
                                return json_encode($parmes).'<br/>'."<a href=javascript:void(0);  target='view_window' >$url<a/>";
                            }
                        ],
                        [
                            'attribute' => 'response',
                            'format'=>'raw',
                            'options' => ['style' => 'width:45%'],
                            'contentOptions' => [
                                'style'=>'word-wrap:break-word;word-break:break-all'
                            ]
                        ],
                        [
                            'attribute' => 'created_at',
                            'options' => ['style' => 'width:10%'],
                            'value' => function($data){
                                return date('Y-m-d H:i:s',$data->created_at);
                            }
                        ]
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>