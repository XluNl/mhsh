<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;

class SessionService extends \common\services\SessionService
{
    /**
     * 反序列session
     * @param $data
     * @return array
     */
    public static function unSerialize($data){
        BExceptionAssert::assertNotBlank($data,BStatusCode::createExp(BStatusCode::ADMIN_USER_SESSION_ERROR));
        try {
            $data = self::unSerializeSessionData($data);
        }
        catch (\Exception $e){
            BExceptionAssert::assertNotBlank(false,BStatusCode::createExp(BStatusCode::ADMIN_USER_SESSION_ERROR));
        }
        BExceptionAssert::assertNotEmpty($data,BStatusCode::createExp(BStatusCode::ADMIN_USER_SESSION_ERROR));
        BExceptionAssert::assertNotBlank($data['__id'],BStatusCode::createExp(BStatusCode::ADMIN_USER_SESSION_ERROR));
        return $data;
    }
}