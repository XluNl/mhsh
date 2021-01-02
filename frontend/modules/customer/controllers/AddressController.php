<?php

namespace frontend\modules\customer\controllers;

use common\models\Common;
use common\models\CustomerAddress;
use common\utils\StringUtils;
use frontend\components\FController;
use frontend\models\FrontendCommon;
use frontend\services\AddressService;
use frontend\utils\ExceptionAssert;
use frontend\utils\RestfulResponse;
use frontend\utils\StatusCode;
use Yii;

class AddressController extends FController {

    public $enableCsrfValidation=false;
	public function actionList() {
	    $customerId = FrontendCommon::requiredActiveCustomerId();
        $addressList = AddressService::getAddressList($customerId);
        return RestfulResponse::success($addressList);
	}

	public function actionModify(){
        $customerId = FrontendCommon::requiredActiveCustomerId();
        $modelId = FrontendCommon::getModelValueFromFormData('CustomerAddress');
        if (!StringUtils::isBlank($modelId)){
            $model = CustomerAddress::find()->where(['id'=>$modelId,'customer_id'=>$customerId])->one();
            ExceptionAssert::assertNotEmpty($model,StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,"地址不存在"));
        }
        else{
            $model = new CustomerAddress();
        }
        $load = $model->load(Yii::$app->request->post());
        ExceptionAssert::assertTrue($load,StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,"数据格式错误"));
        $model->customer_id = $customerId;
        ExceptionAssert::assertTrue($model->save(),StatusCode::createExpWithParams(StatusCode::ADDRESS_OPERATION_ERROR,"地址保存失败".Common::getModelErrors($model)));
        return RestfulResponse::success($model->id);
    }

    public function actionGet(){
        $customerId = FrontendCommon::requiredCustomerId();
        $addressId = Yii::$app->request->get('address_id');
        $addressArr = AddressService::getAddressById($addressId,$customerId);
        return RestfulResponse::success($addressArr);
    }

    public function actionSetDefault(){
        $customerId = FrontendCommon::requiredActiveCustomerId();
        $addressId = Yii::$app->request->get('address_id');
        AddressService::setDefaultAddress($addressId,$customerId);
        return RestfulResponse::success(true);
    }


    public function actionGetDefault(){
        $customerId = FrontendCommon::requiredCustomerId();
        $address = AddressService::getDefaultAddressList($customerId);
        return RestfulResponse::success($address);
    }


    public function actionRemove(){
        $customerId = FrontendCommon::requiredActiveCustomerId();
        $addressId = Yii::$app->request->get('address_id');
        AddressService::remove($addressId,$customerId);
        return RestfulResponse::success(true);
    }


}
