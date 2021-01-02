<?php

namespace common\components\baidu;
/**
 *  百度实用工具类
 */
class BaiduUtils {

	/**
	 * 从目标链接中GET数据页面
	 * @param $url string 要获取的数据页面链接
	 */

	public static function httpGet($url) {
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url); //设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$response = curl_exec($ch); //接收返回信息
		if (curl_errno($ch)) {
			print curl_error($ch);
		}
		curl_close($ch); //关闭curl链接
		return $response; //显示返回信息
	}

	/**
	 * POST数据到目标服务器当中
	 * @param $url string 要获取的数据页面链接
	 * @param $data json 页面返回的数据
	 */

	public static function httpPost($url, $data) {
		$header[] = "Content-Type: text/xml; charset=utf-8";
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url); //设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置是否返回信息
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置HTTP头
		curl_setopt($ch, CURLOPT_POST, 1); //设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //POST数据
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}
