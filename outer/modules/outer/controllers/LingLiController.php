<?php

namespace outer\modules\outer\controllers;

use common\models\SystemOptions;
use common\utils\EncryptUtils;
use outer\components\FController;
use outer\services\CustomerService;
use outer\services\SystemOptionsService;
use outer\utils\ExceptionAssert;
use outer\utils\RestfulResponse;
use outer\utils\StatusCode;
use Yii;

/**
 *
 */
class LingLiController extends FController {


    public function actionEncryptPhone()
    {
        $phone = Yii::$app->request->get('phone');
        ExceptionAssert::assertNotBlank($phone,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'phone'));
        return RestfulResponse::success(EncryptUtils::encode($phone));
    }

    public function actionControl(){
        $phone = Yii::$app->request->get('phone');
        $customer = CustomerService::searchCustomerByPhone($phone);
        $tag = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_LINGlLI_CONTROL);
        $res =  [
            'isRegister'=>!empty($customer),
            'control'=>$tag
        ];
        return RestfulResponse::success($res);
    }
}