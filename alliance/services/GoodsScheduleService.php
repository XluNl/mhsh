<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/26/026
 * Time: 1:10
 */

namespace alliance\services;


use common\models\GoodsConstantEnum;

class GoodsScheduleService extends \common\services\GoodsScheduleService
{

    public static function getActiveGoodsScheduleWithGoodsAndSkuA($scheduleIds, $company_id, $allianceId){
        return parent::getActiveGoodsScheduleWithGoodsAndSku($scheduleIds, $company_id,GoodsConstantEnum::OWNER_HA, $allianceId);
    }
}