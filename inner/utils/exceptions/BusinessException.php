<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace inner\utils\exceptions;
use Throwable;

class BusinessException extends \Exception
{

    /**
     * BusinessException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return BusinessException
     */
    public static  function create($message = "", $code = 0, Throwable $previous = null){
        return new BusinessException($message,$code,$previous);
    }
}