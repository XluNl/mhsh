<?php

namespace template\modules\template\controllers;

use template\components\FController;
use template\services\AccountService;
use template\utils\ExceptionAssert;
use template\utils\RestfulResponse;
use template\utils\StatusCode;
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
        return RestfulResponse::success(true);
	}


}