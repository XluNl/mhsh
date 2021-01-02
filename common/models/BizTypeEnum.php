<?php


namespace common\models;


class BizTypeEnum
{
    const BIZ_TYPE_POPULARIZER = 1;
    const BIZ_TYPE_DELIVERY = 2;
    const BIZ_TYPE_AGENT = 3;
    const BIZ_TYPE_HA = 4;
    const BIZ_TYPE_CUSTOMER_DISTRIBUTE =5;
    const BIZ_TYPE_CUSTOMER_WALLET = 6;
    const BIZ_TYPE_PAYMENT_HANDLING_FEE = 7;
    const BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY = 8;
    const BIZ_TYPE_COMPANY = 9;

    public static $bizTypeCompanyShowArr= [
        self::BIZ_TYPE_POPULARIZER=>'分享团长',
        self::BIZ_TYPE_DELIVERY=>'配送团长',
        self::BIZ_TYPE_AGENT=>'代理商',
        self::BIZ_TYPE_HA=>'异业联盟',
        self::BIZ_TYPE_CUSTOMER_DISTRIBUTE=>'用户活动账户',
        self::BIZ_TYPE_CUSTOMER_WALLET=>'用户钱包',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'社区合伙人商品质保金',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE=>'支付通道费',
        self::BIZ_TYPE_COMPANY=>'平台账户',
    ];

    public static function getBizTypeShowArr($companyId){
        if (Common::isSuperCompany($companyId)){
            return static::$bizTypeCompanyShowArr;
        }
        return static::$bizTypeShowArr;
    }

    public static function getBizTypeOperaArr($companyId){
        if (Common::isSuperCompany($companyId)){
            return static::$bizTypeCompanyShowArr;
        }
        return static::$bizTypeOperaArr;
    }


    public static function getBizTypeShowArrKey($companyId){
        if (Common::isSuperCompany($companyId)){
            return array_keys(static::$bizTypeCompanyShowArr);
        }
        return array_keys(static::$bizTypeShowArr);
    }


    public static $bizTypeShowArr= [
        self::BIZ_TYPE_POPULARIZER=>'分享团长',
        self::BIZ_TYPE_DELIVERY=>'配送团长',
        self::BIZ_TYPE_AGENT=>'代理商',
        self::BIZ_TYPE_HA=>'异业联盟',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE=>'支付通道费',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'社区合伙人商品质保金',
    ];

    public static $bizTypeOperaArr= [
        self::BIZ_TYPE_POPULARIZER=>'分享团长',
        self::BIZ_TYPE_DELIVERY=>'配送团长',
        self::BIZ_TYPE_HA=>'异业联盟',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'社区合伙人商品质保金',
    ];


    public static $bizTypeArr= [
        self::BIZ_TYPE_POPULARIZER=>'分享团长',
        self::BIZ_TYPE_DELIVERY=>'配送团长',
        self::BIZ_TYPE_AGENT=>'代理商',
        self::BIZ_TYPE_HA=>'异业联盟',
        self::BIZ_TYPE_CUSTOMER_DISTRIBUTE=>'用户活动账户',
        self::BIZ_TYPE_CUSTOMER_WALLET=>'用户钱包',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE=>'支付通道费',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'社区合伙人商品质保金',
        self::BIZ_TYPE_COMPANY=>'平台账户',
    ];

    public static $bizTypeCssArr= [
        self::BIZ_TYPE_POPULARIZER=>'label label-info',
        self::BIZ_TYPE_DELIVERY=>'label label-primary',
        self::BIZ_TYPE_AGENT=>'label label-danger',
        self::BIZ_TYPE_HA=>'label label-default',
        self::BIZ_TYPE_CUSTOMER_DISTRIBUTE=>'label label-success',
        self::BIZ_TYPE_CUSTOMER_WALLET=>'label label-warning',
        self::BIZ_TYPE_PAYMENT_HANDLING_FEE=>'label label-default',
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>'label label-default',
        self::BIZ_TYPE_COMPANY=>'label label-success',
    ];

    public static $bizTypeMap= [
        self::BIZ_TYPE_POPULARIZER=>RoleEnum::ROLE_POPULARIZER,
        self::BIZ_TYPE_DELIVERY=>RoleEnum::ROLE_DELIVERY,
        self::BIZ_TYPE_AGENT=>RoleEnum::ROLE_AGENT,
        self::BIZ_TYPE_HA=>RoleEnum::ROLE_DELIVERY,
        self::BIZ_TYPE_CUSTOMER_DISTRIBUTE=>RoleEnum::ROLE_CUSTOMER,
        self::BIZ_TYPE_CUSTOMER_WALLET=>RoleEnum::ROLE_CUSTOMER,
        self::BIZ_TYPE_DELIVERY_COMMODITY_WARRANTY=>RoleEnum::ROLE_DELIVERY,
        self::BIZ_TYPE_COMPANY=>RoleEnum::ROLE_SYSTEM,
    ];


}