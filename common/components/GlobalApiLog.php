<?php
namespace common\components;
use Yii;
use yii\base\Component;
use common\models\ApiLog;
/**
 * 
 */
class GlobalApiLog extends Component
{   
    
    public static function apiLog($event=null)
    {   
        $error = Yii::$app->errorHandler->exception;
        // 线上环境 只记录异常信息
        if(YII_ENV == 'prod' && empty($error)){
            return;
        }
        $data = self::baseFormat();
        $getError = function($error){
            $file = $error->getFile();
            $line = $error->getLine();
            $message = $error->getMessage();
            $code = $error->getCode();
            $err_msg = $message . " [file:{$file}][line:{$line}][code:{$code}][url:{$_SERVER['REQUEST_URI']}][POST_DATE:" . http_build_query($_POST) . "]";
            return $err_msg;
        };
        if($error instanceof \Error){
            $responseData = $getError($error);
            $pathInfo = explode('?',$_SERVER['REQUEST_URI']);
            $data['action'] = empty($data['action'])?$pathInfo[0]:$data['action'];
        }
        else{
            if(YII_DEBUG && $error instanceof \Exception ){
                $responseData =  $getError($error);
            }else{
                if($event){
                    $responseData = $event->sender->data;
                }else{
                    $responseData = Yii::$app->response->data;
                }
            }
        }
        if(Yii::$app->controller->module->id == "debug"){
            return;
        }

        $requestParmes['params'] = array_merge(Yii::$app->request->get(),Yii::$app->request->post());
        $requestParmes['url'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $data['request'] = $requestParmes;
        $data['response'] = $responseData;
        self::saveDblog($data);
    }

    public static function baseFormat(){
        $ip = Yii::$app->request->getRemoteIP();
        $appId = Yii::$app->id;
        $appModule = Yii::$app->controller->module->id;
        $appControllerId = Yii::$app->controller->id;
        $appActionId = Yii::$app->controller->action->id;
        return [
             'ip'         => $ip,
             'url'        => $appModule."/".$appControllerId."/".$appActionId,
             'app_id'      => $appId,
             'module'  => $appModule,
             'controller' =>$appControllerId,
             'action'     =>$appActionId,
             'env' =>YII_ENV
        ];
    }

    public static function saveDblog($data){
        $data['request'] = is_array($data['request'])?json_encode($data['request']):$data['request'];
        $data['response'] = is_array($data['response'])?json_encode($data['response']):$data['response'];
        $model = new ApiLog();
        $model->add($data);
    }

    public function saveLog($data)
    {   
        $requestPars= array_merge(\Yii::$app->request->post(),\Yii::$app->request->get());
        $baseFormat = $this->baseFormat();
        $data = json_encode(['request'=>$requestPars,'route'=>$baseFormat,'res'=>$data],JSON_UNESCAPED_UNICODE);
        // if(YII_DEBUG)
        //     \Yii::info($res,"api");
        \Yii::info($data,"api");
    }

}