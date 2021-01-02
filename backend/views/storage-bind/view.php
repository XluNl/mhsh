<?php

/* @var $this yii\web\View */
/* @var $model common\models\StorageBind */

$this->title = '仓库信息';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    <h3 class="page-heading">已绑定仓库</h3>
                </div>
                <div class="box-body">
                    <h2><?= $model['storage_name']?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
