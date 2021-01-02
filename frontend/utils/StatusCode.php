<?php


namespace frontend\utils;

use frontend\utils\exceptions\BusinessException;
use frontend\utils\exceptions\SkipException;

class StatusCode
{
    const STATUS_SUCCESS = 10000;
    const STATUS_PARAMS_MISS = 10001;
    const STATUS_PARAMS_ERROR = 10002;
    const STATUS_SKIP_MESSAGE = 10003;
    const STATUS_GOODS_OFFLINE = 10004;
    const STORE_NOT_OPEN = 10005;
    const CART_EMPTY = 10006;
    const DELIVERY_NOT_EXIST = 10007;
    const DELIVERY_NOT_ALLOW_ORDER = 10008;
    const REMOVE_EXTRA_SKU_NUM = 10009;
    const NOT_REACH_DELIVERY_START_LIMIT = 10010;
    const DELIVERY_FREIGHT_ERROR = 10011;
    const ORDER_COUPON_CAN_NOT_USE = 10012;
    const ORDER_NOT_EXIST = 10013;
    const ORDER_PAY_ERROR = 10014;
    const PAYMENT_NOT_EXIST = 10015;
    const ORDER_CANCEL_ERROR = 10016;
    const CUSTOMER_NOT_EXIST = 10017;
    const PAY_BALANCE_ERROR = 10018;
    const COUPON_NOT_EXIST = 10019;
    const COUPON_CAN_NOT_USE = 10020;
    const DELIVERY_TYPE_NOT_EXIST = 10021;
    const ADDRESS_NOT_EXIST = 10022;
    const ORDER_ORDER_ERROR = 10023;
    const ADDRESS_OPERATION_ERROR = 10024;
    const ORDER_GOODS_NOT_EXIST = 10025;
    const CUSTOMER_SERVICE_ERROR = 10026;
    const STATUS_SELECTED_DELIVERY_ERROR = 10027;
    const CUSTOMER_SERVICE_CANCEL_ERROR = 10028;
    const CHECK_CAPTCHA_ERROR = 10029;
    const SEND_CAPTCHA_ERROR = 10030;
    const DRAW_COUPON_ERROR = 10031;
    const CITY_SEARCH_ERROR = 10032;
    const CUSTOMER_CREATE_ERROR = 10033;
    const POPULARIZER_BIND_ERROR = 10034;
    const GOODS_NOT_EXIST = 10035;
    const ORDER_COMPLETE_ERROR = 10036;
    const ADD_ORDER_LOG_ERROR= 10037;
    const CUSTOMER_INVITATION_BIND_ERROR= 10038;
    const AMOUNT_MUST_POSITIVE =10039;
    const WITHDRAW_ERROR=10040;
    const INVITATION_NOT_EXIST = 10041;
    const ALLIANCE_NOT_EXIST = 10042;
    const ALLIANCE_NOT_ONLINE = 10043;
    const DELIVERY_CAN_BUY_ALLIANCE_GOODS = 10044;
    const START_SALE_NUM_CHECK_ERROR = 10045;
    const ORDER_NOT_ALLOW_OWNER_TYPE = 10046;
    const BANNER_NOT_EXIST = 10047;
    const GROUP_ACTIVE_NOT_EXIST = 10048;
    const GROUP_ORDER_TYPE_NOT_ALLOW = 10049;
    const GROUP_ROOM_MANUAL_CLOSE = 10050;
    const GROUP_ROOM_NOT_OWNER = 10051;

    const STATUS_NOT_SELECTED_DELIVERY = 80000;
    const NOT_LOGIN = 80001;
    const MINI_WECHAT_LOGIN_ERROR = 80002;
    const MINI_WECHAT_ACCOUNT_CREATE_ERROR = 80003;
    const USER_INFO_NOT_EXIST = 80004;
    const USER_INFO_REGISTER_ERROR = 80006;
    const CART_OPERATION_ERROR = 80007;
    const MINI_WECHAT_ACCOUNT_DISABLED = 80008;
    const PHONE_USED = 80009;
    const ACCOUNT_CREATE_REPEAT= 80010;
    const CODE_GENERATE_ERROR= 80011;
    const PHONE_DECRYPT_ERROR= 80012;
    const PHONE_REGISTER_ERROR= 80013;
    const RECORD_ITEM_DISABLE = 90001;

    const REPOSITORY_CALL_ERROR = 98000;

