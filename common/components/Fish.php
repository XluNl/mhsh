<?php

namespace common\components;

/**
 * 功能类，包含多个功能函数
 * @author gjh <554647162@qq.com>
 * @copyright (c) 2015, FZM
 * @package components
 * @version 1.0
 */

class Fish {

	/**
	 * 判断字符串是不是手机号
	 * @param string $str 要判断的字符串
	 * @return boolean 标识
	 */
	public static function is_mobile($str) {
		if (empty($str)) {
			return false;
		}
		$pattern = "/^\d{5,15}$/";
		if (preg_match($pattern, $str)) {
			return true;
		}
		return false;
	}

	/**
	 * 判断字符串是不是邮箱
	 * @param string $str 要判断字符串
	 * @return boolean 返回的标识符
	 */
	public static function is_email($str) {
		if (empty($str)) {
			return false;
		}
		$pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
		if (preg_match($pattern, $str)) {
			return true;
		}
		return false;
	}

	/**
	 * 生成随机字符串
	 * @param string $type number代表纯数字，letter代表纯字母，mix代表数字和字母混合
	 * @param integer $length 要生成的字符串长度
	 * @return string
	 */
	public static function random($type = "number", $length = 8) {
		$charArr = array(
			'number' => '0123456789',
			'upper' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'lower' => 'abcdefghijklmnopqrstuvwxyz',
		);
		$charStr = "";
		if ($type == "mix") {
			foreach ($charArr as $chars) {
				$charStr .= $chars;
			}
		} else {
			$charStr = $charArr["{$type}"];
		}
		$max = strlen($charStr);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
			$string .= $charStr[mt_rand(0, $max - 1)];
		}
		return $string;
	}

	/* 从目标链接中GET数据页面 */

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

	/* POST数据到目标服务器当中 */

	public static function httpPost($url, $data) {
		$ch = curl_init(); //初始化curl
		curl_setopt($ch, CURLOPT_URL, $url); //设置链接
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //设置是否返回信息
		curl_setopt($ch, CURLOPT_POST, 1); //设置为POST方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); //POST数据
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}
