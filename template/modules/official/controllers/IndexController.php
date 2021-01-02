<?php

namespace template\modules\template\controllers;

use template\components\FController;
use template\services\AccountService;
use template\utils\ExceptionAssert;
use template\utils\RestfulResponse;
use template\utils\StatusCode;
use Yii;

class IndexController extends FController {

    public function actionIndex(){
        $code = Yii::$app->request->get('code');
        $headImgUrl = Yii::$app->request->get('head_img_url');
        $sex = Yii::$app->request->get('sex');
        $nickname = Yii::$app->request->get('nickname');
        ExceptionAssert::assertNotBlank($code,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'code'));
        $data = AccountService::login($code,$nickname,$headImgUrl,$sex);
        return RestfulResponse::success($data);
    }


}