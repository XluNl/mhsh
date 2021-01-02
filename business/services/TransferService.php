<?php


namespace business\services;



use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\BusinessApply;
use common\models\CommonStatus;
use common\models\Delivery;
use common\models\Region;
use common\models\User;
use common\models\UserInfo;
use common\utils\ArrayUtils;
use common\utils\HttpClientUtils;
use common\utils\ModelUtils;
use common\utils\PathUtils;
use common\utils\StringUtils;
use Yii;

class TransferService
{

    public static function checkUserNotExist(){
        $userModel = BusinessCommon::requiredUserModel();
        ExceptionAssert::assertNull($userModel->user_info_id, StatusCode::createExp(StatusCode::ACCOUNT_CREATE_REPEAT));
    }

    public static function transferAccountAuto($session, $iv, $encryptedData,$phone,$name,$headImageUrl,$companyId){
        $decryptPhone = AccountService::decryptPurePhoneNumber($session, $iv, $encryptedData);
        ExceptionAssert::assertTrue($decryptPhone==$phone,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'手机号不一致'));
        self::transferAccount($phone,$name,$headImageUrl,$companyId);
    }


    public static function transferAccount($phone,$name,$headImageUrl,$companyId){
        $userModel = BusinessCommon::requiredUserModel();
        $lingLiData = self::getLingLiDelivery($phone);
        ExceptionAssert::assertTrue($lingLiData['distribution_type']==1,StatusCode::createExpWithParams(StatusCode::PHONE_REGISTER_ERROR,'未找到注册信息，请重新注册'));
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //注册账户
            $userInfo = UserInfo::findOne(['phone'=>$phone]);
            if (empty($userInfo)){
                $userInfo = new UserInfo();
                $userInfo->phone = $phone;
                $userInfo->nickname = $name;
                $userInfo->status = CommonStatus::STATUS_ACTIVE;
                $userInfo->head_img_url = $headImageUrl;
                $userInfo->is_delivery = CommonStatus::STATUS_ACTIVE;
                ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,BusinessCommon::getModelErrors($userInfo)));
            }
            else {
                $exUserModel = User::findOne(['user_type'=>User::USER_TYPE_BUSINESS,'user_info_id'=>$userInfo->id]);
                $userInfo->is_delivery = CommonStatus::STATUS_ACTIVE;
                if (StringUtils::isNotBlank($headImageUrl)){
                    $userInfo->head_img_url = $headImageUrl;
                }
                ExceptionAssert::assertNull($exUserModel, StatusCode::createExp(StatusCode::PHONE_USED));
                ExceptionAssert::assertTrue($userInfo->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,BusinessCommon::getModelErrors($userInfo)));
            }
            $updateUserBool = UserInfoService::updateUserId($userModel->id,User::USER_TYPE_BUSINESS,$userInfo->id);
            ExceptionAssert::assertTrue($updateUserBool, StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,'重复更新,请刷新重试'));


            //创建申请表

            $businessApply = new BusinessApply();
            $businessApply->setScenario("delivery");
            $businessApply->em_phone = $phone;
            $businessApply->user_id = $userInfo->id;
            $businessApply->type = BusinessApply::APPLY_TYPE_DELIVERY;
            $businessApply->nickname = $lingLiData['store_name'];
            $businessApply->realname = $lingLiData['username'];
            $businessApply->wx_number = "";
            $businessApply->occupation = "";
            list($resProvinceId,$resCityId,$resCountyId) = self::getPCC($lingLiData['receive_province_name'],$lingLiData['receive_city_name'],$lingLiData['receive_district_name']);
            $businessApply->province_id = $resProvinceId;
            $businessApply->city_id = $resCityId;
            $businessApply->county_id = $resCountyId;
            $businessApply->community = $lingLiData['store_name'];
            $businessApply->address = $lingLiData['receive_address_all'];
            $businessApply->lat = $lingLiData['lat'];
            $businessApply->lng = $lingLiData['lng'];
            $businessApply->remark = "";
            $businessApply->invite_code = "";
            $businessApply->has_store = 0;
            $businessApply->operator_id = 0;
            $businessApply->operator_name = "零里导入";
            $businessApply->operator_remark = "零里导入";
            $businessApply->action = BusinessApply::ACTION_ACCEPT;
            $businessApply->company_id = $companyId;
            $businessApply->head_img_url = $headImageUrl;

            ExceptionAssert::assertTrue($businessApply->save(),StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,BusinessCommon::getModelErrors($businessApply)));

            //根据申请表创建
            self::createDelivery($businessApply);

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

    public static function getLingLiDelivery($phone){
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
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::USER_INFO_REGISTER_ERROR,$e->getMessage()));
        }
    }

    private static function createDelivery($businessApply){
        $userInfoModel = UserInfoService::getActiveModel($businessApply['user_id'],true);
        ExceptionAssert::assertNotNull($userInfoModel,BusinessException::create("用户未注册"));
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['em_phone'],'em_phone');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['wx_number'],'wx_number');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['realname'],'realname');
        ModelUtils::setIfNotExist($userInfoModel,$businessApply['head_img_url'],'head_img_url');
        $userInfoModel->is_delivery = CommonStatus::STATUS_ACTIVE;
        ExceptionAssert::assertTrue($userInfoModel->save(),BusinessException::create("用户信息更新失败"));

        $delivery = new Delivery();
        $delivery->user_id = $businessApply['user_id'];
        $delivery->company_id = $businessApply['company_id'];
        $delivery->nickname = $businessApply['realname'];
        $delivery->realname = $businessApply['realname'];
        $delivery->wx_number = $businessApply['wx_number'];
        $delivery->province_id = $businessApply['province_id'];
        $delivery->city_id = $businessApply['city_id'];
        $delivery->county_id = $businessApply['county_id'];
        $delivery->community = $businessApply['community'];
        $delivery->address = $businessApply['address'];
        $delivery->lng = $businessApply['lng'];
        $delivery->lat = $businessApply['lat'];
        $delivery->status = CommonStatus::STATUS_ACTIVE;
        $delivery->phone = $userInfoModel['phone'];
        $delivery->em_phone = $businessApply['em_phone'];
        $delivery->min_amount_limit = 0;
        $delivery->allow_order = CommonStatus::STATUS_ACTIVE;
        $delivery->type = Delivery::TYPE_COOPERATE;
        $delivery->head_img_url = $businessApply['head_img_url'];
        ExceptionAssert::assertTrue($delivery->save(),BusinessException::create("配送团长注册失败"));

    }
}