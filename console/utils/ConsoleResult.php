<?php


namespace console\utils;

use common\utils\StringUtils;

/**
 * @property $title
 * @property $message
 * @property $logTag
 * Class ConsoleResult
 * @package console\utils
 */
class ConsoleResult
{
    public $title;
    public $message;
    public $logTag;


    public static function create($title=null,$logTag=null){
        $model = new ConsoleResult();
        $model->logTag = $logTag;
        $model->title = $title;
        return $model;
    }

    public function println(...$params){
        foreach ($params as $param){
            $this->message .="{$param}\n";
        }
        return $this;
    }

    public function printNo(...$params){
        foreach ($params as $param){
            $this->message .="{$param}";
        }
        $this->message .= "\n";
        return $this;
    }

    public function printException(\Exception $error){
        //记录错误信息到文件和数据库内
        $file = $error->getFile();
        $line = $error->getLine();
        $message = $error->getMessage();
        $code = $error->getCode();
        $errMsg = $message."\n[file:{$file}][line:{$line}][code:{$code}]";
        $this->message .= "$errMsg\n";
        return $this;
    }

    public function showLog(){
        $outMsg = "{$this->title}\n{$this->message}";
        if (StringUtils::isNotBlank($this->logTag)){
            \Yii::warning("{$outMsg}",$this->logTag);
        }
        else{
            \Yii::warning("{$outMsg}");
        }
        echo $outMsg;
    }
}