<?php

namespace common\components\baidu;

/**
 *  百度地图详细地址和经纬度转换
 *
 */
class BaiduMapWebApi {

	const DEFAULT_CITY = "杭州市";

	/**
	 *  路线规划距离查询接口
	 *  @param $origin string 起始点查询（可以是地址（百度大厦）或者经纬度（40.056878,116.30815），或名称加经纬度（百度大厦|40.056878,116.30815））
	 *  @param $destination string 终点 方式与起始点相同
	 *  @param $mode string 导航模式（driving（驾车）、walking（步行）、transit（公交）、riding（骑行）新增）
	 *  @param $origin_region string 起点城市
	 *  @param $destination_region string 终点城市
	 *  @param $tactics 导航策略（10，不走高速；11、最少时间；12、最短路径。）
	 */
	public static function direction($origin, $destination, $mode = "driving", $origin_region = "杭州", $destination_region = "杭州", $tactics = 11) {

		if (empty($origin) || empty($destination)) {
			return ["status" => 0, 'errcode' => 1001, 'errmsg' => '数据提交不完整'];
		} else {
			$url = "http://api.map.baidu.com/direction/v1";
			$url .= '?ak=' . BaiDuConfig::MAP_SERVER_AK . '&origin=' . $origin . '&destination=' . $destination . '&mode=' . $mode . '&output=' . BaiDuConfig::OUTPUT;
			if ($mode == "transit" || $mode == "walking") {
				$region = $origin_region;
				$url .= '&region=' . urlencode($region);
			} else {
				$url .= '&origin_region=' . urlencode($origin_region) . '&destination_region=' . urlencode($destination_region);
			}
			$url .= "&tactics=" . $tactics;
			$result_json = BaiDuUtils::httpGet($url);
			if (!empty($result_json)) {
				$result_array = json_decode($result_json, true);

				if ($result_array['status'] != 0) {
					return ["status" => 0, 'errcode' => 2004, 'errmsg' => $result_array['message']];
				} else {
					return ["status" => 1, 'errcode' => 0, 'data' => $result_array['result']];
				}
			}
			return ["status" => 0, 'errcode' => 2003, 'errmsg' => '路线规划信息获取失败！'];
		}
	}
}
