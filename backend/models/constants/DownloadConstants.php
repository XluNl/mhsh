<?php


namespace backend\models\constants;


class DownloadConstants
{
    /**
     * 计算金额
     */
    const CELL_TYPE_MONEY = 1;
    /**
     * 计算金额（带“元”）
     */
    const CELL_TYPE_MONEY_WITH_YUAN = 2;
    /**
     * 百分比
     */
    const CELL_TYPE_PERCENTAGE = 3;
    /**
     * 百分比（带“%”）
     */
    const CELL_TYPE_PERCENTAGE_WITH_SYMBOL = 4;
    public static $purchaseList = [
        'big_sort_name'=>[
            'title'=>'大类',
        ],
        'small_sort_name'=>[
            'title'=>'小类',
        ],
        'no'=>[
            'title'=>'序号',
        ],
        'franchisees'=>[
            'title'=>'加盟商-代理商',
        ],
        'supplier'=>[
            'title'=>'供应商',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>3,
        ],
        'sku_name'=>[
            'title'=>'规格',
        ],
        'sku_unit'=>[
            'title'=>'商品单位',
        ],
        'sold_quantity'=>[
            'title'=>'售卖数量',
        ],
        'stock_quantity'=>[
            'title'=>'库存数量',
        ],
        'purchase_quantity'=>[
            'title'=>'待采购数量',
        ],
        'sku_price'=>[
            'title'=>'售价',
        ],
        'total_amount'=>[
            'title'=>'总价',
        ],
        'remark'=>[
            'title'=>'备注',
        ],
    ];

    public static $orderList=[
        'no'=>[
            'title'=>'序号',
        ],
        'accept_name'=>[
            'title'=>'用户名',
        ],
        'accept_mobile'=>[
            'title'=>'电话',
        ],
        'address'=>[
            'title'=>'地址',
        ],
        'goods_name'=>[
            'title'=>'商品',
        ],
        'sku_name'=>[
            'title'=>'规格',
        ],
        'num'=>[
            'title'=>'数量',
        ],
    ];

