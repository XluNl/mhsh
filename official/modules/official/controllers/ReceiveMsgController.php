<?php

namespace official\modules\official\controllers;

use common\utils\HttpClientUtils;
use official\components\FController;
use official\models\OfficialCommon;
use official\services\ReceiveMsgService;
use official\utils\RestfulResponse;
use Yii;
use yii\helpers\Json;

class ReceiveMsgController extends FController {

    public $enableCsrfValidation = false;

    public function actionIndex(){
        if (OfficialCommon::getHost()=='official.manhaoshenghuo.cn'){
            $resolveRequestUri = Yii::$app->request->getUrl();
            $postBody = Yii::$app->request->getRawBody();
            self::notifyOther("https://official-test.manhaoshenghuo.cn".$resolveRequestUri,$postBody);
        }
        $app = Yii::$app->officialWechat->app;
        $app->server->push(function ($message) {
            try {
                switch ($message['MsgType']) {
                    case 'event' : // '收到事件消息';
                        $reply = ReceiveMsgService::event($message);
                        break;
                    case 'text' : //  '收到文字消息';
                        $reply = null;
                        break;
                    default : // ... 其它消息(image、voice、video、location、link、file ...)
                        $reply = false;
                        break;
                }
                return $reply;
            }
            catch (\Exception $e){
                if (YII_DEBUG) {
                    return $e->getMessage();
                }
                return '系统出错，请联系管理员';
            }
        });
        // 将响应输出
        $response = $app->server->serve();
        $response->send();
        exit();
    }

    public function actionCompleteSubscribe(){
        $res = ReceiveMsgService::completeAllSubscribe();
        return RestfulResponse::success($res);
    }

    private static function notifyOther($url, $request){
        try {
            $response = HttpClientUtils::postXml($url,$request);
            Yii::info("notify ReceiveMsg success",Json::encode($response));
        }
        catch (\Exception $e){
            Yii::error("notify ReceiveMsg error",$e->getMessage());
            //throw BusinessException::create($e->getMessage());
        }
    }
}