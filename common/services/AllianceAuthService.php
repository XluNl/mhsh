<?php


namespace common\services;


use common\models\Alliance;
use common\models\SystemOptions;

class AllianceAuthService
{

    /**
     * 判断是否可以新增账户
     * @param $userId
     * @return array
     */
    public static function checkCreateAlliance($userId){
        $allianceModels = AllianceService::getActiveModelByUserId($userId);
        if (empty($allianceModels)){
            return [true,''];
        }
        $authCount = 0;
        $noAuthCount = 0;
        foreach ($allianceModels as $value){
            if ($value['status']!=Alliance::STATUS_OFFLINE){
                if ($value['auth']==Alliance::AUTH_STATUS_AUTH){
                    $authCount++;
                }
                else{
                    $noAuthCount++;
                }

            }
        }
        if ($authCount>0){
            return [true,""];
        }
        $maxNoAuthCount = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ALLIANCE_COUNT_FOR_NO_AUTH);
        if ($noAuthCount>=$maxNoAuthCount){
            return [false,"未交保证金商户最大支持开店{$maxNoAuthCount}个"];
        }
        else{
            return [true,""];
        }
    }

    /**
     * 判断是否还能再发布商品
     * @param $allianceModel
     * @param null $goodsId
     * @return array
     */
    public static function checkCreateGoods($allianceModel,$goodsId=null){
        if ($allianceModel===null){
            return [false,'联盟店铺不存在'];
        }
        if ($allianceModel['status']==Alliance::STATUS_OFFLINE){
            return [false,'联盟店铺已下线'];
        }
        if ($allianceModel['auth']==Alliance::AUTH_STATUS_AUTH){
            return [true,''];
        }
        $goodsModels = GoodsService::getActiveOwnerGoodsList($allianceModel['company_id'],$allianceModel['id']);
        $goodsCount = 0;
        if (empty($goodsModels)){
            foreach ($goodsModels as $value){
                if ($value['id']!=$goodsId){
                    $goodsCount++;
                }
            }
        }
        $maxNoAuthGoodsCount = SystemOptionsService::getSystemOptionValue(SystemOptions::OPTION_FIELD_SYSTEM_ALLIANCE_GOODS_COUNT_FOR_NO_AUTH);
        if ($goodsCount>=$maxNoAuthGoodsCount){
            return [false,"未交保证金店铺最大支持发布{$maxNoAuthGoodsCount}个商品"];
        }
        else{
            return [true,''];
        }
    }

}