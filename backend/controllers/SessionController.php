<?php


namespace backend\controllers;

use backend\components\InnerControllerInner;
use backend\services\AdminUserService;
use backend\services\SessionService;
use backend\utils\BExceptionAssert;
use backend\utils\BRestfulResponse;
use backend\utils\BStatusCode;
use Yii;

class SessionController extends InnerControllerInner
{

    public function actionSession() {
        $sessionId = Yii::$app->request->get("sessionId");
        BExceptionAssert::assertNotBlank($sessionId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'sessionId'));
        $data = Yii::$app->session->readSession($sessionId);
        $data = SessionService::unSerialize($data);
        return BRestfulResponse::success($data);
    }

    public function actionSessionUserInfo() {
        $sessionId = Yii::$app->request->get("sessionId");
        BExceptionAssert::assertNotBlank($sessionId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'sessionId'));
        $data = Yii::$app->session->readSession($sessionId);
        $data = SessionService::unSerialize($data);
        BExceptionAssert::assertNotBlank($sessionId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'sessionId'));
        $model = AdminUserService::requireActiveModel($data['__id']);
        $model = AdminUserService::mask($model);
        $data['userInfo'] = $model;
        return BRestfulResponse::success($data);
    }

    public function actionUserInfo() {
        $userId = Yii::$app->request->get("userId");
        BExceptionAssert::assertNotBlank($userId,BStatusCode::createExpWithParams(BStatusCode::STATUS_PARAMS_ERROR,'userId'));
        $model = AdminUserService::requireActiveModel($userId);
        $model = AdminUserService::mask($model);
        return BRestfulResponse::success($model);
    }

}