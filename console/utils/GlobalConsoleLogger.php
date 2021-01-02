<?php


namespace console\utils;


class GlobalConsoleLogger
{

    private static $logger = null;

    public static function initLogger($title=null,$logTag=null){
        $log = self::getLogger();
        $log->title = $title;
        $log->logTag = $logTag;
    }

    /**
     * @return ConsoleResult
     */
    private static function getLogger(){
        if (GlobalConsoleLogger::$logger === null){
            GlobalConsoleLogger::$logger = ConsoleResult::create();
        }
        return GlobalConsoleLogger::$logger;
    }

    /**
     * 每个日志打一行
     * @param mixed ...$params
     * @return ConsoleResult
     */
    public static function println(...$params){
        return self::getLogger()->println(...$params);
    }

    /**
     * 打印异常信息
     * @param \Exception $error
     * @return ConsoleResult
     */
    public static function printException(\Exception $error){
        return self::getLogger()->printException($error);
    }

    /**
     * 多个日志打一行
     * @param mixed ...$params
     * @return ConsoleResult
     */
    public static function printNo(...$params){
        return self::getLogger()->printNo(...$params);
    }

    public static function showLog(){
        self::getLogger()->showLog();
    }


}