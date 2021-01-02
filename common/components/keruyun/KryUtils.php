<?php

namespace common\components\keruyun;

use yii\base\Component;
use yii\httpclient\Client;
use yii\helpers\Json;
/**
 * 
 */
class KryUtils extends Component
{	
	//测试环境: https://gldopenapi.keruyun.com
	//正式环境: https://openapi.keruyun.com
	
	public $url = 'https://openapi.keruyun.com';
	public $httpClient = null;
	public $errMsg = '';
	public $shopIdenty ='';
	public $token;
	public $baseParmes = [];
    private static $instance = null;

    public $userInfo = null;

    public static function getInstance($params= [])
    {
        if (null === self::$instance) {
            self::$instance = new self($params);
        }
        return self::$instance;
    }

	public function init()
    {
        parent::init();
        $this->httpClient = new Client([
            'baseUrl' => $this->url
        ]);
        $this->baseParmes =  [
			'appKey' => KryCongifg::APP_KEY,
			'shopIdenty' => $this->shopIdenty,
			'timestamp' => time(),
			'version' => KryCongifg::VERSION
		];
    }

    public function getToken(){
    	if($this->token){
			return $this;
		}
		$params = $this->baseParmes;
		if(empty($params['shopIdenty'])){
			throw new \Exception('shopIdenty 必填！');
		}
		ksort($params);
    	$paramesStr = "";
		foreach ($params as $k => $v) {
		 $paramesStr = $paramesStr.$k.$v;
		}
		$paramesStr.=KryCongifg::APP_SECRET;
		$params['sign'] = hash("sha256", $paramesStr);

		$url = '/open/v1/token/get'.'?'.http_build_query($params);
		$res = $this->httpClient->get($url,null)->setOptions([
                CURLOPT_SSL_VERIFYPEER=>false
            ])->send();
		
		$code = $res->data['code'];
		if($code == 0){
			$this->token = $res->data['result']['token'];
			return $this;
		}
		throw new \Exception(Json::encode($res->data));
    }

	private  function getApiSign()
	{	
		$params = $this->baseParmes;
		ksort($params);
		$paramesStr = "";
		foreach ($params as $k => $v) {
		 $paramesStr = $paramesStr.$k.$v;
		}
		$paramesStr.=$this->token;
		$params['sign'] = hash("sha256", $paramesStr);
		return $params;
	}

	private function paramsJoinToken(){
		return $this->getToken()->getApiSign();
	}

	public  function httpGet($url,$params=null,\Closure $callback = null){
		try{
			$url = $url.'?'.http_build_query($this->paramsJoinToken());
			$res = $this->httpClient->get($url,null)->setOptions([
	                CURLOPT_SSL_VERIFYPEER=>false
	            ])->send();
			if($callback){
				call_user_func($callback,$res,0);
			}
			return $res;
		}catch(\Exception $e){
			if($callback){
				call_user_func($callback,Json::decode($e->getMessage()),0);
			}
		}
		
	}

	public  function httpPost($url,$params=null,\Closure $callback = null){
		try{
			$url = $url.'?'.http_build_query(self::paramsJoinToken());
			$params = json_encode($params);
			$res = $this->httpClient->post($url,$params,['Content-Type'=>'application/json'])->setOptions([
	                CURLOPT_SSL_VERIFYPEER=>false
	            ])->send();
			// var_dump($res->data,$params);die;
			if($callback){
				call_user_func($callback,$res->data,0);
			}
			return $res->data;
		}catch(\Exception $e){
			if($callback){
				call_user_func($callback,Json::decode($e->getMessage()),0);
			}
		}
		
	}

}