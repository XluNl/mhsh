<?php


namespace frontend\services;


use common\models\CommonStatus;
use common\models\CustomerInvitation;
use common\models\OrderPreDistribute;
use common\services\GoodsDisplayDomainService;
use common\utils\DateTimeUtils;
use common\utils\PhoneUtils;
use common\utils\StringUtils;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class CustomerInvitationService extends \common\services\CustomerInvitationService
{
    /**
     * 增加绑定关系
     * @param $id
     * @param $inviteCode
     */
    public static function bindInvitation($id,$inviteCode){
        if (StringUtils::isBlank($inviteCode)){
            return;
        }
        $customer = CustomerService::getActiveModelByInvitation($inviteCode);
        ExceptionAssert::assertNotNull($customer,StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,'邀请码无效'));
        ExceptionAssert::assertTrue($customer['id']!=$id,StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,'不能自己邀请自己'));
        $parentId = self::getInvitationById($id);
        ExceptionAssert::assertNull($parentId,StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,'不能重复绑定'));
        $invitation = new CustomerInvitation();
        $invitation->status = CommonStatus::STATUS_ACTIVE;
        $invitation->customer_id = $id;
        $invitation->parent_id = $customer['id'];
        $invitation->is_connect = CustomerInvitation::IS_CONNECT_TRUE;
        ExceptionAssert::assertTrue($invitation->save(),StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,FrontendCommon::getModelErrors($invitation)));
        $parentId = $customer['id'];
        ExceptionAssert::assertTrue(self::disconnect($parentId),StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,'断连关系失败'));
    }


    /**
     * 统计详情
     * @param $id
     * @return array
     */
    public static function getInvitationSumAndDetail($id){
        $oneLevels = CustomerInvitationService::getCustomerByParentId($id);
        $detail = [];
        $twoLevels = [];
        if (!empty($oneLevels)){
            $oneLevelDetail = CustomerInvitationService::getOneLevelInvitationDetail($id);
            $oneLevelIds = ArrayHelper::getColumn($oneLevels,'customer_id');
            $twoLevels =  CustomerInvitationService::getCustomerByParentId($oneLevelIds);
            $twoLevelDetail = CustomerInvitationService::getTwoLevelInvitationDetail($id);
            $detail = self::assembleCustomerInvitationData($oneLevels, $oneLevelDetail, $twoLevels, $twoLevelDetail, $detail);
            self::removePrivateInfo($detail);
        }
        $res = [
            'one_level_count'=>count($oneLevels),
            'two_level_count'=>count($twoLevels),
            'detail'=>$detail
        ];
        return $res;
    }

    /**
     * 删除隐私信息
     * @param $details
     */
    private static function removePrivateInfo(&$details){
        foreach ($details as $oneK=>$oneV) {
            unset($oneV['phone_org']);
            foreach ($oneV['children'] as $twoK=>$twoV) {
                unset($twoV['phone_org']);
                $oneV['children'][$twoK] = $twoV;
            }
            $details[$oneK] = $oneV;
        }
    }


    /**
     * 获取用户等级
     * @param $customerId
     * @param $targetCustomerId
     * @return int|null
     */
    public static function getInvitationLevel($customerId,$targetCustomerId){
        if (StringUtils::isBlank($customerId)||StringUtils::isBlank($targetCustomerId)){
            return null;
        }
        $oneInvitation = (new Query())->from(CustomerInvitation::tableName())
            ->select(['customer_id'])
            ->where(['parent_id'=>$customerId,'status'=>CommonStatus::STATUS_ACTIVE])->all();
        $oneLevelIds = [];
        if (!empty($oneInvitation)){
            $oneLevelIds = ArrayHelper::getColumn($oneInvitation,'customer_id');
        }
        if (in_array($targetCustomerId,$oneLevelIds)){
            return OrderPreDistribute::LEVEL_ONE;
        }
        $twoInvitation = (new Query())->from(CustomerInvitation::tableName())
            ->select(['customer_id'])
            ->where(['parent_id'=>$oneLevelIds,'status'=>CommonStatus::STATUS_ACTIVE])->all();
        $twoLevelIds = [];
        if (!empty($twoInvitation)){
            $twoLevelIds = ArrayHelper::getColumn($twoInvitation,'customer_id');
        }
        if (in_array($targetCustomerId,$twoLevelIds)){
            return OrderPreDistribute::LEVEL_TWO;
        }
        return null;
    }


    /**
     * 找到二级邀请
     * 1.先判断是否有下级，有的话，直接返回空
     * 2.否则查询上级
     * @param $id
     * @param $parentId
     * @return array|bool|null
     */
    public static function getTwoInvitationById($id,$parentId){

        if (StringUtils::isBlank($id)){
            return null;
        }
        $children = self::getInvitationByParentId($id);
        if (!empty($children)){
            return null;
        }
        return self::getInvitationById($parentId);
    }

    /**
     * 断连
     * @param $customerId
     * @return bool
     */
    public static function disconnect($customerId){
        $count = CustomerInvitation::updateAll(['is_connect'=>CustomerInvitation::IS_CONNECT_FALSE,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['customer_id'=>$customerId]);
        return true;
    }

    /**
     * @param $customerId
     * @return array|bool|\common\models\Customer|\yii\db\ActiveRecord|null
     */
    public static function getParentCustomerInfoB($customerId){
        if (StringUtils::isBlank($customerId)){
            return null;
        }
        $customer = parent::getParentCustomerInfo($customerId);
        if (empty($customer)){
            return null;
        }
        $userInfo =  UserInfoService::getActiveModel($customer['user_id']);

        PhoneUtils::replacePhoneMark($customer,'phone');
        PhoneUtils::replacePhoneMark($userInfo,'phone');
        PhoneUtils::replacePhoneMark($userInfo,'em_phone');
        $userInfo = GoodsDisplayDomainService::renameImageUrl($userInfo,'head_img_url');
        return [
            'customer'=>$customer,
            'userInfo'=>$userInfo
        ];
    }

}