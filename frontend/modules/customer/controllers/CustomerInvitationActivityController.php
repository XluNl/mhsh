<?php

namespace frontend\modules\customer\controllers;

use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\CustomerInvitationActivityService;
use frontend\utils\RestfulResponse;

class CustomerInvitationActivityController extends FController {

	public function actionInfo() {
	    $companyId = FrontendCommon::requiredFCompanyId();
	    $customerId = FrontendCommon::requiredCustomerId();
 		$data = CustomerInvitationActivityService::getActivity($companyId,$customerId);
		return RestfulResponse::success($data);
	}

}
