<?php
namespace common\components\keruyun;


use \Yii;
use yii\base\Component;
use yii\httpclient\Client;
/**
 * 
 */
class KryService  extends Component
{	

	const TEST_SHOP_ID = '811048816';
	// const TEST_SHOP_ID = '811048816;810094162';
	// public $APPkey;
	// public $APPSecret;
	public static  function signXlu()
	{	
		return ;
	}

	/**
	 * [customerLogin 会员登录]
	 * @param  [type] $loginId   [手机号码\微信openId\座机号码\顾客ID]
	 * @param  [type] $loginType [登录类型(0:手机注册客户;1:微信注册用户;2:座机号;101:微信会员卡卡号;)]
	 * @return [type]            [dev:810094162  prod:811048816]
	 */
	public static function customerLogin($loginId,$loginType=1,\Closure $callback = null){
		$parmas = ['loginId'=>$loginId,'loginType'=>$loginType];
		KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/login',$parmas,function($info){
			if($callback){
				call_user_func($callback,$info,0);
			}
			var_dump($res);die;
		});
	}

	/**
	 * [createCustomer 创建会员]
	 * @param  [type] $birthday  [生日]
	 * @param  [type] $loginId   [手机号码\微信openId]
	 * @param  [type] $loginType [登陆类型(0:手机注册客户;1:微信注册用户;2:座机号;101:微信会员卡卡号;)]
	 * @param  [type] $name      [顾客名]
	 * @param  [type] $sex       [性别(-1:未知;0:女;1:男;)]
	 *  xlu 添加成功会员id：441272723013293056
	 * @return [type]            [description]
	 */
	public static function createCustomer($loginId='15872712875',$name='mhsh','$birthday='797184000',$loginType='1',$sex='28'){
		$parmas = [
			'birthday'=>$birthday,
			'loginId'=>$loginId,
			'loginType'=>$loginType,
			'name'=>$name,
			'sex'=>$sex
		];
		$res = KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/createCustomer',$parmas);
		return $res;
	}

	/**
	 * [queryConponList 查询优惠券列表]
	 * @param  [type] $couponStatus [优惠券状态(1:未使用;2:已验证;3:已过期;4:作废;)]
	 * @param  [type] $couponTypes  [优惠券类型(2:折扣券;3:礼品券;4:现金券;)]
	 * @param  [type] $customerId   [顾客ID]
	 * @param  [type] $page         [第几页]
	 * @param  [type] $pageSize     [每页查询数据条数]
	 *
	 * @return [type]               [description]
	 */
	public static function queryConponList($couponStatus=[1],$couponTypes=[2],$customerId='441272723013293056',$page=1,$pageSize=20){
		$parmas= [
			'couponStatus'=>$couponStatus,
			'couponTypes'=>$couponTypes,
			'customerId'=>$customerId,
			'page'=>$page,
			'pageSize'=>$pageSize
		];
		$res = KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/fetchCoupInstanceList',$parmas);
		return $res;
	}

	/**
	 * [queryConponDetail 查询优惠券详情]
	 * @param  [type] $id [优惠券id]
	 * @return [type] [description]
	 */
	public static function queryConponDetail($id){
		$parmas= [
			'id'=>$id,
		];
		$res = KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/fetchCoupInstanceDetail',$parmas);
		return $res;
	}
	/**
	 * [getCoupon 领取优惠券]
	 * @param  [type] $brandId [客如云品牌Id]
	 * @param  [type] $shopId  [客如云门店Id]
	 * @param  [type] $customerId [顾客Id]
	 * @param  [type] $coupons [发券信息]
	 * 领取优惠券ID：395903317324766208
	 * @return [type]          [description]
	 */
	public static function drawCoupon($brandId='567201',$shopId=self::TEST_SHOP_ID,$customerId='441272723013293056',$coupons=null){
		$coupons = [
			['couponId'=>'395903317324766208','count'=>1]
		];
		$parmas= [
			'brandId'=>$brandId,
			'shopId'=>$shopId,
			'customerId'=>$customerId,
			'coupons'=>$coupons
		];
		self::customerLogin($customerId,1,function($res) use ($parmas){
			$res = KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/coupon/manualSend',$parmas);
			return $res;
		});
		
	}

	/**
	 * [queryConponTemp 查询券模板list]
	 * @return [type] [description]
	 */
	public static function queryConponTemps($brandId='567201'){
		$parmas= [
			'brandId'=>$brandId,
			'couponTypeList'=>[2,3,4],
			'pageSize'=>20,
			'currentPage'=>1
		];
		$res = KryUtils::getInstance(['shopIdenty'=>self::TEST_SHOP_ID])->httpPost('/open/v1/crm/coupon/page',$parmas);
		return $res;
	}


}