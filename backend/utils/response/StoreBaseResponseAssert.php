<?php


namespace backend\utils\response;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;

class StoreBaseResponseAssert
{
    /**
     * @param $response
     * @return mixed
     */
    public static function assertSuccessData($response){
        BExceptionAssert::assertNotEmpty($response,BStatusCode::createExpWithParams(BStatusCode::REPOSITORY_CALL_ERROR,"仓库","无结果"));
        BExceptionAssert::assertTrue($response['code']==0,BStatusCode::createExpWithParams(BStatusCode::REPOSITORY_CALL_ERROR,"仓库",$response['msg']));
        return $response['data'];
    }
}