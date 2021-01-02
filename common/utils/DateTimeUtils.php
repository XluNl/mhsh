<?php

namespace common\utils;
class DateTimeUtils
{

    /**
     * 当日第一秒
     * @param $str string
     * @param bool $isStr
     * @return false|int
     */
    public static function startOfDayLong($str, $isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date("Y-m-d 00:00:00",$dateTime);
        return strtotime($dateStr);
    }

    /**
     * 当天最后一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function endOfDayLong($str, $isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date("Y-m-d 23:59:59",$dateTime);
        return strtotime($dateStr);
    }

    /**
     *  当周第一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function startOfWeekLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date('Y-m-d 00:00:00', ($dateTime - ((date('w',$dateTime) == 0 ? 7 : date('w',$dateTime)) - 1) * 24 * 3600));
        return strtotime($dateStr);
    }

    /**
     *  当周最后一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function endOfWeekLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date('Y-m-d 23:59:59', ($dateTime + (7 - (date('w',$dateTime) == 0 ? 7 : date('w',$dateTime))) * 24 * 3600));
        return strtotime($dateStr);
    }


    /**
     *  当月第一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function startOfMonthLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date('Y-m-d 00:00:00', strtotime(date('Y-m', $dateTime) . '-01 00:00:00'));
        return strtotime($dateStr);
    }

    /**
     *  当月最后一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function endOfMonthLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr =  date('Y-m-d 23:59:59', strtotime(date('Y-m', $dateTime) . '-' . date('t',$dateTime) . ' 00:00:00'));
        return strtotime($dateStr);
    }

    /**
     *  当年第一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function startOfYearLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr = date('Y-01-01 00:00:00', $dateTime);
        return strtotime($dateStr);
    }

    /**
     *  当年最后一秒
     * @param $str
     * @param bool $isStr
     * @return false|int
     */
    public static function endOfYearLong($str,$isStr=true){
        if ($isStr){
            $dateTime = strtotime($str);
        }
        else{
            $dateTime = $str;
        }
        $dateStr =  date('Y-12-31 23:59:59', $dateTime);
        return strtotime($dateStr);
    }







    /**
     * longDate解析成标准格式时间
     * @param $longTime
     * @return false|string
     */
    public static function parseStandardWLongDate($longTime=null){
        if (StringUtils::isBlank($longTime)){
            $longTime = time();
        }
        return date('Y-m-d H:i:s',$longTime);
    }

    /**
     * strDate解析成标准格式时间
     * @param $strTime
     * @return false|string
     */
    public static function parseStandardWStrDate($strTime){
        if (StringUtils::isBlank($strTime)){
            $longTime = time();
        }
        else{
            $longTime = strtotime($strTime);
        }
        return date('Y-m-d H:i:s',$longTime);
    }

