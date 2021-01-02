<?php

/* @var $this yii\web\View
 * @var $errorData array
 */

use backend\assets\BootstrapTreeviewAsset;
use common\utils\ArrayUtils;

BootstrapTreeviewAsset::register($this);
$title = "导入结果";
$this->params['subtitle'] = $title;
$this->params['breadcrumbs'][] = $title;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-success box-solid">
                <div class="box-header with-border">
                    导入结果
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th>#</th>
                            <th>Excel行号</th>
                            <th>商品</th>
                            <th>规格</th>
                            <th>错误原因</th>
                        </tr>
                        <?php foreach ($errorData as $k => $data): ?>
                        <tr>
                            <td><?=$k+1;?></td>
                            <td><?=$data['rowNo'];?></td>
                            <td><?=ArrayUtils::getArrayValue('goods_name',$data);?></td>
                            <td><?=ArrayUtils::getArrayValue('sku_name',$data);?></td>
                            <td><?=$data['error'];?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // 首页设置框
    $('#home_setting').hide();
    // 首页设置框结束
</script>