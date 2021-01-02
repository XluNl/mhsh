<?php

namespace common\utils;
class MathUtils
{

    public static function calcGrow($nowValue, $frontValue)
    {
        $nowValue = (int)$nowValue;
        $frontValue = (int)$frontValue;
        if ($nowValue == $frontValue) {
            return "无变动";
        } elseif ($frontValue == 0 && $nowValue > 0) {
            return "100%";
        } elseif ($nowValue == 0 && $frontValue > 0) {
            return "-100%";
        } else if ($nowValue > $frontValue) {
            $c = round(($nowValue - $frontValue) / $frontValue, 2);
            $c = $c * 100;
            return $c . "%";
        } else if ($nowValue < $frontValue) {
            $c = round(($nowValue - $frontValue) / $frontValue, 2);
            $c = $c * 100;
            return $c . "%";
        } else {
            return "系统错误";
        }
    }
}