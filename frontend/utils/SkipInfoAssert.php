<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 16:52
 */

namespace frontend\utils;
use Yii;
use yii\helpers\Url;

class SkipInfoAssert
{
    public $code;
    public $mainMessage;
    public $subMessage;

    public $url1;
    public $url1_msg;

    public $url2;
    public $url2_msg;

    /**
     * SkipInfoModel constructor.
     * @param $code
     * @param $mainMessage
     * @param $subMessage
     * @param $url1
     * @param $url1_msg
     * @param $url2
     * @param $url2_msg
     */
    public function __construct($code, $mainMessage, $subMessage, $url1, $url1_msg, $url2=null, $url2_msg=null)
    {
        $this->code = $code;
        $this->mainMessage = $mainMessage;
        $this->subMessage = $subMessage;
        $this->url1 = $url1;
        $this->url1_msg = $url1_msg;
        $this->url2 = $url2;
        $this->url2_msg = $url2_msg;
    }

    public static function skipInfo($skipInfoAssert) {
        Yii::$app->session->setFlash('code', $skipInfoAssert->code);
        Yii::$app->session->setFlash('mainMessage', $skipInfoAssert->mainMessage);
        Yii::$app->session->setFlash('subMessage', $skipInfoAssert->subMessage);
        Yii::$app->session->setFlash('url1', $skipInfoAssert->url1);
        Yii::$app->session->setFlash('url1_msg', $skipInfoAssert->url1_msg);
        Yii::$app->session->setFlash('url2', $skipInfoAssert->url2);
        Yii::$app->session->setFlash('url2_msg', $skipInfoAssert->url2_msg);
        Yii::$app->response->redirect(Url::toRoute('/tips/info'), 301)->send();
        return \Yii::$app->end();
    }

    public static function assertNotEmpty($obj,$skipInfoAssert){
        if (empty($obj)){
            SkipInfoAssert::skipInfo($skipInfoAssert);
        }
    }

    public static function assertTrue($bool,$skipInfoAssert){
        if ($bool==false){
            SkipInfoAssert::skipInfo($skipInfoAssert);
        }
    }
}