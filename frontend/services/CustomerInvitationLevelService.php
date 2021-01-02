<?php


namespace frontend\services;


use common\models\CustomerInvitationLevel;
use frontend\models\FrontendCommon;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class CustomerInvitationLevelService extends \common\services\CustomerInvitationLevelService
{
    /**
     * 创建条目
     * @param $id
     */
    public static function create($id){
        $level = new  CustomerInvitationLevel();
        $level->customer_id = $id;
        $level->one_level_num = 0;
        $level->two_level_num = 0;
        $level->level =CustomerInvitationLevel::LEVEL_NORMAL;
        ExceptionAssert::assertTrue($level->save(),StatusCode::createExpWithParams(StatusCode::CUSTOMER_INVITATION_BIND_ERROR,FrontendCommon::getModelErrors($level)));
    }

    public static function getUserInfoLevel($customerId){
        $model = parent::getModelWithUserInfo($customerId);
        return $model;
    }

}