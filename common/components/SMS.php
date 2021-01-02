<?php

namespace common\components;
use yii\base\Component;
use yii\helpers\Json;

class SMS extends Component {

	public $tpl_list = array();

	public $apikey = "";
	public $company = "";
	public function init() {
		parent::init();
	}

	public function send($data, $kind = "tpl") {
        $data['tpl_value'] .= '&' . urlencode('#app#') . '=' . urlencode($this->company);
		header("Content-Type:text/html;charset=utf-8");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data["apikey"] = $this->apikey;
		$sort = $data["sort"];
		if (empty($this->tpl_list["{$sort}"])) {
			return array('code' => 101, 'msg' => '没有找到相应的模板');
		}
		$data["tpl_id"] = $this->tpl_list["{$sort}"]["tpl_id"];
		//$data["tpl_value"] .= '&' . urlencode('#company#') . '=' . urlencode($this->company);
		$json_data = $this->tpl_send($ch, $data);
		$array = Json::decode($json_data);
		curl_close($ch);
		return $array;
	}

	public function commonSend($data){
		header("Content-Type:text/html;charset=utf-8");
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$data["apikey"] = $this->apikey;
		$json_data = $this->common_send($ch, $data);
		$array = Json::decode($json_data);
		curl_close($ch);
		return $array;
	}

	private function tpl_send($ch, $data) {
		curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

	private function voice_send($ch, $data) {
		curl_setopt($ch, CURLOPT_URL, 'http://voice.yunpian.com/v2/voice/send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

	private function common_send($ch, $data) {
		curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/batch_send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}
}
