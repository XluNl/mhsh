<?php

use backend\utils\BackendViewUtil;
use common\models\CommonStatus;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\CompanySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '代理商列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?= Html::a('新增代理商', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'name',
                        'contact',
                        'email',
                        'telphone',
                        'office_phone',
                        'fax',
                        'address',
                        'zip_code',
                        'created_at',
                        'updated_at',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],CommonStatus::$StatusArr,CommonStatus::$StatusCssArr);
                            },
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{modify}{enable}{disable}{users}{addUser}',
                            'headerOptions' => ['width' => '290'],
                            'buttons' =>[
                                'modify' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/company/modify','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'enable' => function ( $url, $model, $key) {
                                    if ($model['status']==CommonStatus::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/company/status','id'=>$model['id'],'commander'=>CommonStatus::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-arrow-up',"确认启用？");
                                },
                                'disable' => function ( $url, $model, $key) {
                                    if ($model['status']==CommonStatus::STATUS_DISABLED){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("禁用",['/company/status','id'=>$model['id'],'commander'=>CommonStatus::STATUS_DISABLED],'btn btn-xs btn-danger','fa fa-arrow-down',"确认禁用？");
                                },
                                'users' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("用户列表",['/admin-user/index','AdminUserSearch[company_id]'=>$model['id']],'btn btn-xs btn-info','fa fa-pencil-square-o');
                                },
                                'addUser' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("新增用户",['/admin-user/modify','company_id'=>$model['id']],'btn btn-xs btn-default','fa fa-pencil-square-o');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
