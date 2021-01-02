<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace business\utils;

class AssertDefaultUtil
{
    public static function setNotEmpty(&$obj, $defaultValue){
        if (empty($obj)){
            $obj = $defaultValue;
        }
    }
    public static function setNotNull(&$obj, $defaultValue){
        if ($obj==null){
            $obj = $defaultValue;
        }
    }
}