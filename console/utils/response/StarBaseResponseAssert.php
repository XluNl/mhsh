<?php


namespace console\utils\response;


use console\utils\ExceptionAssert;
use console\utils\StatusCode;

class StarBaseResponseAssert
{
    /**
     * @param $response
     * @return mixed
     */
    public static function assertSuccessData($response){
        ExceptionAssert::assertNotEmpty($response,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"星球","无结果"));
        ExceptionAssert::assertTrue($response['success']==true,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"星球",$response['msg']));
        return $response['data'];
    }
}