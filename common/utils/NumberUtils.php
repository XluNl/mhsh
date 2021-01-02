<?php


namespace common\utils;


class NumberUtils
{

    public static function notNullAndPositiveInteger($integer){
        if ($integer!=null&&$integer>0){
            return true;
        }
        return false;
    }

    public static function isNumeric($num){
        if ($num===null){
            return false;
        }
        if (is_numeric($num)){
            return true;
        }
        else{
            return false;
        }
    }
}