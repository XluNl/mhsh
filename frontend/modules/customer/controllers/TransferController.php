<?php


namespace frontend\modules\customer\controllers;

use common\models\Captcha;
use frontend\components\FController;
use frontend\services\CaptchaService;
use frontend\services\TransferService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class TransferController extends FController
{


    public function actionAccount() {
        $phone = Yii::$app->request->get('phone');
        $name = Yii::$app->request->get('name');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        $captcha = Yii::$app->request->get('captcha');
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($captcha,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'captcha'));
        TransferService::checkUserNotExist();
        //CaptchaService::checkCaptcha($phone,$captcha,Captcha::SORT_SMS_CUSTOMER);
        TransferService::transferAccount($phone,$name,$headImgUrl);
        return RestfulResponse::success(true);
    }


    public function actionAutoAccount() {
        $phone = Yii::$app->request->get('phone');
        $name = Yii::$app->request->get('name');
        $session = Yii::$app->request->get('session');
        $iv = Yii::$app->request->get('iv');
        $encryptedData = Yii::$app->request->get('encrypted_data');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($session,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'session'));
        ExceptionAssert::assertNotBlank($iv,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'iv'));
        ExceptionAssert::assertNotBlank($encryptedData,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'encrypted_data'));
        TransferService::checkUserNotExist();
        TransferService::transferAccountAuto($session, $iv, $encryptedData,$phone,$name,$headImgUrl);
        return RestfulResponse::success(true);
    }

    public function actionEncryptedAccount() {
        $encryptedPhone = Yii::$app->request->get('encryptedPhone');
        $name = Yii::$app->request->get('name');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        ExceptionAssert::assertNotBlank($encryptedPhone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'encryptedPhone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        TransferService::checkUserNotExist();
        TransferService::transferEncryptedAccount($encryptedPhone,$name,$headImgUrl);
        return RestfulResponse::success(true);
    }
}