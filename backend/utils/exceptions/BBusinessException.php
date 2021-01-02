<?php
namespace backend\utils\exceptions;
use Throwable;

class BBusinessException extends \Exception
{
    /**
     * BBusinessException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function updateMessage($message){
        $this->message = $message;
        return $this;
    }

    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return BBusinessException
     */
    public static  function create($message = "", $code = 0, Throwable $previous = null){
        return new BBusinessException($message,$code,$previous);
    }

}