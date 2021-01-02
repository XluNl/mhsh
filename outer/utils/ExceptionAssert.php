<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace outer\utils;
use Yii;

class ExceptionAssert
{

    public static function assertNotEmpty($obj, $e){
        if ($obj===null||empty($obj)){
            self::commonProcess($e);
        }
    }

    public static function assertEmpty($obj, $e){
        if (!empty($obj)){
            self::commonProcess($e);
        }
    }

    public static function assertNotBlank($obj, $e){
        if ($obj===null||$obj===''){
            self::commonProcess($e);
        }
    }

    public static function assertNotNull($obj, $e){
        if ($obj===null){
            self::commonProcess($e);
        }
    }

    public static function assertNull($obj, $e){
        if ($obj!==null){
            self::commonProcess($e);
        }
    }

    public static function assertTrue($bool,$e){
        if ($bool===false){
            self::commonProcess($e);
        }
    }
    private static function commonProcess($e){
        Yii::error($e->getMessage());
        throw $e;
    }
}