<?php

use common\models\Common;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/02/04/004
 * Time: 1:19
 */


if (!empty($oneLevelModel['children'])){
    $twoLevelModels = $oneLevelModel['children'];
    $provider = new ArrayDataProvider([
        'allModels' => $twoLevelModels
    ]);
    echo GridView::widget([
        'dataProvider' => $provider,
        //'layout'=>"{items}",
        //'showHeader' => false,
        'tableOptions'=>['class' => 'table table-condensed table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'header' => '客户头像',
                'format' => [
                    'image',
                    [
                        'onerror' => 'ifImgNotExists(this)',
                        'class' => 'img-circle',
                        'width'=>'40',
                        'height'=>'40'
                    ]
                ],
                'value' => function ($data) {
                    return $data['head_img_url'];
                },
            ],
            [
                'header' => '客户姓名',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['name'];
                },
            ],
            [
                'header' => '客户手机号',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['phone_org'];
                },
            ],
            [
                'header' => '客户等级',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['level_text'];
                },
            ],
            [
                'header' => '预估佣金/实际佣金',
                'format' => 'raw',
                'value' => function ($data) {
                    return Common::showAmountWithYuan($data['amount']).'/'.Common::showAmountWithYuan($data['amount_ac']);
                },
            ],
            [
                'header' => '订单金额',
                'format' => 'raw',
                'value' => function ($data) {
                    return Common::showAmountWithYuan($data['order_amount']);
                },
            ],
            [
                'header' => '订单数量',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['order_count'];
                },
            ],
        ],
    ]);
}

