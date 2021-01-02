<?php


namespace common\services;

use common\models\CustomerInvitationActivityResult;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\Json;

class CustomerInvitationActivityResultService
{
    /**
     * 新增日志
     * @param $activityId
     * @param $customerId
     * @param $customerName
     * @param $customerPhone
     * @param $invitationCount
     * @param $invitationOrderCount
     * @param $invitationChildrenCount
     * @param $invitationChildrenOrderCount
     * @param $invitationData
     * @param $prizes
     * @return array
     */
    public static function addResult($activityId,$customerId,$customerName,$customerPhone,$invitationCount,$invitationOrderCount,$invitationChildrenCount,$invitationChildrenOrderCount,$invitationData,$prizes){
        $result = new CustomerInvitationActivityResult();
        $result->customer_id = $customerId;
        $result->customer_name = $customerName;
        $result->customer_phone = $customerPhone;
        $result->activity_id = $activityId;
        $result->invitation_count = $invitationCount;
        $result->invitation_order_count = $invitationOrderCount;
        $result->invitation_children_count = $invitationChildrenCount;
        $result->invitation_children_order_count = $invitationChildrenOrderCount;
        $result->children = Json::htmlEncode($invitationData);
        $result->prizes = Json::htmlEncode($prizes);
        if ($result->save()){
            return [true,''];
        }
        return [false,"邀请活动结算日志保存失败,activityId:{$activityId}|$customerId:{$activityId}"];
    }


    public static function getModel($activityId,$customerId){
        $result = (new Query())->from(CustomerInvitationActivityResult::tableName())->where(['activity_id'=>$activityId,'customer_id'=>$customerId])->one();
        if ($result===false){
            return null;
        }
        else {
            $result['prizes'] = StringUtils::isBlank($result['prizes'])?[]:Json::decode($result['prizes']);
            $result['children'] = StringUtils::isBlank($result['children'])?[]:Json::decode($result['children']);
            return $result;
        }
    }

}