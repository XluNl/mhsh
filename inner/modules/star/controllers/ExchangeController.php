<?php


namespace inner\modules\star\controllers;

use common\utils\DateTimeUtils;
use inner\components\StarControllerInner;
use inner\services\ExchangeService;
use inner\utils\ExceptionAssert;
use inner\utils\StatusCode;
use Yii;
use yii\helpers\Json;

class ExchangeController extends StarControllerInner
{

    public $enableCsrfValidation = false;

    public function actionExchange()
    {

        $phone = Yii::$app->request->post("phone");
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        $changeAmount = Yii::$app->request->post("changeAmount");
        ExceptionAssert::assertNotBlank($changeAmount,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'changeAmount'));
        ExceptionAssert::assertTrue($changeAmount>=0.01,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'changeAmount最小为0.01元'));
        $tradeNo = Yii::$app->request->post("transactionNumber");
        ExceptionAssert::assertNotBlank($tradeNo,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'transactionNumber'));
        $exchangeTime = Yii::$app->request->post("addtime");
        ExceptionAssert::assertNotBlank($exchangeTime,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'addtime'));
        ExceptionAssert::assertTrue(DateTimeUtils::checkFormatYmdHms($exchangeTime),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_ERROR,'addtime格式错误'));
        $res = [];
        try {
            ExchangeService::exchangeBalance($tradeNo,$phone,$changeAmount,$exchangeTime);
            $res['status'] = 1;
            $res['msg'] = '成功';
            return Json::encode($res);
        }
        catch (\Exception $e){
            $res['status'] = 0;
            $res['msg'] = $e->getMessage();
            Yii::error('ExchangeController.exchange:'.$e->getMessage());
            return Json::encode($res);
        }
    }
}