    const STATUS_BUSY_ERROR = 99999;
    public static $exceptionArr = [
        self::STATUS_SUCCESS=>'处理成功',
        self::STATUS_PARAMS_MISS =>'参数缺失:%s',
        self::STATUS_PARAMS_ERROR =>'参数错误:%s',
        self::STATUS_SKIP_MESSAGE =>'跳转',
        self::STATUS_GOODS_OFFLINE =>'商品已下线',
        self::STORE_NOT_OPEN =>'商店在%s至%s之间允许下单',
        self::CART_EMPTY =>'购物车为空',
        self::DELIVERY_NOT_EXIST =>'此配送点不存在，请重新选择',
        self::DELIVERY_NOT_ALLOW_ORDER =>'此配送点不允许下单，请重新选择',
        self::REMOVE_EXTRA_SKU_NUM =>'以下商品超过购买限制：%s',
        self::NOT_REACH_DELIVERY_START_LIMIT =>'未达起送金额：%s',
        self::DELIVERY_FREIGHT_ERROR=>'配送费设置错误:%s',
        self::ORDER_COUPON_CAN_NOT_USE=>'该优惠券不可用:%s',
        self::ORDER_NOT_EXIST=>'订单不存在',
        self::ORDER_PAY_ERROR=>'订单支付错误:%s',
        self::PAYMENT_NOT_EXIST=>'支付方式不存在',
        self::ORDER_CANCEL_ERROR=>'订单取消错误:%s',
        self::CUSTOMER_NOT_EXIST=>'客户信息不存在',
        self::PAY_BALANCE_ERROR=>'余额支付失败',
        self::COUPON_NOT_EXIST=>'优惠券不存在',
        self::DELIVERY_TYPE_NOT_EXIST=>'配送方式不存在',
        self::ADDRESS_NOT_EXIST=>'配送地址不存在',
        self::ORDER_ORDER_ERROR=>'下单失败:%s',
        self::ADDRESS_OPERATION_ERROR=>'收货地址操作失败:%s',
        self::ORDER_GOODS_NOT_EXIST=>'订单商品不存在',
        self::CUSTOMER_SERVICE_ERROR=>'申请售后失败:%s',
        self::STATUS_SELECTED_DELIVERY_ERROR =>'修改配送点失败',
        self::CUSTOMER_SERVICE_CANCEL_ERROR=>'取消售后失败:%s',
        self::CHECK_CAPTCHA_ERROR=>'验证码验证失败:%s',
        self::SEND_CAPTCHA_ERROR=>'验证码获取失败:%s',
        self::CART_OPERATION_ERROR=>'购物车操作失败:%s',
        self::DRAW_COUPON_ERROR=>'领券失败:%s',
        self::CITY_SEARCH_ERROR=>'城市检索失败',
        self::CUSTOMER_CREATE_ERROR=>'创建客户信息失败:%s',
        self::CODE_GENERATE_ERROR => '小程序码生成失败',
        self::POPULARIZER_BIND_ERROR=>'分享关系绑定失败:%s',
        self::GOODS_NOT_EXIST=>'商品不存在',
        self::ORDER_COMPLETE_ERROR=>'订单完成错误:%s',
        self::ADD_ORDER_LOG_ERROR=>'增加订单日志错误:%s',
        self::CUSTOMER_INVITATION_BIND_ERROR=>'邀请关系绑定失败:%s',
        self::AMOUNT_MUST_POSITIVE=>'金额必须大于0',
        self::WITHDRAW_ERROR=>'提现失败:%s',
        self::INVITATION_NOT_EXIST=>'邀请关系不存在',
        self::ALLIANCE_NOT_EXIST=>'异业联盟点不存在',
        self::ALLIANCE_NOT_ONLINE=>'异业联盟点暂停营业',
        self::DELIVERY_CAN_BUY_ALLIANCE_GOODS=>'只有团长才能购买联盟商品',
        self::START_SALE_NUM_CHECK_ERROR=>'未达到单品起售数量:%s',
        self::ORDER_NOT_ALLOW_OWNER_TYPE=>'不支持的模式',
        self::BANNER_NOT_EXIST=>'Banner不存在',
        self::GROUP_ACTIVE_NOT_EXIST=>'团购活动不存在',
        self::GROUP_ORDER_TYPE_NOT_ALLOW=>'非法的拼团方式',
        self::GROUP_ROOM_MANUAL_CLOSE=>'手动关闭房间错误:%s',
        self::GROUP_ROOM_NOT_OWNER=>'无权操作此房间',

        self::STATUS_NOT_SELECTED_DELIVERY =>'未选择配送点',
        self::NOT_LOGIN =>'请先登录',
        self::MINI_WECHAT_LOGIN_ERROR =>'小程序登录失败',
        self::MINI_WECHAT_ACCOUNT_CREATE_ERROR =>'小程序账户创建失败',
        self::ACCOUNT_CREATE_REPEAT =>'您已注册，请勿重复注册',
        self::MINI_WECHAT_ACCOUNT_DISABLED =>'小程序账户已禁用',
        self::USER_INFO_NOT_EXIST =>'用户信息不存在，请先注册',
        self::USER_INFO_REGISTER_ERROR =>'用户信息注册失败:%s',
        self::PHONE_USED=>'手机号已注册，请更换手机号重试',
        self::PHONE_DECRYPT_ERROR=>'手机号解码错误',
        self::PHONE_REGISTER_ERROR=>'注册失败:%s',
        self::RECORD_ITEM_DISABLE=>'已禁用',
        self::REPOSITORY_CALL_ERROR=>'[%s]依赖调用失败:%s',
        self::STATUS_BUSY_ERROR =>'系统繁忙',
    ];

    public static function createExp($code){
        $message = "未知错误";
        if (key_exists($code,self::$exceptionArr)){
            $message = self::$exceptionArr[$code];
        }
        return new BusinessException($message,$code);
    }

    public static function createExpWithParams($code,...$params){
        $message = "未知错误";
        if (key_exists($code,self::$exceptionArr)){
            $message = sprintf(self::$exceptionArr[$code],...$params);
        }
        return new BusinessException($message,$code);
    }

    public static function createSkipExp($flag, $title, $url, $btnMsg, $subUrl=null, $subBtnMsg=null){
        return new SkipException($flag, $title, $url, $btnMsg,self::$exceptionArr[StatusCode::STATUS_SKIP_MESSAGE],StatusCode::STATUS_SKIP_MESSAGE,$subUrl,$subBtnMsg);
    }
}