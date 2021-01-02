<?php


namespace business\utils;


class ProcessResult
{
    public $result;

    public $errorMsg;

    /**
     * ProcessResult constructor.
     * @param $result
     * @param $errorMsg
     */
    public function __construct($result, $errorMsg)
    {
        $this->result = $result;
        $this->errorMsg = $errorMsg;
    }

    /**
     * 判断是否成功处理
     * @return bool
     */
    public function isSuccess(){
        if ($this->result==true){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

}