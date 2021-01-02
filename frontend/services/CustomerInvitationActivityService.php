<?php


namespace frontend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use common\models\Customer;
use common\models\CustomerInvitationActivity;
use common\models\UserInfo;
use common\services\CustomerInvitationActivityResultService;
use common\services\GoodsDisplayDomainService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\PhoneUtils;
use yii\db\Query;

class CustomerInvitationActivityService extends \common\services\CustomerInvitationActivityService
{

    public static function getActivity($companyId,$customerId){
        $nowTime = DateTimeUtils::parseStandardWLongDate(time());
        $activity = parent::getActiveModelOne($companyId,$nowTime,CustomerInvitationActivity::TYPE_INVITATION);

        if (empty($activity)){
            return [];
        }
        $activity = GoodsDisplayDomainService::renameImageUrl($activity,'image');
        $activity['invitation'] = [];
        if ($activity['settle_status']==CustomerInvitationActivity::SETTLE_STATUS_DEAL){
            $activity['biz_status'] = CustomerInvitationActivity::BIZ_STATUS_SETTLED;
            $activity['biz_status_text'] = ArrayUtils::getArrayValue(CustomerInvitationActivity::BIZ_STATUS_SETTLED,CustomerInvitationActivity::$bizStatusArr);
            $activity['invitation'] = CustomerInvitationActivityResultService::getModel($activity['id'],$customerId);
        }
        else if ($activity['activity_start_time']>$nowTime){
            $activity['biz_status'] = CustomerInvitationActivity::BIZ_STATUS_UN_START;
            $activity['biz_status_text'] = ArrayUtils::getArrayValue(CustomerInvitationActivity::BIZ_STATUS_UN_START,CustomerInvitationActivity::$bizStatusArr);
        }
        else if ($activity['activity_start_time']<=$nowTime&&$activity['activity_end_time']>=$nowTime){
            $activity['biz_status'] = CustomerInvitationActivity::BIZ_STATUS_RUNNING;
            $activity['biz_status_text'] = ArrayUtils::getArrayValue(CustomerInvitationActivity::BIZ_STATUS_RUNNING,CustomerInvitationActivity::$bizStatusArr);
        }
        else {
            $activity['biz_status'] = CustomerInvitationActivity::BIZ_STATUS_UN_SETTLED;
            $activity['biz_status_text'] = ArrayUtils::getArrayValue(CustomerInvitationActivity::BIZ_STATUS_UN_SETTLED,CustomerInvitationActivity::$bizStatusArr);
        }
        $invitation = self::getOrderStatistic($activity['id'],$companyId,$customerId);
        self::completeUserInfo($invitation);
        self::maskPhone($invitation);
        $activity['invitation'] = $invitation;
        return $activity;
    }

    private static function getOrderStatistic($activityId,$companyId,$customerId){
        list($res,$data) = parent::preStatistic($activityId,$companyId,$customerId);
        if (!$res){
            BExceptionAssert::assertTrue($res,BStatusCode::createExpWithParams(BStatusCode::CUSTOMER_INVITATION_ACTIVITY_SETTLE_ERROR,$data));
        }
        if (empty($data['invitationModels'])){
            return [];
        }
        return $data['invitationModels'][0];
    }

    private static function completeUserInfo(&$invitation){
        if (empty($invitation)||empty($invitation['children'])){
            return;
        }
        $customerIds =[];
        foreach ($invitation['children'] as $k=>$v){
            $customerIds[] = $v['child_customer_id'];
        }
        $customerTable = Customer::tableName();
        $userInfoTable = UserInfo::tableName();
        $userInfos = (new Query())->from($customerTable)
            ->innerJoin($userInfoTable,"{$userInfoTable}.id={$customerTable}.user_id")
            ->where(["{$customerTable}.id"=>$customerIds])
            ->select(["{$userInfoTable}.*","{$customerTable}.id as customer_id"])->all();
        $userInfos = GoodsDisplayDomainService::batchRenameImageUrl($userInfos,"head_img_url");
        $userInfos = ArrayUtils::index($userInfos,'customer_id');
        foreach ($invitation['children'] as $k=>$v){
            if (key_exists($v['child_customer_id'],$userInfos)){
                $v['child_customer_head'] = $userInfos[$v['child_customer_id']]['head_img_url'];
            }
            else{
                $v['child_customer_head'] ="";
            }
            $invitation['children'][$k] = $v;
        }
    }

    private static function maskPhone(&$invitation){
        if (empty($invitation)||empty($invitation['children'])){
            return;
        }
        PhoneUtils::replacePhoneMark($invitation,"customer_phone");
        PhoneUtils::batchReplacePhoneMark($invitation['children'],"child_customer_phone");
        foreach ($invitation['children'] as $k=>$v){
            if (!empty($v['children'])){
                PhoneUtils::batchReplacePhoneMark($v['children'],"child_customer_phone");
            }
            $invitation['children'][$k] = $v;
        }
    }
}