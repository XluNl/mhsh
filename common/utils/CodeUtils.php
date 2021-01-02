<?php


namespace common\utils;


class CodeUtils
{

    public static function rmUTF8UnSupportCharacter($str){
        if (empty($str)){
            return "";
        }
        return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $str);
    }
}