<?php


namespace alliance\utils;
use yii\helpers\Json;

class RestfulResponse
{

    public $status;
    public $code;
    public $data;
    public $error;

    /**
     * RestfulResponse constructor.
     * @param $status
     * @param $code
     * @param $data
     * @param $error
     */
    public function __construct($status, $code, $data, $error)
    {
        $this->status = $status;
        $this->code = $code;
        $this->data = $data;
        $this->error = $error;
    }


    public static function errorBusyError($exception){
        return Json::htmlEncode(new RestfulResponse(false,StatusCode::STATUS_BUSY_ERROR,null,$exception->getMessage()));
    }

    public static function error($exception,$data=null){
        return Json::htmlEncode(new RestfulResponse(false,$exception->getCode(),$data,$exception->getMessage()));
    }

    public static function success($data){
        return Json::encode(new RestfulResponse(true,StatusCode::STATUS_SUCCESS,$data,""));
    }

}