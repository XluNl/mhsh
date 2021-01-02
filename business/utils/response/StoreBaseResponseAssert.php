<?php


namespace business\utils\response;



use business\utils\ExceptionAssert;
use business\utils\StatusCode;

class StoreBaseResponseAssert
{
    /**
     * @param $response
     * @return mixed
     */
    public static function assertSuccessData($response){
        ExceptionAssert::assertNotEmpty($response,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"仓库","无结果"));
        ExceptionAssert::assertTrue($response['code']==0,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"仓库",$response['msg']));
        return $response['data'];
    }
}