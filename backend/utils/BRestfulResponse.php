<?php


namespace backend\utils;


use yii\helpers\Json;

class BRestfulResponse
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
        return Json::htmlEncode(new BRestfulResponse(false,BStatusCode::STATUS_BUSY_ERROR,null,$exception->getMessage()));
    }

    public static function error($exception,$data=null){
        return Json::htmlEncode(new BRestfulResponse(false,$exception->getCode(),$data,$exception->getMessage()));
    }

    public static function success($data){
        return Json::htmlEncode(new BRestfulResponse(true,BStatusCode::STATUS_SUCCESS,$data,""));
    }

}