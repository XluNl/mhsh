<?php


namespace common\models;

/**
 * 定义商品、订单、分类的公共常量
 * Class GoodsConstantEnum
 * @package common\models
 */
class GoodsConstantEnum
{
    /**
     * 商品类别 goods_type
     */
    const TYPE_OBJECT = 1;
    const TYPE_VIRTUAL = 2;
    const TYPE_EXPRESS = 3;
    const TYPE_GROUP_ACTIVE = 4;

    public static $typeArr=[
        self::TYPE_OBJECT=>'实物',
        self::TYPE_VIRTUAL=>'虚拟',
        self::TYPE_EXPRESS=>'快递',
        self::TYPE_GROUP_ACTIVE=>'拼团',
    ];

    public static $typeCssArr=[
        self::TYPE_OBJECT=>'label label-info',
        self::TYPE_VIRTUAL=>'label label-primary',
        self::TYPE_EXPRESS=>'label label-success',
        self::TYPE_GROUP_ACTIVE=>'label label-warning',
    ];

    const OWNER_SELF = 1;
    const OWNER_HA = 2;
    const OWNER_DELIVERY = 3;

    const OWNER_SELF_ID = 0;
    public static $ownerArr=[
        self::OWNER_SELF=>'代理商自营',
        self::OWNER_HA=>'异业联盟',
        self::OWNER_DELIVERY=>'团长自营',
    ];
    public static $ownerCssArr=[
        self::OWNER_SELF=>'label label-info',
        self::OWNER_HA=>'label label-warning',
        self::OWNER_DELIVERY=>'label label-primary',
    ];


    const STATUS_DELETED = 0;
    const STATUS_UP = 1;
    const STATUS_DOWN = 2;
    const STATUS_ACTIVE = 3;

    public static $statusArr=[
        self::STATUS_DELETED=>'删除',
        self::STATUS_UP=>'上架',
        self::STATUS_DOWN=>'下架',
        self::STATUS_ACTIVE=>'新增',
    ];


    public static $statusCssArr=[
        self::STATUS_DELETED=>'label label-danger',
        self::STATUS_UP=>'label label-success',
        self::STATUS_DOWN=>'label label-warning',
        self::STATUS_ACTIVE=>'label label-info',
    ];

    public static $statusListArr=[
        self::STATUS_DOWN=>'下架',
        self::STATUS_UP=>'上架',
    ];

    public static $activeStatusArr = [
        self::STATUS_UP,
        self::STATUS_DOWN,
        self::STATUS_ACTIVE,
    ];


    const SCHEDULE_DISPLAY_CHANNEL_NORMAL = 1;
    const SCHEDULE_DISPLAY_CHANNEL_SPIKE = 2;
    const SCHEDULE_DISPLAY_CHANNEL_OUTER = 3;
    const SCHEDULE_DISPLAY_CHANNEL_DISCOUNT = 4;
    const SCHEDULE_DISPLAY_CHANNEL_GROUP = 5;

    const SCHEDULE_DISPLAY_CHANNEL_STAR = 6;

    public static $scheduleDisplayChannelArr=[
        self::SCHEDULE_DISPLAY_CHANNEL_NORMAL => '普通',
        self::SCHEDULE_DISPLAY_CHANNEL_SPIKE => '秒杀',
        self::SCHEDULE_DISPLAY_CHANNEL_OUTER => '异业联盟',
        self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT => '折扣购',
        self::SCHEDULE_DISPLAY_CHANNEL_GROUP => '拼团',
        self::SCHEDULE_DISPLAY_CHANNEL_STAR=>'星球专区',
    ];
    public static $scheduleDisplayChannelCssArr=[
        self::SCHEDULE_DISPLAY_CHANNEL_NORMAL => 'label label-info',
        self::SCHEDULE_DISPLAY_CHANNEL_SPIKE => 'label label-primary',
        self::SCHEDULE_DISPLAY_CHANNEL_OUTER => 'label label-warning',
        self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT => 'label label-danger',

        self::SCHEDULE_DISPLAY_CHANNEL_STAR=>'label label-success',
    ];


