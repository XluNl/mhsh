<?php


namespace inner\modules\inner\controllers;


use inner\services\GoodsDisplayDomainService;
use inner\components\InnerControllerInner;
use inner\services\GoodsService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class GoodsController extends InnerControllerInner
{
    public function actionList()
    {
        $companyIds = Yii::$app->request->get("companyIds");
        ExceptionAssert::assertNotBlank($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $companyIds = explode(",", $companyIds);
        ExceptionAssert::assertNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $goodsOwner = Yii::$app->request->get("goodsOwner", null);
        $bigSort = Yii::$app->request->get("bigSort",null);
        $smallSort = Yii::$app->request->get("smallSort",null);
        $keyword = Yii::$app->request->get("keyword", null);
        $activeDataProvider = GoodsService::getPageFilter($companyIds,$goodsOwner,$bigSort,$smallSort,$keyword,$pageNo,$pageSize);
        GoodsDisplayDomainService::assembleGoodsList($activeDataProvider);
        return RestfulResponse::successModelDataProvider($activeDataProvider);
    }
}