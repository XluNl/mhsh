<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace template\utils\exceptions;
use Throwable;

class SkipException extends \Exception
{
    public $flag;
    public $title;
    public $url;
    public $btnMsg;
    public $subUrl;
    public $subBtnMsg;

    /**
     * SkipException constructor.
     * @param $flag
     * @param $title
     * @param $url
     * @param $btnMsg
     * @param string $message
     * @param int $code
     * @param null $subUrl
     * @param null $subBtnMsg
     * @param Throwable|null $previous
     */
    public function __construct($flag, $title, $url, $btnMsg,$message = "", $code = 0, $subUrl=null, $subBtnMsg=null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->flag = $flag;
        $this->title = $title;
        $this->url = $url;
        $this->btnMsg = $btnMsg;
        $this->subUrl = $subUrl;
        $this->subBtnMsg = $subBtnMsg;
    }

    public function generateData(){
        $data = [];
        $data['flag'] = $this->flag;
        $data['title'] = $this->title;
        $data['url'] = $this->url;
        $data['btnMsg'] = $this->btnMsg;
        $data['subUrl'] = $this->subUrl;
        $data['subBtnMsg'] = $this->subBtnMsg;
        return $data;
    }

}