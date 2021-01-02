<?php
namespace frontend\modules\customer\controllers;

use common\components\keruyun\KryService;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\AccountService;
use frontend\services\CouponBatchService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use yii\web\Response;
use Yii;


/**
 * 
 */
class KryController extends FController
{
	
	public function actionCouponQuery()
	{	
		Yii::$app->response->format = Response::FORMAT_JSON;
		$res['code'] ='0';
		$res['message'] = '测试OK';
		$res['messageUuid'] = '11111ggggggg';
		$res['result'] = [
			[
				'brandId'=>1,
				'shopId' => '888',
				'couponInfo'=>[
					"couponNo"=>"123456789012345601",
	            	"couponName"=>"第三方满减券",
	            	"couponType"=>1,
	            	"status"=>1,
	            	"statusDesc"=>"可用",
	            	"threshold"=>1.0,
	            	"faceValue"=>1.0,
	            	"deductPerThreshold"=>1,
	            	"startTime"=>"2019-08-01",
	                "endTime"=>"2019-08-01"
				]
			]
			
		];
		return $res;
	}

	public function actionCreateUser()
	{	
		$res = Yii::$app->get('keRuYun')->createCustomer();
		var_dump($res);die;
	}

	public function actionUserLogin()
	{	
		$res = Yii::$app->get('keRuYun')->customerLogin('15872712875');
		var_dump($res);die;
	}
	public function actionConponList()
	{	
		$res = Yii::$app->get('keRuYun')->queryConponList();
		var_dump($res);die;
	}
	public function actionConponTemps()
	{	
		$res = Yii::$app->get('keRuYun')->queryConponTemps();
		var_dump($res);die;
	}
	public function actionDrawCoupon()
	{	
		$res = Yii::$app->get('keRuYun')->drawCoupon();
		var_dump($res);die;
	}
}