<?php


namespace frontend\services;


use common\models\CustomerAddress;
use common\models\Region;
use common\utils\ArrayUtils;
use common\utils\EncryptUtils;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use common\utils\PhoneUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use Yii;

class TransferService
{


    public static function checkUserNotExist(){
        $userModel = FrontendCommon::requiredUserModel();
        ExceptionAssert::assertNull($userModel->user_info_id, StatusCode::createExp(StatusCode::ACCOUNT_CREATE_REPEAT));
    }

    public static function transferAccountAuto($session, $iv, $encryptedData,$phone,$name,$headImageUrl){
        $decryptPhone = AccountService::decryptPurePhoneNumber($session, $iv, $encryptedData);
        ExceptionAssert::assertTrue($decryptPhone==$phone,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'手机号不一致'));
        self::transferAccount($phone,$name,$headImageUrl);
    }

    public static function transferEncryptedAccount($encryptedPhone,$name,$headImageUrl){
        $decryptPhone = EncryptUtils::decode($encryptedPhone);
        ExceptionAssert::assertNotBlank($decryptPhone,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'解密错误'));
        ExceptionAssert::assertTrue(PhoneUtils::checkPhoneFormat($decryptPhone),StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'手机号格式错误'));
        $customer = CustomerService::searchCustomerByPhone($decryptPhone);
        ExceptionAssert::assertNull($customer,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'该手机号已被使用'));
        self::transferAccount($decryptPhone,$name,$headImageUrl);
    }




    public static function transferAccount($phone,$name,$headImageUrl){
        $userModel = FrontendCommon::requiredUserModel();
        $lingLiData = self::getLingLiUserInfo($phone);
        ExceptionAssert::assertTrue($lingLiData['distribution_type']==1,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'未找到注册信息，请重新注册'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            $customer = UserInfoService::register($phone,$name,$headImageUrl,$userModel);
            CustomerInvitationLevelService::create($customer['id']);

            self::tryAddAddress($customer, $lingLiData, $phone);

            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }

    }

    public static function getPCC($provinceName,$cityName,$countyName){
        $resProvinceId = null;
        $resCityId = null;
        $resCountyId = null;
        $provinceIds = self::getRegionId($provinceName,0,0);
        foreach ($provinceIds as $provinceId){
            $resProvinceId = $provinceId;
            $cityIds = self::getRegionId($cityName,1,$provinceId);
            foreach ($cityIds as $cityId){
                $resCityId = $cityId;
                $countyIds = self::getRegionId($countyName,2,$cityId);
                foreach ($countyIds as $countyId){
                    $resCountyId = $countyId;
                }
            }
        }
        ExceptionAssert::assertNotBlank($resProvinceId,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,"省份转化失败"));
        ExceptionAssert::assertNotBlank($resCityId,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,"城市转化失败"));
        ExceptionAssert::assertNotBlank($resCountyId,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,"县区转化失败"));
        return [$resProvinceId,$resCityId,$resCountyId];
    }

    private static function getRegionId($name,$level,$pid){
        $conditions = [];
        if (StringUtils::isNotBlank($level)){
            $conditions['level'] = $level;
        }
        if (StringUtils::isNotBlank($pid)){
            $conditions['parent_id'] = $pid;
        }
        $region = Region::find()->where(
            ['and',['like','name',$name],
                $conditions
            ]
        )->all();
        return ArrayUtils::getColumnWithoutNull( 'id',$region);
    }

    public static function getLingLiUserInfo($phone){
        $request['mobile'] = $phone;
        $url = PathUtils::join(Yii::getAlias("@lingLiUrl"),"/api/Commander/userInfo");
        try {
            $response = HttpClientUtils::post($url,$request);
            ExceptionAssert::assertNotEmpty($response,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"零里","无结果"));
            ExceptionAssert::assertTrue($response['status']==true&&!empty($response['data'])&&$response['data']['uid']!=null,StatusCode::createExpWithParams(StatusCode::REPOSITORY_CALL_ERROR,"零里",'查不到数据'));
            return $response['data'];
        }
        catch (\Exception $e){
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,"查不到原信息"));
        }
    }

    /**
     * @param $customer
     * @param $lingLiData
     * @param $phone
     */
    private static function tryAddAddress($customer, $lingLiData, $phone): void
    {
        try {
            $customerAddress = new CustomerAddress();
            $customerAddress->customer_id = $customer['id'];
            $customerAddress->name = $lingLiData['username'];
            $customerAddress->phone = $phone;
            list($resProvinceId, $resCityId, $resCountyId) = self::getPCC($lingLiData['receive_province_name'], $lingLiData['receive_city_name'], $lingLiData['receive_district_name']);
            $customerAddress->province_id = $resProvinceId;
            $customerAddress->city_id = $resCityId;
            $customerAddress->county_id = $resCountyId;
            $customerAddress->community = $lingLiData['store_name'];
            $customerAddress->address = $lingLiData['receive_address_all'];;
            $customerAddress->lat = $lingLiData['lat'];;
            $customerAddress->lng = $lingLiData['lng'];;
            $customerAddress->is_default = CustomerAddress::DEFAULT_TRUE;
            $customerAddress->save();
        }
        catch (\Exception $e){
            Yii::error("tryAddAddress error".$e->getMessage());
        }
    }

}