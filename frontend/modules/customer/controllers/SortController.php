<?php

namespace frontend\modules\customer\controllers;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\services\GoodsSortService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;
use yii\web\Controller;

class SortController extends Controller {

    public function actionCustomer() {
        $company_id = FrontendCommon::requiredFCompanyId();
        $parent_id = Yii::$app->request->get("parent_id",'0');
        $sort_owner = Yii::$app->request->get("sort_owner");
        ExceptionAssert::assertTrue($parent_id>=0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"parent_id"));
        ExceptionAssert::assertTrue(!StringUtils::isBlank($sort_owner),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"sort_owner"));
        $data = GoodsSortService::getSortByParentId($parent_id,$company_id,$sort_owner);
        return RestfulResponse::success($data);
    }

	public function actionList() {
	    $company_id = FrontendCommon::requiredFCompanyId();
		$parent_id = Yii::$app->request->get("parent_id",'0');
        $sort_owner = Yii::$app->request->get("sort_owner");
        ExceptionAssert::assertTrue($parent_id>=0,StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"parent_id"));
        ExceptionAssert::assertTrue(!StringUtils::isBlank($sort_owner),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,"sort_owner"));
        $data = GoodsSortService::getSortByParentId($parent_id,$company_id,$sort_owner);
        return RestfulResponse::success($data);
	}

    public function actionMark() {
        $sortId = Yii::$app->request->get("id",'-1');
        return RestfulResponse::success($sortId);
    }

    public function actionUnmark() {
        $sortId = Yii::$app->request->get("id",'-1');
        return RestfulResponse::success($sortId);
    }

}