<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace backend\utils;
use backend\models\BackendCommon;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use backend\utils\params\RenderParams;
use Yii;

class BExceptionAssert
{

    public static function assertNotEmpty($obj, $exception){
        if ($obj===null||empty($obj)){
            self::commonProcess($exception);
        }
    }

    public static function assertEmpty($obj, $exception){
        if (!empty($obj)){
            self::commonProcess($exception);
        }
    }

    public static function assertNotBlank($obj, $exception){
        if ($obj===null||$obj===''){
            self::commonProcess($exception);
        }
    }

    public static function assertNotNull($obj, $exception){
        if ($obj===null){
            self::commonProcess($exception);
        }
    }

    public static function assertNull($obj, $exception){
        if ($obj!==null){
            self::commonProcess($exception);
        }
    }

    public static function assertTrue($bool,$exception){
        if ($bool===false){
            self::commonProcess($exception);
        }
    }


    public static function assertNotBlankAndNotEmpty($obj, $e){
        self::assertNotBlank($obj,$e);
        $arr = explode(",", $obj);
        self::assertNotEmpty($arr,$e);
        return $arr;
    }

    private static function commonProcess($exception){
        if ($exception instanceof RenderParams){
            BackendCommon::showErrorInfo($exception->message);
            echo $exception->controller->render($exception->view, $exception->params);
            Yii::$app->response->send();
            Yii::$app->end();
        }
        else if ($exception instanceof RedirectParams){
            BackendCommon::showErrorInfo($exception->message);
            Yii::$app->response->redirect($exception->url, 302)->send();
            Yii::$app->end();
        }
        else if ($exception instanceof BBusinessException){
            //BackendCommon::showErrorInfo($exception->getMessage());
            throw $exception;
        }
    }
}