<?php


namespace backend\models\constants;


class IndexStatisticConstants
{

    public static $deliverySummaryEveryDayHeader = [
        'time_text'=>[
            'title'=>'日期',
            'cols'=>2,
        ],
        'delivery_cnt'=>[
            'title'=>'配送团长下单数',
            'cols'=>2,
        ],
        'popularizer_cnt'=>[
            'title'=>'分享团长下单数',
            'cols'=>2,
        ],
        'distribute_delivery_amount'=>[
            'title'=>'配送团长佣金',
            'cols'=>2,
        ],
        'distribute_popularizer_amount'=>[
            'title'=>'分享团长佣金',
            'cols'=>2,
        ],
    ];


    public static $orderSummaryEveryDayHeader = [
        'time_text'=>[
            'title'=>'日期',
            'cols'=>2,
        ],
        'need_amount'=>[
            'title'=>'销售额',
            'cols'=>2,
        ],
        'order_count'=>[
            'title'=>'订单数',
            'cols'=>2,
        ],
        'discount_amount'=>[
            'title'=>'优惠金额',
            'cols'=>2,
        ],
        'customer_service_count'=>[
            'title'=>'售后订单',
            'cols'=>2,
        ],
    ];

    public static $goodsSummaryHeader = [
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>4,
        ],
        'num'=>[
            'title'=>'销量',
            'cols'=>2,
        ],
        'percentage'=>[
            'title'=>'占比',
            'cols'=>2,
        ],
    ];


    public static $deliverySummaryHeader = [
        'delivery_name'=>[
            'title'=>'团长名',
            'cols'=>2,
        ],
        'amount'=>[
            'title'=>'销售金额',
            'cols'=>2,
        ],
        'percentage'=>[
            'title'=>'占比',
            'cols'=>2,
        ],
    ];

    public static $orderDeliveryDayHeader = [
        'time_text'=>[
            'title'=>'日期',
            'cols'=>2,
        ],
        'customer_count'=>[
            'title'=>'下单用户',
            'cols'=>2,
        ],
        'order_amount'=>[
            'title'=>'客单价',
            'cols'=>2,
        ],
        'delivery_count'=>[
            'title'=>'新增配送团长',
            'cols'=>2,
        ],
        'popularizer_count'=>[
            'title'=>'新增分享团长',
            'cols'=>2,
        ],
    ];

}