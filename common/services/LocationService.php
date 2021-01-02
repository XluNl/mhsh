<?php


namespace common\services;


class LocationService
{

    /**
     * 解析距离文本
     * @param $distance
     * @return string
     */
    public static function resolveDistance($distance){
        if ($distance>=1000){
            $distance = $distance/1000.0;
            return round($distance, 2).'km';
        }
        else{
            return round($distance, 0).'m';
        }
    }



    /**
     * 两个已知经纬度之间的距离,单位为千米
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

}