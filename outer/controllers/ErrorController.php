<?php


namespace outer\controllers;


use outer\utils\exceptions\BusinessException;
use outer\utils\exceptions\SkipException;
use outer\utils\RestfulResponse;
use Yii;
use yii\log\FileTarget;
use yii\web\Controller;

class ErrorController extends Controller
{
    public function actionError()
    {
        $error = Yii::$app->errorHandler->exception;
        if ($error) {
            //记录错误信息到文件和数据库内
            $file = $error->getFile();
            $line = $error->getLine();
            $message = $error->getMessage();
            $code = $error->getCode();

            //把错误信息写入文件内，Yii已经封装好一个类Target
            $log = new FileTarget();
            //把文件放入runtime里面的logs文件夹内
            $log->logFile = Yii::$app->getRuntimePath() . "/logs/err.log";
            //设置错误信息放置格式
            $err_msg = $message . " [file:{$file}][line:{$line}][code:{$code}][url:{$_SERVER['REQUEST_URI']}][POST_DATE:" . http_build_query($_POST) . "]";

            //写入错误信息
            $log->messages[] = [
                $err_msg,
                1,
                'application',
                microtime(true)
            ];

            $log->export(); #执行
        }
        Yii::$app->response->statusCode = 200;
        if ($error instanceof BusinessException){
            return RestfulResponse::error($error);
        }
        else if ($error instanceof SkipException){
            return RestfulResponse::error($error,$error->generateData());
        }
        return RestfulResponse::errorBusyError($error);
     }

}