<?php


namespace inner\utils;

use inner\utils\exceptions\BusinessException;
use inner\utils\exceptions\SkipException;

class StatusCode
{
    const STATUS_SUCCESS = 10000;
    const STATUS_PARAMS_MISS = 10001;
    const STATUS_PARAMS_ERROR = 10002;
    const STATUS_SKIP_MESSAGE = 10003;
    const ADD_ORDER_LOG_ERROR= 10004;

    const ORDER_NOT_EXIST = 20000;
    const DELIVERY_RECEIVE_ERROR = 20001;
    const STORAGE_SKU_BIND_ERROR = 20002;
    const STORAGE_DELIVERY_OUT_ERROR = 20003;
    const STORAGE_MODIFY_EXPECT_ARRIVE_TIME_ERROR = 20004;

    const STAR_EXCHANGE_BALANCE_ERROR = 20005;

    const NOT_LOGIN = 80001;
    const INNER_ACCOUNT_LOGIN_ERROR = 80002;
    const INNER_ACCOUNT_CREATE_ERROR = 80003;
    const USER_INFO_NOT_EXIST = 80004;
    const USER_INFO_REGISTER_ERROR = 80006;
    const INNER_ACCOUNT_DISABLED = 80008;
    const PHONE_USED = 80009;
    const ACCOUNT_CREATE_REPEAT= 80010;
    const RECORD_ITEM_DISABLE = 90001;

    const STATUS_BUSY_ERROR = 99999;
    public static $exceptionArr = [
        self::STATUS_SUCCESS=>'处理成功',
        self::STATUS_PARAMS_MISS =>'参数缺失:%s',
        self::STATUS_PARAMS_ERROR =>'参数错误:%s',
        self::STATUS_SKIP_MESSAGE =>'跳转:%s',
        self::ADD_ORDER_LOG_ERROR=>'记录订单日志错误:%s',

        self::ORDER_NOT_EXIST=>'订单不存在',
        self::DELIVERY_RECEIVE_ERROR=>'商品确认送达团长失败:%s',
        self::STORAGE_SKU_BIND_ERROR=>'仓库商品绑定失败:%s',
        self::STORAGE_DELIVERY_OUT_ERROR=>'仓库发货失败:%s',
        self::STORAGE_MODIFY_EXPECT_ARRIVE_TIME_ERROR=>'仓库修改预计送达时间失败:%s',
        self::STAR_EXCHANGE_BALANCE_ERROR=>'星球兑换余额失败:%s',

        self::NOT_LOGIN =>'请先登录',
        self::INNER_ACCOUNT_LOGIN_ERROR =>'公众号登录失败',
        self::INNER_ACCOUNT_CREATE_ERROR =>'公众号创建失败',
        self::ACCOUNT_CREATE_REPEAT =>'您已注册，请勿重复注册',
        self::INNER_ACCOUNT_DISABLED =>'公众号账户已禁用',
        self::USER_INFO_NOT_EXIST =>'用户信息不存在，请先注册',
        self::USER_INFO_REGISTER_ERROR =>'用户信息注册失败',
        self::PHONE_USED=>'手机号已注册，请更换手机号重试',
        self::RECORD_ITEM_DISABLE=>'已禁用',
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