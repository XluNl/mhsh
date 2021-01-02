<?php

use backend\utils\BackendViewUtil;
use common\models\Tag;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = '标签列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style type="text/css">
    .box-body   th
    {
        text-align:center;
    }
</style>
<div class="container-fluid">

    <div>
        <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
    <div class="row">
        <div class="box box-success">
            <div class="box-body" style="text-align: center">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'options' => [
                        'style'=>'overflow: auto; word-wrap: break-word;'
                    ],
                    'columns' => [
                        [
                            'attribute' => 'group_id',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['group_id'],Tag::$groupArr,Tag::$groupCssArr);
                            },
                        ],
                        [
                            'attribute' => 'tag_id',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['tag_id'],Tag::$tagArr,Tag::$tagCssArr);
                            },
                        ],
                        'biz_id',
                        'biz_name',
                        'biz_ext',
                        [
                            'headerOptions' => ['width' => '180'],
                            'header' => '有效期',
                            'attribute' => 'start_time',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return $data['start_time'].'<br/>'.$data['end_time'];
                            },
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
