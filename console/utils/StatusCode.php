<?php


namespace console\utils;

use console\utils\exceptions\BusinessException;

class StatusCode
{
    const STATUS_SUCCESS = 10000;
    const STATUS_PARAMS_MISS = 10001;
    const STATUS_PARAMS_ERROR = 10002;
    const STATUS_SKIP_MESSAGE = 10003;
    const ORDER_CANCEL_ERROR = 10004;
    const ORDER_COMPLETE_ERROR = 10005;
    const NOTIFY_STORAGE_DELIVERY_OUT_ERROR = 10006;
    const CLOSE_GROUP_ROOM = 11000;

    const RECORD_ITEM_DISABLE = 90001;
    const REPOSITORY_CALL_ERROR = 98000;
    const STATUS_BUSY_ERROR = 99999;
    public static $exceptionArr = [
        self::STATUS_SUCCESS=>'处理成功',
        self::STATUS_PARAMS_MISS =>'参数缺失:%s',
        self::STATUS_PARAMS_ERROR =>'参数错误:%s',
        self::STATUS_SKIP_MESSAGE =>'跳转',
        self::ORDER_CANCEL_ERROR=>'订单取消错误:%s',
        self::ORDER_COMPLETE_ERROR=>'订单完成错误:%s',
        self::NOTIFY_STORAGE_DELIVERY_OUT_ERROR=>'通知仓库扣库存失败:%s',
        self::RECORD_ITEM_DISABLE=>'已禁用',
        self::REPOSITORY_CALL_ERROR=>'[%s]依赖调用失败:%s',
        self::STATUS_BUSY_ERROR =>'系统繁忙',
        self::CLOSE_GROUP_ROOM=>'关闭团失败:%s',
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
}