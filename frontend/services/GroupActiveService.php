<?php


namespace frontend\services;

use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class GroupActiveService extends \common\services\GroupActiveService
{

    public static function getGroupActiveList($ownerType, $companyId, $deliveryId,$keyword, $pageNo=1, $pageSize=20){
        $groupActiveList = GoodsScheduleService::getGroupActiveDisplayUpToday($ownerType,$companyId,null,null,$deliveryId,$keyword,$pageNo,$pageSize);
        $groupActiveList = self::batchDisplayVO($groupActiveList);
        $groupActiveList = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($groupActiveList);
        return $groupActiveList;
    }


    public static function getGroupActiveDetail($activeNo,$ownerType, $companyId){
        $groupActiveList = GoodsScheduleService::getGroupActiveDisplayDetail($activeNo,$ownerType,$companyId);
        ExceptionAssert::assertNotEmpty($groupActiveList,StatusCode::createExp(StatusCode::GROUP_ACTIVE_NOT_EXIST));
        $groupActiveList = self::batchDisplayVO($groupActiveList);
        $groupActiveList = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($groupActiveList);
        $groupActiveList = GoodsService::completeDetail($groupActiveList,$companyId);
        return $groupActiveList[0];
    }


    public static function getGroupActiveStatistic($activeNo){
        return GroupRoomService::getRoomStatistic($activeNo);
    }
}