    /**
     * 判断是否在此期时间之间
     * @param $nowTime integer
     * @param $startTimeStr string
     * @param $endTimeStr string
     * @return boolean
     */
    public static function isBetween($nowTime,$startTimeStr,$endTimeStr){
        $startTime = strtotime($startTimeStr);
        $endTime = strtotime($endTimeStr);
        if ($nowTime>=$startTime&&$nowTime<=$endTime){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 判断是否在此期时间之间
     * @param $nowTime string
     * @param $startTimeStr string
     * @param $endTimeStr string
     * @return bool
     */
    public static function isBetweenStr($nowTime,$startTimeStr,$endTimeStr){
        $startTime = strtotime($startTimeStr);
        $endTime = strtotime($endTimeStr);
        $nowTime = strtotime($nowTime);
        if ($nowTime>=$startTime&&$nowTime<=$endTime){
            return true;
        }
        else{
            return false;
        }
    }


    /**
     * 判断$str1>$str2
     * @param $str1 string
     * @param $str2 string
     * @return bool
     */
    public static function biggerStr($str1, $str2){
        $time1 = strtotime($str1);
        $time2 = strtotime($str2);
        if ($time1>$time2){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 前一天
     * @param $time
     * @return false|string
     */
    public static function yesterday($time){
        if (!is_numeric($time)){
            $time = strtotime($time);
        }
        return date('Y-m-d',$time-3600*24);
    }

    /**
     * 格式化日期
     * @param $time
     * @param bool $isStr
     * @return false|string
     */
    public static function formatYearAndMonthAndDay($time, $isStr=true){
        if ($isStr){
            $time = strtotime($time);
        }
        return date('Y-m-d',$time);
    }

    /**
     * 格式化日期（年月日）(中文版)
     * @param $time
     * @param bool $isStr
     * @return false|string
     */
    public static function formatYearAndMonthAndDayChinese($time, $isStr=true){
        if ($isStr){
            $time = strtotime($time);
        }
        return date('Y年m月d日',$time);
    }


    /**
     * strDate解析成YY月DD日
     * @param $strTime
     * @return false|string
     */
    public static function parseMonthAndDayByStrDate($strTime){
        return date('m月d日',strtotime($strTime));
    }

    /**
     * 2020/01/01
     * @param $strTime
     * @return false|string
     */
    public static function formatYearAndMonthAndDaySlash($strTime){
        return date('Y/m/d',strtotime($strTime));
    }

    /**
     * 格式化日期(月日时分秒)(中文版)
     * @param $time
     * @param bool $isStr
     * @return false|string
     */
    public static function formatMonthAndDayAndHourAndMinuteAndSecondChinese($time, $isStr=true){
        if ($isStr){
            $time = strtotime($time);
        }
        return date('m月d日H时i分s秒',$time);
    }


    /**
     * 后一天
     * @param $time
     * @return false|string
     */
    public static function tomorrow($time){
        if (!is_numeric($time)){
            $time = strtotime($time);
        }
        return date('Y-m-d',$time+3600*24);
    }

    /**
     * 校验时间格式
     * @param $time
     * @return bool
     */
    public static function checkFormat($time){
        return strtotime($time)!==0;
    }


    /**
     * 验证日期格式（yyyy-MM-dd）
     * @param $dateStr
     * @return bool
     */
    public static function checkFormatYmd($dateStr){
        if (StringUtils::isBlank($dateStr)){
            return false;
        }
        if (preg_match("/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/",$dateStr)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证时间格式（yyyy-MM-dd HH:mm:ss）
     * @param $dateStr
     * @return bool
     */
    public static function checkFormatYmdHms($dateStr){
        if (StringUtils::isBlank($dateStr)){
            return false;
        }
        if (preg_match("/^((\d{2}(([02468][048])|([13579][26]))[\-\/\s]?((((0?[13578])|(1[02]))[\-\/\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\-\/\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\-\/\s]?((0?[1-9])|([1-2][0-9])))))|(\d{2}(([02468][1235679])|([13579][01345789]))[\-\/\s]?((((0?[13578])|(1[02]))[\-\/\s]?((0?[1-9])|([1-2][0-9])|(3[01])))|(((0?[469])|(11))[\-\/\s]?((0?[1-9])|([1-2][0-9])|(30)))|(0?2[\-\/\s]?((0?[1-9])|(1[0-9])|(2[0-8]))))))(\s((([0-1][0-9])|(2?[0-3]))\:([0-5]?[0-9])((\s)|(\:([0-5]?[0-9])))))?$/",$dateStr)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 校验时间格式（如果空也返回true）
     * @param $time
     * @return bool
     */
    public static function checkFormatIfNotBlack($time){
        if (StringUtils::isBlank($time)){
            return true;
        }
        return strtotime($time)!==0;
    }

    /**
     * 校验时间格式(Y-m-d)
     * @param $date
     * @return bool
     */
    public static function checkYearAndMonthAndDayFormat($date){
        return self::checkDateIsValid($date);
    }

    /**
     * 校验时间格式(Y-m-d)
     * @param $date
     * @return bool
     */
    public static function checkYearAndMonthAndDayFormatIfNotBlack($date){
        if (StringUtils::isBlank($date)){
            return true;
        }
        return self::checkDateIsValid($date);
    }

    /**
     * 校验时间格式(Y-m-d H:i:s)
     * @param $time
     * @return bool
     */
    public static function checkYearAndMonthAndDayAndHourAndMinuteAndSecondFormatIfNotBlack($time){
        if (StringUtils::isBlank($time)){
            return true;
        }
        return self::checkDateIsValid($time,["Y-m-d H:i:s"]);
    }

    /**
     * 输入年月，增减月份，输出年月
     * @param $yearMonthStr
     * @param $months
     * @return false|string
     */
    public static function plusMonth($yearMonthStr, $months){
        return date('Y-m',strtotime("{$yearMonthStr} {$months} month"));
    }

    /**
     * 输入年月日，增减日期，输出年月日
     * @param $yearMonthDayStr
     * @param $days
     * @return false|string
     */
    public static function plusDay($yearMonthDayStr, $days){
        return date('Y-m-d',strtotime("{$yearMonthDayStr} {$days} day"));
    }

    /**
     * 输入年月日，增减日期，输出年月日
     * @param $yearMonthDayStr
     * @param $hours
     * @return false|string
     */
    public static function plusHour($yearMonthDayStr, $hours){
        return date('Y-m-d H:i:s',strtotime("{$yearMonthDayStr} {$hours} hours"));
    }

    /**
     * 输入年月日，增减日期，输出年月日
     * @param $yearMonthDayStr
     * @param $minute
     * @return false|string
     */
    public static function plusMinute($yearMonthDayStr, $minute){
        return date('Y-m-d H:i:s',strtotime("{$yearMonthDayStr} {$minute} minutes"));
    }

    /**
     * 输入年月日，增减日期，输出年月日
     * @param $yearMonthDayStr
     * @param $seconds
     * @return false|string
     */
    public static function plusSecond($yearMonthDayStr, $seconds){
        return date('Y-m-d H:i:s',strtotime("{$yearMonthDayStr} {$seconds} seconds"));
    }

    /**
     * 格式化成年月（2019-06）
     * @param $dateTime
     * @param $isStr
     * @return false|string
     */
    public static function formatYearAndMonth($dateTime, $isStr=true){
        if ($isStr){
            $dateTime = strtotime($dateTime);
        }
        return date('Y-m',$dateTime);
    }

    /**
     * 格式化成年月(2019年06月)
     * @param $dateTime
     * @param bool $isStr
     * @return false|string
     */
    public static function formatYearAndMonthChinese($dateTime, $isStr=true){
        if ($isStr){
            $dateTime = strtotime($dateTime);
        }
        return date('Y年m月',$dateTime);
    }

    /**
     * 格式化时间（中文）
     * @param $time
     * @param bool $isStr
     * @return false|string
     */
    public static function parseChineseDateTime($time, $isStr=true){
        if ($isStr){
            $time = strtotime($time);
        }
        return date('Y年m月d日H时i分s秒',$time);
    }


    /**
     * 校验日期是否准确
     * @param $date
     * @param array $formats
     * @return bool
     */
     private static function checkDateIsValid($date, $formats = ["Y-m-d"]) {
        $unixTime = strtotime($date);
        if (!$unixTime) {
            //strtotime转换不对，日期格式显然不对。
            return false;
        }
        //校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是否在时间区间之内（21:00:00~08:00:00）
     * @param $dayStartTimeStr string
     * @param $dayEndTimeStr string
     * @return bool
     */
    public static function inDayBetween($dayStartTimeStr,$dayEndTimeStr){
        $nowTime = time();
        $dateStr = date('H:i:s',$nowTime);
        if ($dayStartTimeStr>$dayEndTimeStr){
            if ($dateStr>=$dayStartTimeStr||$dateStr<=$dayEndTimeStr){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            if ($dateStr>=$dayStartTimeStr&&$dateStr<=$dayEndTimeStr){
                return true;
            }
            else{
                return false;
            }
        }

    }
}