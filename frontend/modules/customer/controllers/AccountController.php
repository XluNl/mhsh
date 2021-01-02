<?php
namespace frontend\modules\customer\controllers;

use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\AccountService;
use frontend\services\CouponBatchService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class AccountController extends FController {

    public function actionLogin(){
        $code = Yii::$app->request->get('code');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        $sex = Yii::$app->request->get('sex');
        $nickname = Yii::$app->request->get('nickname');
        ExceptionAssert::assertNotBlank($code,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'code'));
        $data = AccountService::login($code,$nickname,$headImgUrl,$sex);
        return RestfulResponse::success($data);
    }

	public function actionReg() {
        $phone = Yii::$app->request->get('phone');
        $name = Yii::$app->request->get('name');
        $captcha = Yii::$app->request->get('captcha');
        $inviteCode = Yii::$app->request->get('invite_code');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($captcha,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'captcha'));
        //ExceptionAssert::assertNotBlank($headImgUrl,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'head_img_url'));
        AccountService::reg($phone,$captcha,$name,$headImgUrl,$inviteCode);

        // 注册完成尝试发放新人优惠券
        CouponBatchService::automaticDrawCoupon();
        // 添加用户到客如云
        Yii::$app->get('keRuYun')->createCustomer($phone,$name);
        return RestfulResponse::success(true);
	}

    public function actionAutoReg() {
        $phone = Yii::$app->request->get('phone');
        $name = Yii::$app->request->get('name');
        $session = Yii::$app->request->get('session');
        $iv = Yii::$app->request->get('iv');
        $encryptedData = Yii::$app->request->get('encrypted_data');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        $inviteCode = Yii::$app->request->get('invite_code');

        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($session,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'session'));
        ExceptionAssert::assertNotBlank($iv,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'iv'));
        ExceptionAssert::assertNotBlank($encryptedData,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'encrypted_data'));
        AccountService::autoReg($session, $iv, $encryptedData,$phone,$name,$headImgUrl,$inviteCode);

        // 注册完成尝试发放新人优惠券
        CouponBatchService::automaticDrawCoupon();
        // 添加用户到客如云
        Yii::$app->get('keRuYun')->createCustomer($phone,$name);
        return RestfulResponse::success(true);
    }

    public function actionDecryptPhone() {
        $session = Yii::$app->request->get('session');
        $iv = Yii::$app->request->get('iv');
        $encryptedData = Yii::$app->request->get('encrypted_data');
        ExceptionAssert::assertNotBlank($session,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'session'));
        ExceptionAssert::assertNotBlank($iv,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'iv'));
        ExceptionAssert::assertNotBlank($encryptedData,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'encrypted_data'));
        $phone = AccountService::decryptPhone($session, $iv, $encryptedData);
        return RestfulResponse::success($phone);
    }

    public function actionBind(){
        $inviteCode = Yii::$app->request->get('invite_code');
        ExceptionAssert::assertNotBlank($inviteCode,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'invite_code'));
        $customerId = FrontendCommon::requiredActiveCustomerId();
        AccountService::bindInvitation($customerId,$inviteCode);
        return RestfulResponse::success(true);
    }


    public function actionLogout() {
        if (!Yii::$app->user->isGuest){
            Yii::$app->user->logout();
            return $this->goHome();
        }
    }
    
}