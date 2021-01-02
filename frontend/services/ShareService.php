<?php


namespace frontend\services;


use EasyWeChat\Kernel\Http\StreamResponse;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;

class ShareService
{
    public static function generate($scene,$page,$width){
        $response = Yii::$app->frontendWechat->miniProgram->app_code->getUnlimit($scene,['width'=>$width,'page'=>$page] );
        ExceptionAssert::assertTrue($response instanceof StreamResponse,StatusCode::createExp(StatusCode::CODE_GENERATE_ERROR));
        return $response->getBody()->getContents();
    }
}