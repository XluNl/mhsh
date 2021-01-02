<?php


namespace backend\utils;

use backend\utils\exceptions\BBusinessException;

class BStatusCode
{
    const STATUS_SUCCESS = 10000;
    const STATUS_PARAMS_MISS = 10001;
    const STATUS_PARAMS_ERROR = 10002;
    const ORDER_NOT_EXIST = 10003;
    const ORDER_UPDATE_ERROR = 10004;
    const ORDER_COMPLETE_ERROR = 10005;
    const ORDER_DELIVERY_OUT_ERROR = 10006;
    const ADD_ORDER_LOG_ERROR= 10007;
    const DOWNLOAD_ERROR= 10008;
    const CUSTOMER_SERVICE_OPERATION_ERROR=10009;
    const WITHDRAW_ADD_LOG_ERROR=10010;
    const WITHDRAW_AUDIT_ERROR=10011;
    const WITHDRAW_REFUND_ERROR=10012;
    const WITHDRAW_DEAL_ERROR=10013;
    const SYSTEM_OPTION_MODIFY_ERROR=10014;
    const DELIVERY_OUT_ERROR = 10015;
    const UPLOAD_WEIGHT_ERROR = 10016;
    const DRAW_COUPON_ERROR = 10017;
    const DRAW_BONUS_ERROR = 10018;
    const CUSTOMER_INVITATION_ACTIVITY_SETTLE_ERROR = 10019;
    const ORDER_CANCEL_ERROR = 10020;
    const GOODS_SKU_ALLIANCE_AUDIT_ERROR = 10021;
    const SORT_NOT_EXIST = 10022;
    const CLAIM_BALANCE_ERROR = 10023;
    const DELIVERY_GOODS_DELIVERY_CHANNEL_ERROR = 10024;
    const WITHDRAW_ERROR=10025;
    const STORAGE_SKU_BIND_ERROR = 10026;
    const STORAGE_UN_BIND= 10027;


    const ADMIN_USER_SESSION_ERROR=80001;
    const ADMIN_USER_NOT_EXIST=80002;
    const ADMIN_USER_NOT_ACTIVE=80003;


    const RECORD_ITEM_DISABLE = 90001;
    const REPOSITORY_CALL_ERROR = 98000;
    const STATUS_BUSY_ERROR = 99999;

    public static $exceptionArr = [
        self::STATUS_SUCCESS=>'处理成功',
        self::STATUS_PARAMS_MISS =>'参数缺失:%s',
        self::STATUS_PARAMS_ERROR =>'参数错误:%s',
        self::ORDER_NOT_EXIST=>'订单不存在',
        self::ORDER_UPDATE_ERROR=>'订单更新失败:%s',
        self::ORDER_COMPLETE_ERROR=>'订单确认失败:%s',
        self::ORDER_DELIVERY_OUT_ERROR=>'订单出库失败:%s',
        self::ADD_ORDER_LOG_ERROR=>'订单日志增加失败:%s',
        self::DOWNLOAD_ERROR=>'下载失败:%s',
        self::CUSTOMER_SERVICE_OPERATION_ERROR=>'售后操作错误:%s',
        self::WITHDRAW_ADD_LOG_ERROR=>'提现日志保存失败',
        self::WITHDRAW_AUDIT_ERROR=>'提现审核失败:%s',
        self::WITHDRAW_REFUND_ERROR=>'提现退款失败:%s',
        self::WITHDRAW_DEAL_ERROR=>'启动打款失败:%s',
        self::SYSTEM_OPTION_MODIFY_ERROR=>'配置项修改失败:%s',
        self::DELIVERY_OUT_ERROR=>'发货失败:%s',
        self::UPLOAD_WEIGHT_ERROR=>'校准重量失败:%s',
        self::DRAW_COUPON_ERROR=>'领取优惠券失败:%s',
        self::DRAW_BONUS_ERROR=>'领取奖励金失败:%s',
        self::CUSTOMER_INVITATION_ACTIVITY_SETTLE_ERROR=>'邀请活动结算异常:%s',
        self::ORDER_CANCEL_ERROR=>'订单取消错误:%s',
        self::GOODS_SKU_ALLIANCE_AUDIT_ERROR=>'联盟商品审核失败:%s',
        self::SORT_NOT_EXIST=>'分类不存在',
        self::CLAIM_BALANCE_ERROR=>'扣款失败:%s',
        self::DELIVERY_GOODS_DELIVERY_CHANNEL_ERROR=>'给团长批量投放商品失败:%s',
        self::WITHDRAW_ERROR=>'提现错误:%s',
        self::STORAGE_SKU_BIND_ERROR=>'仓库商品绑定失败:%s',
        self::STORAGE_UN_BIND=>'未绑定仓库',

        self::ADMIN_USER_SESSION_ERROR=>'Session错误',
        self::ADMIN_USER_NOT_EXIST=>'管理用户不存在',
        self::ADMIN_USER_NOT_ACTIVE=>'管理用户被禁用',

        self::REPOSITORY_CALL_ERROR=>'[%s]依赖调用失败:%s',
        self::RECORD_ITEM_DISABLE=>'已禁用',
        self::STATUS_BUSY_ERROR =>'系统繁忙',
    ];

    public static function createExp($code){
        $message = "未知错误";
        if (key_exists($code,self::$exceptionArr)){
            $message = self::$exceptionArr[$code];
        }
        return new BBusinessException($message,$code);
    }

    public static function createExpWithParams($code,...$params){
        $message = "未知错误";
        if (key_exists($code,self::$exceptionArr)){
            $message = sprintf(self::$exceptionArr[$code],...$params);
        }
        return new BBusinessException($message,$code);
    }
}