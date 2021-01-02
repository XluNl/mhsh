<?php


namespace backend\components;


use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use backend\utils\exceptions\AjaxBusinessException;
use backend\utils\exceptions\BBusinessException;
use yii\web\ErrorHandler;

class ExceptionHandler extends ErrorHandler
{
    public function renderException($exception) {
        if (YII_DEBUG) {
            // 如果为开发模式时，可以按照之前的页面渲染异常，因为框架的异常渲染十分详细，方便我们寻找错误
            return parent::renderException($exception);
        } else {
            // 用户不适当的操作导致的异常
            if ($exception instanceof AjaxBusinessException) {
                http_response_code(200);
                echo BRestfulResponse::error($exception);
            }
            else if ($exception instanceof BBusinessException) {
                http_response_code(200);
                echo BRestfulResponse::error($exception);
            }
            else if ($exception instanceof \Exception) {
                echo BRestfulResponse::error($exception);
            }
            else{
                http_response_code(200);
                echo BRestfulResponse::errorBusyError(BStatusCode::createExp(BStatusCode::STATUS_BUSY_ERROR));
            }
        }
    }
}