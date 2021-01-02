<?php


namespace common\utils;


use yii\helpers\ArrayHelper;

class ArrayUtils
{
    /**
     * @param $key
     * @param array $arr
     * @return array
     */
    public static function getColumnWithoutNull($key,array $arr){
        if (empty($arr)){
            return [];
        }
        $cols = ArrayHelper::getColumn($arr,$key);
        $res = [];
        foreach ($cols as $k=>$v){
            if ($v===null&&in_array($v,$res)){
                $res[] = $v;
            }
        }
        return $cols;
    }


    public static function getModelColumnWithoutNull($key,array $arr){
        if (empty($arr)){
            return [];
        }
        $res = [];
        foreach ($arr as $k=>$v){
            $vv = $v->$key;
            if ($vv!==null&&!in_array($vv,$res)){
                $res[] = $vv;
            }
        }
        return $res;
    }


    public static function mergeMap(array $distArr,$key,array $srcArr){
        $arr1 = array_values($distArr);
        $arr2 = array_values($srcArr);
        for ($i=count($arr1);$i<count($arr2);$i++){
            $arr1[] = [];
        }
        foreach ($arr1 as $k=>$v){
            $v[$key] = $arr2[$k];
            $arr1[$k] = $v;
        }
        return $arr1;
    }

    /**
     *  二维排序
     * @param $data
     * @param $filed1
     * @param $type1
     * @param $filed2
     * @param $type2
     * @return mixed
     */
    public static function sortByTwoFiled($data, $filed1, $type1, $filed2, $type2)
    {
        if (count($data) <= 0) {
            return $data;
        }
        $tArray1 = [];
        $tArray2 =  [];
        foreach ($data as $key => $value) {
            $tArray1[$key] = $value[$filed1];
            $tArray2[$key] = $value[$filed2];
        }
        array_multisort($tArray1, $type1, $tArray2, $type2, $data);
        return $data;
    }

    /**
     * 建索引（空数组也行）
     * @param $arr
     * @param $key
     * @return array
     */
    public static function index($arr,$key){
        if (empty($arr)){
            return [];
        }
        return ArrayHelper::index($arr,$key);
    }

    /**
     * map多值
     * @param $array
     * @param $from
     * @param $to
     * @param mixed ...$tos
     * @return array
     */
    public static function map($array, $from, $to,...$tos){
        $res = [];
        foreach ($array as $v){
            $key = $v[$from];
            $value = $v[$to];
            if (!empty($tos)){
                foreach ($tos as $v2){
                    $value = $value.'-'.$v[$v2];
                }
            }
            $res[$key] = $value;
        }
        return $res;
    }


    /**
     * 过滤
     * @param $array
     * @param $to
     * @param mixed ...$tos
     * @return array
     */
    public static function subArray($array, $to,...$tos){
        if (empty($array)){
            return [];
        }
        $result = [];
        foreach ($array as $v){
            $item = [$to=>$v[$to]];
            foreach ($tos as $to2){
                $item[$to2]=$v[$to2];
            }
            $result[]= $item;
        }
        return $result;
    }

    public static function subValueAdd($array,$attr){
        $c = 0;
        foreach ($array as $v){
            $c += $v[$attr];
        }
        return $c;
    }


    public static function mapToArray($map,$kName,$vName){
        if (empty($map)){
            return [];
        }
        $res = [];
        foreach ($map as $k=>$v){
            $res[] = [$kName=>$k,$vName=>$v];
        }
        return $res;
    }

    public static function getSubColumnWithoutNull(array $arr,...$subAttrs){
        if (empty($arr)){
            return [];
        }
        $res = [];
        foreach ($arr as $k=>$v){
            $col = self::getSubAttr($v,...$subAttrs);
            if ($col!==null){
                $res[] = $col;
            }
        }
        return $res;
    }

    public static function getSubAttr($arr,...$subAttrs){
        $t = $arr;
        foreach ($subAttrs as $subAttr){
            if (key_exists($subAttr,$t)){
                $t = $t[$subAttr];
            }
            else{
                return null;
            }
        }
        return $t;
    }

    public static function setSubAttr(&$arr,$key,$value,...$subAttrs){
        $t = &$arr;
        foreach ($subAttrs as $subAttr){
            if (key_exists($subAttr,$t)){
                $t = &$t[$subAttr];
            }
            else{
                return null;
            }
        }
        $t[$key] = $value;
    }


    /**
     * 通过值反查key，可空数组
     * @param $value
     * @param array $arr
     * @param $default
     * @return int|null|string
     */
    public static function getArrayKey($value,$arr = [],$default = null){
        if (empty($arr)){
            return $default;
        }
        foreach ($arr as $k=>$v){
            if ($value==$v){
                return $k;
            }
        }
        return $default;
    }



    /**
     * 获取array的第一个key
     * @param $arr
     * @param null $default
     * @return int|string|null
     */
    public static function getFirstKeyFromArray($arr,$default=null){
        if (empty($arr)){
            return $default;
        }
        foreach ($arr as $k=>$v){
            return $k;
        }
        return $default;
    }

    /**
     * 获取array的第一个value
     * @param $arr
     * @param null $default
     * @return mixed|null
     */
    public static function getFirstValueFromArray($arr,$default=null){
        if (empty($arr)){
            return $default;
        }
        foreach ($arr as $k=>$v){
            return $v;
        }
        return $default;
    }



    /**
     * 在数组中查找值，可空数组
     * @param $key
     * @param array|object $arr
     * @param string $default
     * @return mixed|string|integer
     */
    public static function getArrayValue($key, $arr ,$default = "未知")
    {
        if (is_array($arr)){
            if (empty($arr)||!array_key_exists($key,$arr)){
                return $default;
            }
            return $arr[$key];
        }
        else{
            if ($arr===null){
                return $default;
            }
            if (array_key_exists($key,$arr)){
                return $arr[$key];
            }
            if (!$arr->hasAttribute($key)){
                return $default;
            }
            return $arr->$key;
        }
    }

    /**
     * 计算数组的比例
     * @param array $arr 无key的数组
     * @param int $proportion 总量，默认100
     * @return array
     */
    public static function calculateArrayProportion(array $arr,$proportion=100){
        if (empty($arr)){
            return [];
        }
        $sum = 0;
        foreach ($arr as $v){
            $sum += $v;
        }
        $res = [];
        if ($sum==0){
            foreach ($arr as $v){
                $res[] = 0;
            }
            return $res;
        }
        $costProportion = 0;
        for ($i=0;$i<count($arr);$i++){
            if ($i==count($arr)-1){
                $res[] = $proportion - $costProportion;
            }
            else{
                $t = round($proportion*$arr[$i]*1.0/$sum);
                $costProportion += $t;
                $res[] = $costProportion;
            }
        }
        return $res;
    }


    /**
     * 数组内元素都乘以$multiplier
     * @param array $arr
     * @param $multiplier
     * @return array
     */
    public static function multiplyArray(array $arr,$multiplier){
        if (empty($arr)){
            return [];
        }
        $res = [];
        foreach ($arr as $v){
            $res[] = $v*$multiplier;
        }
        return $res;
    }

    /**
     * 数组内元素都除以$divisor
     * @param array $arr
     * @param $divisor
     * @return array
     * @throws \Exception
     */
    public static function divideArray(array $arr,$divisor){
        if (empty($arr)){
            return [];
        }
        if ($divisor==0){
            throw new \Exception("除数不能为0");
        }
        $res = [];
        foreach ($arr as $v){
            $res[] = $v/$divisor;
        }
        return $res;
    }
}