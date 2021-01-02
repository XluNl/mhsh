<?php


namespace frontend\services;


use common\models\GoodsConstantEnum;
use frontend\utils\ExceptionAssert;
use frontend\utils\StatusCode;

class AllianceService extends \common\services\AllianceService
{
    /**
     * 获取配送方式
     * @param $allianceModel
     * @return array
     */
    public static function getAvailableFreight($allianceModel){
        $availableTypes = [];
        $availableTypes[] = [
            'type'=> GoodsConstantEnum::DELIVERY_TYPE_ALLIANCE_SELF,
            'name'=>GoodsConstantEnum::$deliveryTypeArr[GoodsConstantEnum::DELIVERY_TYPE_ALLIANCE_SELF],
            'amount'=>0,
        ];
        return $availableTypes;
    }

    public static function getFreight($deliveryType,$allianceId,$company_id,$pay_amount,$distance)
    {
        if ($deliveryType==GoodsConstantEnum::DELIVERY_TYPE_ALLIANCE_SELF){
            return 0;
        }
        else{
            ExceptionAssert::assertTrue(false,StatusCode::createExp(StatusCode::DELIVERY_TYPE_NOT_EXIST));
        }
    }
}