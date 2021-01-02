<?php

namespace common\utils;
class StringUtils
{
    public static function isBlank($str){
        if ($str===null||(is_string($str)&&trim($str)==='')){
            return true;
        }
        return false;
    }

    public static function isNotBlank($str){
        return !self::isBlank($str);
    }

    public static function isEmpty($array){
        if ($array===null||empty($array)){
            return true;
        }
        return false;
    }

    public static function isNotEmpty($array){
        return !self::isEmpty($array);
    }

    public static function containsSubString($sourceStr,$subStr){
        if (strpos($sourceStr,$subStr)!==FALSE){
            return true;
        }
        return false;
    }

    public static function removeSubString($src,...$substrings){
        if (empty($substrings)){
            return $src;
        }
        foreach ($substrings as $substring){
            $src = str_replace($substring,"",$src);
        }
        return $src;
    }

    public static function fullZeroForNumber($num,$length){
        return str_pad($num,$length,"0",STR_PAD_LEFT);
    }

    public static function startsWith($searchStr, $str){
        return strncmp($searchStr, $str, strlen($str)) === 0;
    }

    public static function endsWith($searchStr, $str){
        return $str === '' || substr_compare($searchStr, $str, -strlen($str)) === 0;
    }

    public static function isBlankOrEmpty($array){
        if (self::isBlank($array)||self::isEmpty($array)){
            return true;
        }
        return false;
    }

    public static function isNotBlankAndNotEmpty($array){
        return !self::isBlankOrEmpty($array);
    }


    /**
     * 找到第一个非空的
     * @param mixed ...$strings
     * @return mixed|string
     */
    public static function filterFirstNotBlank(...$strings){
        if (empty($strings)){
            return "";
        }
        foreach ($strings as $str){
            if (self::isNotBlank($str)){
                return $str;
            }
        }
        return "";
    }
}