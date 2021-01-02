<?php

/**
 *  信息发送接口
 *
 */
namespace api\controllers;
use common\components\Common;
use Yii;
use yii\helpers\Json;
use yii\rest\Controller;

class MsgController extends Controller {

	public function actionTest(){
		$result = [
			"status" => 200, 
			"code"=> 1001,
			"name" => "Not Found Exception", 
			"message"=>"The requested resource was not found.",
			'data' => "OK",
			"type"=>"yii\\web\\NotFoundHttpException",
			'data'=>'',
		];
		return $result;
	}
	public function actionText(){
		$openid = Yii::$app->request->post('openid');
		if (empty($openid)) {
			$result = ['status' => 0, 'error' => 1007, 'data' => '用户OPENID不能为空'];
		} else {
			$content = Yii::$app->request->post('content');
			if(empty($content)){
				$result = ['status' => 0, 'error' => 1007, 'data' => 'CONTENT(发送的内容)不能为空'];
			}else{
				$weixin_id = 2;
				$access_token = Yii::$app->redis->get("access_token_" . $weixin_id);
				$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
				$data = ['touser' => $openid, 'msgtype' => 'text', 'text' => ['content' => urlencode($content)]];
				$result_json = Common::httpPost($url, urldecode(Json::encode($data)));
				$result_array = Json::decode($result_json);
				if (empty($result_array['errcode'])) {
					$result = ["status" => 1, 'error' => 0, 'data' => "OK"];
				} else {
					$result = ["status" => 0, 'error' => $result_array['errcode'], 'data' => $result_array['errmsg']];
				}
			}
		}
		return Json::encode($result);
	}
}