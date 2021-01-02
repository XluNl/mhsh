<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace business\utils;
use Yii;

class ExceptionAssert
{

    public static function assertNotEmpty($obj, $e){
        if ($obj===null||empty($obj)){
            self::commonProcess($e);
        }
    }

    public static function assertKeyExistAndNotBlack($obj,$attr, $e){
        if ($obj===null||!key_exists($attr,$obj)||$obj[$attr]===''){
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

    public static function assertBlank($obj, $e){
        if ($obj!==null&&$obj!==''){
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
    public static function assertNotBlankAndNotEmpty($obj, $e){
        self::assertNotBlank($obj,$e);
        $arr = explode(",", $obj);
        self::assertNotEmpty($arr,$e);
        return $arr;
    }

    private static function commonProcess($e){
        Yii::error($e->getMessage());
        throw $e;
    }

}