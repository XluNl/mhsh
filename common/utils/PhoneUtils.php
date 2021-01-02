<?php


namespace common\utils;


class PhoneUtils
{

    public static function batchReplacePhoneMark(&$list, $src,$dist=null){
        if (empty($list)){
            return;
        }
        foreach ($list as $k=>$v){
            self::replacePhoneMark($v,$src,$dist);
            $list[$k] = $v;
        }
    }

    public static function replacePhoneMark(&$arr,$src,$dist=null){
        if (empty($arr)||!key_exists($src,$arr)){
            return;
        }
        if (StringUtils::isBlank($dist)){
            $dist = $src;
        }
        $arr[$dist]=self::dataDesensitization($arr[$src],3, 4);
    }

    public static function dataDesensitization($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string)){
            return false;
        }
        $strArr = array();
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strArr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strLen = count($strArr);
        $begin = $start >= 0 ? $start : ($strLen - abs($start));
        $end = $last = $strLen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } elseif ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; $i++) {
            $strArr[$i] = $re;
        }
        if ($begin >= $end || $begin >= $last || $end > $last) return false;
        return implode('', $strArr);
    }

    /**
     * 校验手机号格式
     * @param $phone
     * @return bool
     */
    public static function checkPhoneFormat($phone){
        if (empty($phone)){
            return false;
        }
        if(preg_match("/^1[3-9]\d{9}$/", $phone)){
            return true;
        }
        return false;
    }
}