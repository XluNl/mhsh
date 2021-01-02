<?php


namespace business\utils;

use business\utils\exceptions\BusinessException;
use business\utils\exceptions\SkipException;

class StatusCode
{
    const STATUS_SUCCESS = 10000;
    const STATUS_PARAMS_MISS = 10001;
    const STATUS_PARAMS_ERROR = 10002;
    const STATUS_SKIP_MESSAGE = 10003;
    const BUSINESS_APPLY_NOT_EXIST = 10004;
    const BUSINESS_APPLY_OPERATION_ERROR = 10005;
    const CHECK_CAPTCHA_ERROR = 10006;
    const SEND_CAPTCHA_ERROR = 10007;
    const DELIVERY_CHANGE_ERROR = 10008;
    const ORDER_NOT_EXIST = 10009;
    const UPLOAD_WEIGHTS = 10010;
    const DELIVERY_NOT_EXIST = 10011;
    const RECEIVE_ERROR = 10012;
    const ADD_ORDER_LOG_ERROR=10013;
    const GET_ORDER_ERROR = 10014;
    const DELIVERY_BELONG_NOT_ALLOW = 10015;
    const CUSTOMER_SERVICE_OPERATION_ERROR = 10016;
    const DELIVERY_COMMENT_OPERATION_ERROR = 10017;
    const DISTRIBUTE_BALANCE_DETAIL_ERROR = 10018;
    const PERMISSION_NOT_ALLOW = 10019;
    const DISTRIBUTE_STATISTICS_ERROR = 10020;
    const POPULARIZER_NOT_EXIST = 10021;
    const POPULARIZER_CHANGE_ERROR = 10022;
    const AMOUNT_MUST_POSITIVE = 10023;
    const WITHDRAW_ERROR = 10024;
    const ILLEGAL_BIZ_TYPE = 10025;
    const DISTRIBUTE_ITEM_NOT_EXIST = 10026;
    const UN_UPLOAD_WEIGHTS = 10027;
    const GOODS_SKU_ALLIANCE_MODIFY = 10028;
    const GOODS_SKU_ALLIANCE_NOT_EXIST = 10029;
    const GOODS_SKU_ALLIANCE_PUBLISH_ERROR = 10030;
    const GOODS_SKU_NOT_EXIST = 10031;
    const GOODS_SKU_STATUS_OPERATION_ERROR = 10032;
    const GOODS_SCHEDULE_COLLECTION_NOT_EXIST = 10033;
    const GOODS_SCHEDULE_COLLECTION_MODIFY = 10034;
    const GOODS_SCHEDULE_COLLECTION_STATUS_OPERATION_ERROR = 10035;
    const GOODS_SCHEDULE_ADD_GOODS_ERROR = 10036;
    const GOODS_SCHEDULE_MODIFY_STOCK_ERROR = 10037;
    const DELIVERY_AUTH_ERROR = 10038;
    const DELIVERY_AUTH_PAY_CALLBACK_ERROR = 10039;
    const DELIVERY_OUT_ERROR = 10040;
    const ORDER_NOT_BELONG = 10041;
    const BATCH_UPLOAD_AND_RECEIVE_ORDER = 10042;
    const COUPON_BATCH_NOT_EXIST = 10043;
    const COUPON_BATCH_NOT_BELONG = 10044;
    const COUPON_BATCH_MODIFY_ERROR = 10045;
    const COUPON_BATCH_STATUS_OPERATION_ERROR = 10046;
    const COUPON_BATCH_MANUAL_DRAW_ERROR = 10047;
    const CHARGE_PAY_ERROR = 10048;
    const CHARGE_CALL_BACK_ERROR = 10049;
    const BUSINESS_AUTH_PAY_CALLBACK_ERROR = 10050;


