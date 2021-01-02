<?php

use common\models\Common;
use common\models\CustomerInvitation;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\grid\GridView;
use yii\helpers\Json;

/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/02/04/004
 * Time: 1:19
 */


if (!empty($preStatisticModel['children'])){
    $childrenModels = Json::decode($preStatisticModel['children']);
    $provider = new ArrayDataProvider([
        'allModels' => $childrenModels,
        'pagination' => new Pagination(['pageSize' => 200])
    ]);
    echo GridView::widget([
        'dataProvider' => $provider,
        //'layout'=>"{items}",
        //'showHeader' => false,
        'tableOptions'=>['class' => 'table table-condensed table-bordered'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'header' => '客户姓名',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['child_customer_name'];
                },
            ],
            [
                'header' => '客户手机号',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['child_customer_phone'];
                },
            ],
            [
                'header' => '邀请时间',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['child_customer_invite_time'];
                },
            ],
            [
                'header' => '下单数量',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data['child_customer_order_count'];
                },
            ],
            [
                'header' => '下单总金额',
                'format' => 'raw',
                'value' => function ($data) {
                    return Common::showAmountWithYuan($data['child_customer_order_amount']) ;
                },
            ],
            [

                'header' => '下级有效邀请人数',
                'attribute' => 'invitation_count',
                'value' => function ($data) {
                    return $data['invitation_count'];
                },
            ],
            [

                'header' => '下级有效已下单人数',
                'attribute' => 'invitation_order_count',
                'value' => function ($data) {
                    return $data['invitation_order_count'];
                },
            ],
            [
                'header' => '下级邀请',
                'format' => 'raw',
                'value' => function ($data) {
                    $str= "";
                    if (!empty($data['children'])){
                        foreach ($data['children'] as $v){
                            if (CustomerInvitation::IS_CONNECT_TRUE!=$v['child_customer_is_connect']){
                                $str = $str.'已断连--';
                            }
                            $str = $str."{$v['child_customer_name']}/{$v['child_customer_phone']}/{$v['child_customer_invite_time']}/{$v['child_customer_order_count']}/". Common::showAmountWithYuan($v['child_customer_order_amount']).'<br/>';
                        }
                    }
                    return $str ;
                },
            ],
        ],
    ]);
}

