<?php
namespace api\controllers;
use common\components\baidu\BaiDuMapGencoder;
use common\components\baidu\BaiDuMapWebApi;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class BaiduController extends Controller {

	public function actionMapgencode() {
		$address = \Yii::$app->request->get("address", "");
		$city = \Yii::$app->request->get("city", "");
		$data = ['address' => $address, 'city' => $city];
		return Json::encode(BaiDuMapGencoder::encode($data));
	}

	public function actionDistance() {
		$lat = Yii::$app->request->get('lat');
		$lng = Yii::$app->request->get('lng');
		$origin = "30.401689,120.10453";
		if (empty($lat) || empty($lng)) {
			$result = ["status" => 0, 'errcode' => 1001, 'errmsg' => '数据提交不完整'];
		} else {
			$destination = "{$lat},{$lng}";
			$result = BaiDuMapWebApi::direction($origin, $destination);
			if ($result['status']) {
				$result['data'] = $result['data']['routes'][0]['distance'];
			}
		}
		return Json::encode($result);
	}
}
