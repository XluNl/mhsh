<?php


namespace business\modules\delivery\controllers;


use business\components\FController;
use business\services\CaptchaService;
use business\services\TransferService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\models\Captcha;
use Yii;

class TransferController extends FController
{

    public function actionAccount() {
        $phone = Yii::$app->request->get('phone');
        $companyId = Yii::$app->request->get('companyId',1);
        $name = Yii::$app->request->get('name');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        $captcha = Yii::$app->request->get('captcha');
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        ExceptionAssert::assertNotBlank($name,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'name'));
        ExceptionAssert::assertNotBlank($companyId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyId'));
        ExceptionAssert::assertNotBlank($captcha,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'captcha'));
        TransferService::checkUserNotExist();
        CaptchaService::checkCaptcha($phone,$captcha,Captcha::SORT_SMS_BUSINESS);
        TransferService::transferAccount($phone,$name,$headImgUrl,$companyId);
        return RestfulResponse::success(true);
    }


    public function actionAutoAccount() {
        $phone = Yii::$app->request->get('phone');
        $companyId = Yii::$app->request->get('companyId',1);
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
        ExceptionAssert::assertNotBlank($companyId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyId'));
        TransferService::checkUserNotExist();
        TransferService::transferAccountAuto($session, $iv, $encryptedData,$phone,$name,$headImgUrl,$companyId);
        return RestfulResponse::success(true);
    }
}