    public static $sortingList=[
        'sort_1_name'=>[
            'title'=>'一级分类',
        ],
        'goods_name'=>[
            'title'=>'名称',
            'cols'=>2,
        ],
        'sku_name'=>[
            'title'=>'规格',
            'cols'=>2,
        ],
        'sku_unit'=>[
            'title'=>'单位',
        ],
        'sold_amount'=>[
            'title'=>'数量',
        ],
        'sku_price'=>[
            'title'=>'价格',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'remark'=>[
            'title'=>'备注',
        ],
    ];

    public static $orderGoodsList=[
        'no'=>[
            'title'=>'序号',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>3,
        ],
        'sku_name'=>[
            'title'=>'规格',
        ],

        'sku_price'=>[
            'title'=>'商品单价',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'num'=>[
            'title'=>'商品数量',
        ],
        'sku_unit'=>[
            'title'=>'商品单位',
        ],
        'need_amount'=>[
            'title'=>'总价',
            'type'=>self::CELL_TYPE_MONEY
        ],
    ];


    public static $routeSummaryList=[
        'no'=>[
            'title'=>'序号',
        ],
        'nickname'=>[
            'title'=>'提货点名称',
        ],
        'realname'=>[
            'title'=>'团长名称',
        ],
        'address'=>[
            'title'=>'提货点地址',
            'cols'=>2,
        ],
        'phone'=>[
            'title'=>'联系电话',
            'cols'=>2,
        ],
        'num'=>[
            'title'=>'商品总量',
        ],
        'remark'=>[
            'title'=>'备注',
        ],
    ];



    public static $deliveryGoodsList=[
        'no'=>[
            'title'=>'序号',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>3,
        ],
        'sku_name'=>[
            'title'=>'规格',
        ],

        'sku_price'=>[
            'title'=>'商品单价',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'num'=>[
            'title'=>'商品数量',
        ],
        'sku_unit'=>[
            'title'=>'商品单位',
        ],
        'need_amount'=>[
            'title'=>'总价',
            'type'=>self::CELL_TYPE_MONEY
        ],
    ];


    public static $routeGoodsList=[
        'no'=>[
            'title'=>'序号',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>4,
        ],
        'sku_name'=>[
            'title'=>'规格',
        ],
        'num'=>[
            'title'=>'商品数量',
        ],
        'sku_unit'=>[
            'title'=>'商品单位',
        ],
        'remark'=>[
            'title'=>'备注',
        ],
    ];


    public static $scheduleList=[
        'goods_owner'=>[
            'title'=>'商品归属(自营/异业联盟)',
        ],
        'goods_name'=>[
            'title'=>'商品',
        ],
        'sku_name'=>[
            'title'=>'属性',
        ],
        'schedule_display_channel'=>[
            'title'=>'展示模块(普通/秒杀/折扣购)',
        ],
        'schedule_status'=>[
            'title'=>'排期状态(上架/下架/新增)',
        ],
        'schedule_name'=>[
            'title'=>'排期名称',
        ],
        'schedule_price'=>[
            'title'=>'活动价格',
        ],
        'schedule_stock'=>[
            'title'=>'活动库存',
        ],
        'schedule_limit_quantity'=>[
            'title'=>'限购数量',
        ],
        'display_order'=>[
            'title'=>'排序(从大到小)',
        ],
        'display_start'=>[
            'title'=>'展示开始时间(2019-01-01 00:00:00)',
        ],
        'display_end'=>[
            'title'=>'展示结束时间(2019-01-01 00:00:00)',
        ],
        'online_time'=>[
            'title'=>'起售时间(2019-01-01 00:00:00)',
        ],
        'offline_time'=>[
            'title'=>'止售时间(2019-01-01 00:00:00)',
        ],
        'expect_arrive_time'=>[
            'title'=>'预计送达时间(2019-01-01)',
        ],
        'validity_start'=>[
            'title'=>'有效期起始时间(2019-01-01 00:00:00)',
        ],
        'validity_end'=>[
            'title'=>'有效期截止时间(2019-01-01 00:00:00)',
        ],
    ];

    public static $goodsSkuList=[
        'goods_owner_name'=>[
            'title'=>'商品归属(自营/异业联盟)',
        ],
        'sort_1_name'=>[
            'title'=>'一级分类',
        ],
        'sort_2_name'=>[
            'title'=>'二级分类',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>2,
        ],
        'goods_type'=>[
            'title'=>'商品类别(实物/虚拟/快递)',
        ],
        'goods_status'=>[
            'title'=>'商品状态(上架/下架/新增)',
        ],
        'goods_display_order'=>[
            'title'=>'商品排列顺序',
        ],
        'sku_name'=>[
            'title'=>'属性名称',
        ],
        'sku_status'=>[
            'title'=>'属性状态(上架/下架/新增)',
        ],
        'sku_unit'=>[
            'title'=>'属性单位',
        ],
        'sku_stock'=>[
            'title'=>'属性库存',
        ],
        'sku_sold'=>[
            'title'=>'属性已售',
        ],
        'sku_describe'=>[
            'title'=>'属性描述',
        ],
        'sku_display_order'=>[
            'title'=>'商品属性排列顺序',
        ],
        'sku_standard'=>[
            'title'=>'是否为标准件(标准品/非标准品)',
        ],
        'sku_unit_factor'=>[
            'title'=>'重量因子(千克)',
        ],
        'purchase_price'=>[
            'title'=>'采购价',
        ],
        'reference_price'=>[
            'title'=>'划线价',
        ],
        'one_level_rate'=>[
            'title'=>'用户一级分销比例(百分比)',
        ],
        'two_level_rate'=>[
            'title'=>'用户二级分销比例(百分比)',
        ],
        'share_rate_1'=>[
            'title'=>'一级分享比例(百分比)',
        ],
        'delivery_rate'=>[
            'title'=>'配送比例(百分比)',
        ],
        'production_date'=>[
            'title'=>'生产时间/有效期起始时间',
        ],
        'expired_date'=>[
            'title'=>'过期时间/有效期结束时间',
        ],
    ];


    public static $orderListExport=[
        'order_no'=>[
            'title'=>'订单编号',
            'cols'=>2,
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>2,
        ],
        'sku_name'=>[
            'title'=>'商品规格',
        ],
        'order_goods_sku_price'=>[
            'title'=>'商品单价',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'order_goods_num'=>[
            'title'=>'商品数量',
        ],
        'order_goods_discount_amount'=>[
            'title'=>'优惠折扣',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'order_goods_amount'=>[
            'title'=>'金额',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'pay_status'=>[
            'title'=>'支付状态',
        ],
        'pay_name'=>[
            'title'=>'余额支付/三方支付',
        ],
        'created_at'=>[
            'title'=>'下单时间',
            'cols'=>2,
        ],
        'expect_arrive_time'=>[
            'title'=>'预计送达时间',
        ],
        'accept_name'=>[
            'title'=>'收货人姓名',
        ],
        'accept_mobile'=>[
            'title'=>'收货人电话',
            'cols'=>2,
        ],
        'accept_address'=>[
            'title'=>'收货人地址',
            'cols'=>3,
        ],
        'delivery_name'=>[
            'title'=>'配送点联系人',
        ],
        'delivery_phone'=>[
            'title'=>'配送点电话',
            'cols'=>2,
        ],
    ];


    public static $goodsSkuStockLogList=[
        'type'=>[
            'title'=>'日志类型',
        ],
        'goods_name'=>[
            'title'=>'商品',
            'cols'=>2,
        ],
        'sku_name'=>[
            'title'=>'属性',
        ],
        'num'=>[
            'title'=>'数量',
        ],
        'remark'=>[
            'title'=>'备注',
            'cols'=>2,
        ],
        'operator_name'=>[
            'title'=>'操作人名称',
        ],
        'created_at'=>[
            'title'=>'时间',
            'cols'=>2,
        ],
    ];



    public static $sortCollectionOrderList=[
        'big_sort_name'=>[
            'title'=>'一级分类',
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>2,
        ],
        'sku_name'=>[
            'title'=>'规格',
            'cols'=>2,
        ],
        'sku_unit'=>[
            'title'=>'单位',
        ],
        'num'=>[
            'title'=>'数量',
        ],
        'sku_price'=>[
            'title'=>'商品单价',
            'type'=>self::CELL_TYPE_MONEY
        ],
    ];

    public static $sortDetailOrderList=[
        'id'=>[
            'title'=>'序号',
        ],
        'name_and_phone'=>[
            'title'=>'用户名/电话',
            'cols'=>2,
        ],
        'address'=>[
            'title'=>'地址',
            'cols'=>4,
        ],
        'goods_name'=>[
            'title'=>'商品名称',
            'cols'=>3,
        ],
        'sku_name'=>[
            'title'=>'规格',
            'cols'=>2,
        ],
        'num'=>[
            'title'=>'数量',
        ],
    ];


    /**
     * @var array 订单统计-用户数据统计
     */
    public static $orderStatisticCustomerStatisticDataList=[
        'customer_name'=>[
            'title'=>'用户名',
        ],
        'customer_phone'=>[
            'title'=>'手机号',
            'cols'=>2,
        ],
        'order_count'=>[
            'title'=>'订单数',
        ],
        'order_goods_sum'=>[
            'title'=>'商品数',
        ],
        'order_goods_count'=>[
            'title'=>'单品数',
        ],
        'order_amount'=>[
            'title'=>'总金额',
            'type'=>self::CELL_TYPE_MONEY
        ],
    ];



    /**
     * @var array 订单统计-团长数据统计
     */
    public static $orderStatisticDeliveryStatisticDataList=[
        'delivery_name'=>[
            'title'=>'姓名',
        ],
        'delivery_phone'=>[
            'title'=>'电话',
            'cols'=>2,
        ],
        'address'=>[
            'title'=>'地址',
            'cols'=>4,
        ],
        'customer_count'=>[
            'title'=>'下单人数',
        ],
        'order_count'=>[
            'title'=>'订单数',
        ],
        'order_goods_num'=>[
            'title'=>'商品数',
        ],
        'order_goods_count'=>[
            'title'=>'商品件数',
        ],
        'order_amount'=>[
            'title'=>'总金额',
            'type'=>self::CELL_TYPE_MONEY
        ],
    ];


    /**
     * @var array 订单统计-团长数据统计
     */
    public static $orderStatisticGoodsStatisticDataList=[
        'schedule_name'=>[
            'title'=>'排期',
        ],
        'goods_name'=>[
            'title'=>'商品名',
        ],
        'sku_name'=>[
            'title'=>'商品规格',
        ],
        'goods_num'=>[
            'title'=>'销售数量',
        ],
        'purchase_price'=>[
            'title'=>'进价',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'sku_price'=>[
            'title'=>'售价',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'goods_amount'=>[
            'title'=>'总金额',
            'type'=>self::CELL_TYPE_MONEY
        ],
        'customer_count'=>[
            'title'=>'下单人数',
        ],
        'delivery_count'=>[
            'title'=>'下单团数',
        ],
    ];

}