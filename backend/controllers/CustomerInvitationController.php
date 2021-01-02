<?php
namespace backend\controllers;
use backend\models\BackendCommon;
use backend\models\searches\CustomerInvitationSearch;
use backend\services\CustomerInvitationService;
use backend\services\CustomerService;
use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use Yii;

class CustomerInvitationController extends BaseController {

    public function actionIndex(){
        $userInfoId = Yii::$app->request->get('user_info_id');
        $customerModel = CustomerService::getActiveModelByUserInfoId($userInfoId);
        BExceptionAssert::assertNotNull($customerModel,RedirectParams::create("客户信息未找到",Yii::$app->request->referrer));
        $parentCustomer = CustomerInvitationService::getParentCustomerInfo($customerModel['id']);
        $searchModel = new CustomerInvitationSearch();
        BackendCommon::addValueIdToParams('parent_id',$customerModel['id'],'CustomerInvitationSearch');
        BackendCommon::addCompanyIdToParams('CustomerInvitationSearch');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider = CustomerInvitationService::completeCustomerInvitationData($dataProvider,$searchModel);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'customerModel'=>$customerModel,
            'parentCustomer'=>$parentCustomer
        ]);
    }


}