    public static $scheduleDisplayChannelMap=[
        self::OWNER_SELF=>[
            self::SCHEDULE_DISPLAY_CHANNEL_NORMAL,
            self::SCHEDULE_DISPLAY_CHANNEL_SPIKE,
            self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,
            self::SCHEDULE_DISPLAY_CHANNEL_GROUP,
            self::SCHEDULE_DISPLAY_CHANNEL_STAR,
        ],
        self::OWNER_HA=>[
            self::SCHEDULE_DISPLAY_CHANNEL_OUTER
        ],
        self::OWNER_DELIVERY=>[
            self::SCHEDULE_DISPLAY_CHANNEL_NORMAL,
            self::SCHEDULE_DISPLAY_CHANNEL_SPIKE,
            self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,
        ],
    ];


    public static $scheduleSearchDisplayChannelMap=[
        self::OWNER_SELF=>[
            self::SCHEDULE_DISPLAY_CHANNEL_NORMAL,
            self::SCHEDULE_DISPLAY_CHANNEL_SPIKE,
            self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,
        ],
        self::OWNER_HA=>[
            self::SCHEDULE_DISPLAY_CHANNEL_OUTER
        ],
        self::OWNER_DELIVERY=>[
            self::SCHEDULE_DISPLAY_CHANNEL_NORMAL,
            self::SCHEDULE_DISPLAY_CHANNEL_SPIKE,
            self::SCHEDULE_DISPLAY_CHANNEL_DISCOUNT,
        ],
    ];


    const DELIVERY_TYPE_SELF = 1;
    const DELIVERY_TYPE_HOME = 2;
    const DELIVERY_TYPE_EXPRESS = 3;
    const DELIVERY_TYPE_ALLIANCE_SELF = 4;
    public static $deliveryTypeSelfArr = [
        self::DELIVERY_TYPE_SELF => '到点自取',
        self::DELIVERY_TYPE_HOME => '配送到家',
        self::DELIVERY_TYPE_EXPRESS => '快递',
    ];

    public static $deliveryTypeAllianceArr = [
        self::DELIVERY_TYPE_ALLIANCE_SELF=>'送货上门',
    ];

    public static $deliveryTypeArr = [
        self::DELIVERY_TYPE_SELF => '到点自取',
        self::DELIVERY_TYPE_HOME => '配送到家',
        self::DELIVERY_TYPE_EXPRESS => '快递',
        self::DELIVERY_TYPE_ALLIANCE_SELF=>'送货上门',
    ];
    public static $deliveryTypeCssArr = [
        self::DELIVERY_TYPE_SELF => 'label label-info',
        self::DELIVERY_TYPE_HOME => 'label label-primary',
        self::DELIVERY_TYPE_EXPRESS => 'label label-success',
        self::DELIVERY_TYPE_ALLIANCE_SELF=>'label label-warning',
    ];


    /**
     * 商品类型允许的配送方案
     */
    public static $canDeliveryTypeMap=[
        self::TYPE_OBJECT=>[
            self::DELIVERY_TYPE_SELF,
            self::DELIVERY_TYPE_HOME,
        ],
        self::TYPE_VIRTUAL=>[
            self::DELIVERY_TYPE_SELF,
            self::DELIVERY_TYPE_HOME,
        ],
        self::TYPE_EXPRESS=>[
            self::DELIVERY_TYPE_EXPRESS,
        ],
    ];


    const DISPLAY_STATUS_WAITING = 1;
    const DISPLAY_STATUS_IN_SALE = 2;
    const DISPLAY_STATUS_SALE_OUT = 3;
    const DISPLAY_STATUS_END = 4;

    public static $displayStatusTextArr = [
        self::DISPLAY_STATUS_WAITING => '等待售卖',
        self::DISPLAY_STATUS_IN_SALE => '售卖中',
        self::DISPLAY_STATUS_SALE_OUT => '售罄',
        self::DISPLAY_STATUS_END => '抢购结束',
    ];

    const ALLIANCE_DISPLAY_GOODS_STATUS_UP =1;
    const ALLIANCE_DISPLAY_GOODS_STATUS_DOWN =2;
    public static $allianceDisplayGoodsStatusArr = [
        self::ALLIANCE_DISPLAY_GOODS_STATUS_UP  ,
        self::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN,
    ];
    public static $allianceDisplayGoodsStatusTextArr = [
        self::ALLIANCE_DISPLAY_GOODS_STATUS_UP => '上架中',
        self::ALLIANCE_DISPLAY_GOODS_STATUS_DOWN => '下架中',
    ];
}