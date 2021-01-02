<?php

use yii\base\Model;

$this->context->layout = 'sub';

use common\models\GroupActive;
use common\models\Common;


/* @var Model $model */
?>

<style type="text/css">
    .form-group {
        margin-bottom: 0px;
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="panel panel-default panel-no-border" style="margin-top: 20px;">
            <div class="the-box">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <h4>商品信息</h4>
                    </div>
                    <div class="col-md-6 col-sm-6 col-xs-12 form-group has-feedback "
                         style="display: flex;align-items: center;">
                        <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                            <img src="<?= Common::generateAbsoluteUrl($model['schedule']['goods']['goods_img'] ?? ''); ?>"
                                 width="100px" height="100px">
                        </div>
                    </div>
                    <div class="col-md-9 col-sm-9 col-xs-12 form-group has-feedback "
                         style="display: flex;align-items: center;">
                        <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                            <label>商品名称:</label><label><?= $model['schedule']['goods']['goods_name']; ?></label>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-12 form-group has-feedback ">
                            <label>商品ID:</label><label><?= $model['schedule']['goods']['id']; ?></label>
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback "
                         style="display: flex;align-items: center;">
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                            <label>商品描述:</label><label
                                    id="goods_describe"><?= $model['schedule']['goods']['goods_describe'] ?? '' ?></label>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <h4>拼团规则</h4>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        </div>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>商品规格</th>
                                <th>商品库存</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                                <th>人数</th>
                                <th>价格</th>
                            </tr>
                            </thead>
                            <tbody id="skubody">
                            <tr>
                                <td>
                                    <!--<input disabled type="checkbox" name="" checked class="" style="width: 20px;">-->
                                    <label id="sku_name"><?= $model['schedule']['goodsSku']['sku_name'] ?></label>
                                </td>
                                <td>
                                    <label id="stock"><?= $model['schedule']['schedule_stock'] ?></label>
                                </td>
                                <td>1</td>
                                <td>
                                    <input id="price" type="number" class="form-control" disabled
                                           value="<?= Common::showAmount($model['schedule']['price']); ?>">
                                </td>
                                <td>2</td>
                                <td><input disabled type="number" class="form-control" name="GroupActive[rule_desc][]"
                                           value="<?= $model->rule_desc['price2'] ?? ''; ?>"></td>
                                <td>3</td>
                                <td><input disabled type="number" class="form-control" name="GroupActive[rule_desc][]"
                                           value="<?= $model->rule_desc['price3'] ?? ''; ?>"></td>
                                <td>4</td>
                                <td><input disabled type="number" class="form-control" name="GroupActive[rule_desc][]"
                                           value="<?= $model->rule_desc['price4'] ?? ''; ?>"></td>
                                <td>5</td>
                                <td><input disabled type="number" class="form-control" name="GroupActive[rule_desc][]"
                                           value="<?= $model->rule_desc['price5'] ?? ''; ?>"></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <h4>拼团时间:<?= $model['continued'] ?>分钟</h4>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <h4>活动时间段:<?= $model['schedule']['online_time'] ?>
                            ～ <?= $model['schedule']['offline_time'] ?></h4>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12 form-group has-feedback ">
                        <h4>活动状态:<?= GroupActive::$activeStatus[$model['status']]; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>