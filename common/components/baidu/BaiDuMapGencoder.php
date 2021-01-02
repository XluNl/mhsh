<?php

namespace common\components\baidu;

/**
 *  百度地图详细地址和经纬度转换
 *
 */
class BaiDuMapGencoder {

	/**
	 *  详细地址与经纬度转换数据获取链接
	 *
	 */

	/**
	 *  通用参数说明
	 *	output	否	xml	json或xml	输出格式为json或者xml
	 *	ak	是	无	E4805d16520de693a3fe707cdc962045	用户申请注册的key，自v2开始参数修改为“ak”，之前版本参数为“key”
	 *	sn	否	无		若用户所用ak的校验方式为sn校验时该参数必须。 （sn生成算法）
	 *	callback	否	无	callback=showLocation(JavaScript函数名)	将json格式的返回值通过callback函数返回以实现jsonp功能
	 *
	 */
	/**
	 *  一、地址转经纬度
	 *     1.特殊参数说明
	 *     	address	是	无	北京市海淀区上地十街10号  根据指定地址进行坐标的反定向解析，最多支持100个字节输入。
	 *
	 *			可以输入三种样式的值，分别是：
	 *			1、标准的地址信息，如北京市海淀区上地十街十号
	 *			2、名胜古迹、标志性建筑物，如天安门，百度大厦
	 *			3、支持“*路与*路交叉口”描述方式，如北一环路和阜阳路的交叉路口
	 *			注意：后两种方式并不总是有返回结果，只有当地址库中存在该地址描述时才有返回。
	 *		city	否	“北京市”	“广州市”  地址所在的城市名。用于指定上述地址所在的城市，当多个城市都有上述地址时，该参数起到过滤作用。
	 *	   2.链接实例.eg:
	 *      http://api.map.baidu.com/geocoder/v2/?address=北京市海淀区上地十街10号&output=json&ak=E4805d16520de693a3fe707cdc962045&callback=showLocation
	 *	   3.返回值说明
	 *	    status Int 返回结果状态值， 成功返回0，其他值请查看下方返回码状态表。
	 *		location object 经纬度坐标
	 *		lat float 纬度值
	 *		lng float 经度值
	 *		precise Int 位置的附加信息，是否精确查找。1为精确查找，即准确打点；0为不精确，即模糊打点。
	 *		confidence Int 可信度，描述打点准确度
	 *		level string 地址类型
	 *
	 *		eg://带回调函数的返回格式
	 *		showLocation&&showLocation(
	 *		{
	 * 			status: 0,
	 * 			result: {
	 *   			location: {
	 *     				lng: 116.30814954222,
	 *     				lat: 40.056885091681
	 *   			},
	 *   			precise: 1,
	 *   			confidence: 80,
	 *   			level: "商务大厦"
	 *			}
	 *		})
	 *
	 *		//不带回调函数的返回值
	 *			{
	 *		  		status: 0,
	 *		  		result: {
	 *					location: {
	 *			      		lng: 116.30814954222,
	 *			      		lat: 40.056885091681
	 *			    	},
	 *			    	precise: 1,
	 *			    	confidence: 80,
	 *			    	level: "商务大厦"
	 *			  	}
	 *			}
	 *
	 */
	const SERVER_URL = "http://api.map.baidu.com/geocoder/v2/";

	const COORD_TYPE = "bd09ll";

	const OUTPUT = "json";

	const DEFAULT_CITY = "杭州市";

	public static function encode($data, $kind = "address") {
		$result = ["status" => 0, 'errcode' => ''];
		if (!empty($data)) {
			$ak = BaiDuConfig::MAP_SERVER_AK;
			$url = BaiDuMapGencoder::SERVER_URL . '?output=' . BaiDuMapGencoder::OUTPUT . '&ak=' . $ak;
			if ($kind == "address") {
				$city = (empty($data['city'])) ? BaiDuMapGencoder::DEFAULT_CITY : $data['city'];
				$url .= "&city=" . $city;
				if (empty($data['address'])) {
					return ["status" => 0, 'errcode' => 2001, 'errmsg' => '使用地址转换代码，转码地址不能为空，缺少address字段'];
				} else {
					$address = $data['address'];
					$url .= "&address=" . urlencode($address);
					$result_json = BaiDuUtils::httpGet($url);
					if (!empty($result_json)) {
						$result_array = json_decode($result_json, true);
						if ($result_array['status'] == 0) {
							$location = $result_array['result']['location'];
							return ["status" => 1, 'errcode' => 0, 'data' => $location];
						}
					}
					return ["status" => 0, 'errcode' => 2002, 'errmsg' => '位置信息获取失败！'];
				}
			}
		} else {
			return ["status" => 0, 'errcode' => 1001, 'errmsg' => '数据提交不完整'];
		}
	}
}