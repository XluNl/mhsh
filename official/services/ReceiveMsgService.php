<?php


namespace official\services;

use common\utils\StringUtils;
use official\utils\ExceptionAssert;
use official\utils\StatusCode;
use Yii;

class ReceiveMsgService
{


    public static function event($message)
    {
        switch ($message['Event']) {
            // 关注事件
            case 'subscribe' :
                return self::subscribeEvent($message);
                break;
            // 取消关注事件
            case 'unsubscribe' :
                //TODO 暂时不处理
                break;
            // 二维码扫描事件
            case 'SCAN' :
                //TODO 暂时不处理
                break;
            // 上报地理位置事件
            case 'LOCATION' :
                //TODO 暂时不处理
                break;
            // 自定义菜单(点击)事件
            case 'CLICK' :
                //TODO 暂时不处理
                break;
        }
        return false;
    }

    private static function subscribeEvent($message){
        $openId = $message['FromUserName'];
        AccountService::login($openId);
        return true;
    }


    public static function completeAllSubscribe(){
        try {
            $nextOpenId = null;
            do{
                $res = Yii::$app->officialWechat->app->user->list($nextOpenId);  // $nextOpenId 可选
                ExceptionAssert::assertNotEmpty($res,StatusCode::createExp(StatusCode::OFFICIAL_ACCOUNT_LOGIN_ERROR));
                if (isset($res['data']['openid'])){
                    foreach ($res['data']['openid'] as $v){
                        AccountService::login($v);
                    }
                }
                $nextOpenId = $res['next_openid'];
            }
            while (StringUtils::isNotEmpty($nextOpenId));
            return true;
        }
        catch (\Exception $e){
            Yii::error("completeAllSubscribe error",$e);
            return false;
        }
    }

}