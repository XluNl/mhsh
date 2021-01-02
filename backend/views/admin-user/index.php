<?php

use backend\models\BackendCommon;
use backend\utils\BackendViewUtil;
use common\models\AdminUser;
use common\utils\DateTimeUtils;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\searches\AdminUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '登录账户列表';
$this->params['breadcrumbs'][] = ['label' => '代理商列表', 'url' => ['/company/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-index">

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="row">
        <div class="box box-success">
            <div class="box-header with-border">
                <?= Html::a('新增账户', ['modify'], ['class' => 'btn btn-info btn-lg']) ?>
            </div>
            <div class="box-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        'company_name',
                        'username',
                        'nickname',
                        'email',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['status'],AdminUser::$status_arr,AdminUser::$statusCssArr);
                            },
                        ],
                        [
                            'attribute' => 'is_super_admin',
                            'format' => 'raw',
                            'value' => function ($data) {
                                return BackendViewUtil::getArrayWithLabel($data['is_super_admin'],AdminUser::$superAdminArr,AdminUser::$superAdminCssArr);
                            },
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function ($data) {
                                return DateTimeUtils::parseStandardWLongDate($data['created_at']);
                            },
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => function ($data) {
                                return DateTimeUtils::parseStandardWLongDate($data['updated_at']);
                            },
                        ],
                        [
                            'header' => '操作',
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{modify}{enable}{disable}{assign}{super-admin}{add-user}',
                            'headerOptions' => ['width' => '290'],
                            'buttons' =>[
                                'modify' => function ($url, $model, $key) {
                                    return BackendViewUtil::generateOperationATag("修改",['/admin-user/modify','id'=>$model['id'],'company_id'=>$model['company_id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'enable' => function ( $url, $model, $key) {
                                    if ($model['status']==AdminUser::STATUS_ACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("启用",['/admin-user/status','id'=>$model['id'],'commander'=>AdminUser::STATUS_ACTIVE],'btn btn-xs btn-success','fa fa-arrow-up',"确认启用？");
                                },
                                'disable' => function ( $url, $model, $key) {
                                    if ($model['status']==AdminUser::STATUS_INACTIVE){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("禁用",['/admin-user/status','id'=>$model['id'],'commander'=>AdminUser::STATUS_INACTIVE],'btn btn-xs btn-warning','fa fa-arrow-down',"确认禁用？");
                                },
                                'assign' => function ($url, $model, $key) {
                                    if ($model['is_super_admin'] == AdminUser::SUPER_ADMIN_YES){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("分配权限",['/assignment/view','id'=>$model['id']],'btn btn-xs btn-primary','fa fa-pencil-square-o');
                                },
                                'super-admin' => function ($url, $model, $key) {
                                    if ($model['is_super_admin'] == AdminUser::SUPER_ADMIN_YES){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("设为超管",['/admin-user/super-admin','id'=>$model['id']],'btn btn-xs btn-info','fa fa-pencil-square-o');
                                },
                                'add-user' => function ($url, $model, $key) {
                                    if (!BackendCommon::isSuperCompany(BackendCommon::getFCompanyId())){
                                        return "";
                                    }
                                    return BackendViewUtil::generateOperationATag("为代理商新增账户",['/admin-user/modify','company_id'=>$model['company_id']],'btn btn-xs btn-warning','fa fa-pencil-square-o');
                                },
                            ],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
