<?php
namespace backend\utils\params;
/**
 * redirect url
 * Class RedirectParams
 * @property string $message
 * @property string $url
 */
class RedirectParams
{
    public $message;
    public $url;

    /**
     * RedirectParams constructor.
     * @param string $message
     * @param string $url
     */
    public function __construct($message, $url)
    {
        $this->message = $message;
        $this->url = $url;
    }

    public function updateMessage($message){
        $this->message = $message;
        return $this;
    }

    /**
     * @param $message
     * @param $url
     * @return RedirectParams
     */
    public static function create($message, $url)
    {
        return new RedirectParams($message, $url);
    }

}