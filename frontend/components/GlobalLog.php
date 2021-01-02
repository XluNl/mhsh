<?php
namespace frontend\components;

class GlobalLog{
	private static $instance = null;
	public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function baseFormat(){
        return [
             'ip'         => \Yii::$app->request->getRemoteIP(),
             'url'        => \Yii::$app->controller->module->id."/".\Yii::$app->controller->id."/".\Yii::$app->controller->action->id
        ];
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
