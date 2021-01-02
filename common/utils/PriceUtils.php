<?php


namespace common\utils;


class PriceUtils
{
    public static function validateInput($price){
        if ($price<0){
            return false;
        }
        if (!is_numeric($price)||strpos($price,".")!==false){
            return false;
        }
        return intval($price)%10==0;
    }

    public static function accurateToTen($num){
        return intval($num/10)*10;
    }

    public static function accurateTo2Point($num){
        return intval($num*100)/100;
    }

    public static function accurateTo3Point($num){
        return intval($num*1000)/1000;
    }

    public static function accurateTo4Point($num){
        return intval($num*10000)/10000;
    }
}