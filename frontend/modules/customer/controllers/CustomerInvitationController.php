<?php

namespace frontend\modules\customer\controllers;
use frontend\models\FrontendCommon;
use frontend\services\CustomerInvitationLevelService;
use frontend\services\CustomerInvitationService;
use frontend\utils\RestfulResponse;
use yii\web\Controller;

class CustomerInvitationController extends Controller {


    public function actionDetail() {
        $customerId = FrontendCommon::requiredActiveCustomerId();
        $data = CustomerInvitationService::getInvitationSumAndDetail($customerId);
        $data['user_info'] = CustomerInvitationLevelService::getModelWithUserInfo($customerId);
        return RestfulResponse::success($data);
    }


}