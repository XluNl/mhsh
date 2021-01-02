<?php


namespace inner\modules\inner\controllers;


use inner\components\InnerControllerInner;
use inner\services\CompanyService;
use inner\utils\ExceptionAssert;
use inner\utils\RestfulResponse;
use inner\utils\StatusCode;
use Yii;

class CompanyController extends InnerControllerInner
{
    public function actionInfos()
    {
        $companyIds = Yii::$app->request->get("companyIds");
        $companyIds = ExceptionAssert::assertNotBlankAndNotEmpty($companyIds,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'companyIds'));
        $data = CompanyService::getAllModel($companyIds);
        return RestfulResponse::success($data);
    }

    public function actionList()
    {
        $pageNo = Yii::$app->request->get("pageNo", 1);
        $pageSize = Yii::$app->request->get("pageSize", 20);
        $provider = CompanyService::getList($pageNo,$pageSize);
        return RestfulResponse::successModelDataProvider($provider);
    }

}