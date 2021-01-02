<?php


namespace inner\modules\inner\controllers;


use inner\components\InnerControllerInner;
use inner\services\GoodsSortService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class GoodsSortController extends InnerControllerInner
{

    public function actionList()
    {
        $companyId = Yii::$app->request->get("companyId");
        ExceptionAssert::assertNotBlank($companyId,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyId'));
        $parentId = Yii::$app->request->get("parentId", 0);
        $goodsOwner = Yii::$app->request->get("sortOwner", null);
        $sortArr = GoodsSortService::getGoodsSortOptions($companyId,$goodsOwner,$parentId);
        return RestfulResponse::success($sortArr);
    }


}