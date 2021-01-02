<?php

/**
 *  信息发送接口
 *
 */
namespace api\controllers;
use common\components\Common;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

class MessageController extends Controller {

	/**
	 * 下单完成后的确认接口主动发送到用户关注的微信公众号
	 *  采用的是微信的客服接口
	 */

	public function actionOrder() {
		$order_no = Yii::$app->request->get('order_no');
		if (empty($order_no)) {
			$result = ['status' => 0, 'error' => 1006, 'data' => '订单号不能为空'];
		} else {
			$openid = Yii::$app->request->get('openid');
			if (empty($openid)) {
				$result = ['status' => 0, 'error' => 1007, 'data' => '用户OPENID不能为空'];
			} else {
				$sql = "select * from sptx_order where order_no = '" . $order_no . "'";
				$main_db = Yii::$app->maindb;
				$row = $main_db->createCommand($sql)->queryOne();
				if (!empty($row)) {
					$weixin_id = 2;
					$order_status = $row['order_status'];
					$title = (empty($order_status)) ? "确认订单" : "确认订单";
					$action = (empty($order_status)) ? "restaurant/order/pay" : "restaurant/order/detail";
					$url = "http://shiputx.shiputx.cn/{$action}?order_no=" . $order_no;
					$info = (empty($order_status)) ? "请点击微信支付" : "点击查看详情";
					$message = (empty($order_status)) ? "您的订单已确认下单成功，如您需在线支付~" : "您的订单已确认下单成功，臣妾已经为您备货";
					$message_info_url = sprintf("%s<a href='%s'>%s</a>", $message, $url, $info);
					$content = $title . PHP_EOL . '订单号:' . $row['order_no'] . PHP_EOL;
					$content .= '下单时间:' . date("Y-m-d H:i:s", $row['create_time']) . PHP_EOL;
					$content .= '订单金额:' . Common::showAmount($row['real_amount']) . '元' . PHP_EOL;
					$content .= '付款方式:' . $row['pay_name'] . PHP_EOL;
					if ($row['pay_id']==1003){
					    if ($row['pay_amount']<$row['real_amount']){
					        $content .= '付款金额:余额支付(' . Common::showAmount($row['paid_amount']) . '元),另需货到付款('.$row['real_amount']-Common::showAmount($row['paid_amount']) .'元)' . PHP_EOL;
					    }
					    else {
					        $content .= '付款金额:余额支付(' . Common::showAmount($row['paid_amount']) . '元)' . PHP_EOL;
					    }
					}
					$content .= '支付宝账号:郑平贵18358120647'. PHP_EOL;
					$content .= '商户名称:' . $row['accept_restaurant'] . PHP_EOL;
					$content .= '收货时间:' . date("Y-m-d", time() + 24 * 3600) . " 7:00-9:00" . PHP_EOL;
					$zone = $row['accept_zone'];
					$content .= '收货地址:浙江省杭州市' . Common::$zone_list["{$zone}"] . '' . $row['accept_address'] . PHP_EOL;
					$content .= '如有问题请拨打:18358120647' . PHP_EOL;
					$content .= $message_info_url;
					//$access_token = Yii::$app->redis->get("access_token_" . $weixin_id);
					$access_token = Yii::$app->wechat->access_token->getToken();
					$url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
					$data = ['touser' => $openid, 'msgtype' => 'text', 'text' => ['content' => urlencode($content)]];
					$result_json = Common::httpPost($url, urldecode(Json::encode($data)));
					$result_array = Json::decode($result_json);
					if (empty($result_array['errcode'])) {
						$result = ["status" => 1, 'error' => 0, 'data' => "OK"];
					} else {
						$result = ["status" => 0, 'error' => $result_array['errcode'], 'data' => $result_array['errmsg']];
					}
				} else {
					$result = ['status' => 0, 'error' => 1008, 'data' => '订单号错误，没有找到该订单'];
				}

			}
		}
		return Json::encode($result);
	}
	public function actionModifyorder() {
		$order_no = Yii::$app->request->get('order_no');
		if (empty($order_no)) {
			$result = ['status' => 0, 'error' => 1006, 'data' => '订单号不能为空'];
		} else {
			$openid = Yii::$app->request->get('openid');
			if (empty($openid)) {
				$result = ['status' => 0, 'error' => 1007, 'data' => '用户OPENID不能为空'];
			} else {
				$amount = Yii::$app->request->get('amount', 0);
				if (empty($amount)) {
					$result = ['status' => 0, 'error' => 1008, 'data' => '订单实际金额不能为空'];
				} else {
					$weixin_id = 2;
					$content = '识汇生鲜:' . PHP_EOL;
					$content .= "尊敬的用户您好,根据实际的分拣情况，订单号'" . $order_no . "'的订单有所变更，变更后的金额为'" . $amount . "'元。请您在确认收货后及时把货款打入支付宝：18358120647(郑平贵)" . PHP_EOL;
					$content .= '如有问题请拨打:18358120647' . PHP_EOL;
					$url = "http://shiputx.shiputx.cn/restaurant/order/detail?order_no=" . $order_no;
					$content .= sprintf("%s<a href='%s'>%s</a>", "具体变更信息", $url, "点击查看详情");
					//$access_token = Yii::$app->redis->get("access_token_" . $weixin_id);
					$access_token = Yii::$app->wechat->access_token->getToken();
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
		}
		return Json::encode($result);
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
				//$access_token = Yii::$app->redis->get("access_token_" . $weixin_id);
				$access_token = Yii::$app->wechat->access_token->getToken();
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