    const STATUS_NOT_SELECTED_DELIVERY = 80000;
    const NOT_LOGIN = 80001;
    const MINI_WECHAT_LOGIN_ERROR = 80002;
    const MINI_WECHAT_ACCOUNT_CREATE_ERROR = 80003;
    const USER_INFO_NOT_EXIST = 80004;
    const USER_INFO_REGISTER_ERROR = 80006;
    const MINI_WECHAT_ACCOUNT_DISABLED = 80008;
    const PHONE_USED = 80009;
    const ACCOUNT_CREATE_REPEAT= 80010;
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
        self::BUSINESS_APPLY_NOT_EXIST=>'申请不存在',
        self::BUSINESS_APPLY_OPERATION_ERROR=>'申请操作失败:%s',
        self::CHECK_CAPTCHA_ERROR=>'验证码验证失败:%s',
        self::SEND_CAPTCHA_ERROR=>'验证码获取失败:%s',
        self::DELIVERY_CHANGE_ERROR => '更换配送点失败:%s',
        self::ORDER_NOT_EXIST=>'订单不存在',
        self::UPLOAD_WEIGHTS =>'确认收货失败:%s',
        self::DELIVERY_NOT_EXIST=>'配送点不存在',
        self::RECEIVE_ERROR=>'收货失败:%s',
        self::ADD_ORDER_LOG_ERROR=>'订单日志记录失败',
        self::GET_ORDER_ERROR=>'获取订单失败',
        self::DELIVERY_BELONG_NOT_ALLOW=>'此配送点无权限',
        self::CUSTOMER_SERVICE_OPERATION_ERROR=>'售后处理失败:%s',
        self::DELIVERY_COMMENT_OPERATION_ERROR=>'团长说操作失败:%s',
        self::DISTRIBUTE_BALANCE_DETAIL_ERROR=>'明细错误:%s',
        self::PERMISSION_NOT_ALLOW =>'无权限',
        self::DISTRIBUTE_STATISTICS_ERROR=>'分润统计错误:%s',
        self::POPULARIZER_NOT_EXIST=>'分享团长不存在;%s',
        self::POPULARIZER_CHANGE_ERROR=>'分享团长切换错误;%s',
        self::AMOUNT_MUST_POSITIVE=>'金额必须大于0',
        self::WITHDRAW_ERROR=>'提现失败:%s',
        self::ILLEGAL_BIZ_TYPE=>'非法的业务类型:%s',
        self::DISTRIBUTE_ITEM_NOT_EXIST=>'分润明细不存在',
        self::UN_UPLOAD_WEIGHTS=>'取消上传实际重量失败:%s',
        self::GOODS_SKU_ALLIANCE_MODIFY=>'审核商品修改失败:%s',
        self::GOODS_SKU_ALLIANCE_NOT_EXIST=>'审核商品不存在:%s',
        self::GOODS_SKU_ALLIANCE_PUBLISH_ERROR=>'商品发布失败:%s',
        self::GOODS_SKU_NOT_EXIST=>'商品不存在:%s',
        self::GOODS_SKU_STATUS_OPERATION_ERROR=>'商品状态操作失败:%s',
        self::GOODS_SCHEDULE_COLLECTION_NOT_EXIST=>'商品大排期不存在',
        self::GOODS_SCHEDULE_COLLECTION_MODIFY=>'商品大排期修改出错:%s',
        self::GOODS_SCHEDULE_COLLECTION_STATUS_OPERATION_ERROR=>'排期状态修改出错:%s',
        self::GOODS_SCHEDULE_ADD_GOODS_ERROR=>'排期增加商品错误:%s',
        self::GOODS_SCHEDULE_MODIFY_STOCK_ERROR=>'排期库存设置错误:%s',
        self::DELIVERY_AUTH_ERROR=>'店铺认证失败:%s',
        self::DELIVERY_AUTH_PAY_CALLBACK_ERROR=>'保证金支付回调失败:%s',
        self::DELIVERY_OUT_ERROR=>'发布失败:%s',
        self::ORDER_NOT_BELONG =>'无权操作订单',
        self::BATCH_UPLOAD_AND_RECEIVE_ORDER=>'批量上传重量并确认收货失败:%s',
        self::COUPON_BATCH_NOT_EXIST=>'优惠券批次不存在',
        self::COUPON_BATCH_NOT_BELONG=>'优惠券批次无权操作',
        self::COUPON_BATCH_MODIFY_ERROR=>'优惠券批次编辑错误:%s',
        self::COUPON_BATCH_STATUS_OPERATION_ERROR=>'优惠券批次状态编辑错误:%s',
        self::COUPON_BATCH_MANUAL_DRAW_ERROR=>'社区合伙人优惠券手动发放失败:%s',
        self::CHARGE_PAY_ERROR=>'充值失败:%s',
        self::CHARGE_CALL_BACK_ERROR=>'充值回调失败:%s',
        self::BUSINESS_AUTH_PAY_CALLBACK_ERROR=>'团长保证金支付回调失败:%s',

        self::STATUS_NOT_SELECTED_DELIVERY =>'未选择配送点',
        self::NOT_LOGIN =>'请先登录',
        self::MINI_WECHAT_LOGIN_ERROR =>'小程序登录失败',
        self::MINI_WECHAT_ACCOUNT_CREATE_ERROR =>'小程序账户创建',
        self::ACCOUNT_CREATE_REPEAT =>'您已注册，请勿重复注册',
        self::MINI_WECHAT_ACCOUNT_DISABLED =>'小程序账户已禁用',
        self::USER_INFO_NOT_EXIST =>'用户信息不存在，请先注册',
        self::USER_INFO_REGISTER_ERROR =>'用户信息注册失败:%s',
        self::PHONE_USED=>'手机号已注册，请更换手机号重试',
        self::PHONE_DECRYPT_ERROR=>'手机号解码错误',
        self::PHONE_REGISTER_ERROR=>'手机号注册失败:%s',
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