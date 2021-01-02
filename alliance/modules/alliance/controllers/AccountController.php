<?php
namespace alliance\modules\alliance\controllers;

use alliance\components\FController;
use alliance\services\AccountService;
use alliance\utils\ExceptionAssert;
use alliance\utils\RestfulResponse;
use alliance\utils\StatusCode;
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
        ExceptionAssert::assertNotBlank($phone, StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($captcha,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'captcha'));
        AccountService::reg($phone,$captcha,$name);
        return RestfulResponse::success(true);
    }

    public function actionLogout() {
        if (!Yii::$app->user->isGuest){
            Yii::$app->user->logout();
            return $this->goHome();
        }
    }
    
}