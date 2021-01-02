<?php

namespace business\modules\delivery\controllers;
use business\components\FController;
use business\services\CustomerService;
use business\utils\ExceptionAssert;
use business\utils\RestfulResponse;
use business\utils\StatusCode;
use common\utils\StringUtils;
use Yii;

class CustomerController extends FController {

	public function actionSearch() {
        $keyword = Yii::$app->request->get("search_keyword");
        ExceptionAssert::assertTrue(StringUtils::isNotBlank($keyword),StatusCode::createExpWithParams(StatusCode::STATUS_PARAMS_MISS,'search_keyword'));
        $options = CustomerService::searchCustomerUserOption($keyword);
		return RestfulResponse::success($options);
	}

}