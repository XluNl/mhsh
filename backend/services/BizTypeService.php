<?php


namespace backend\services;


use common\models\BizTypeEnum;
use yii\helpers\Html;

class BizTypeService extends \common\services\BizTypeService
{

    public static function createJumpUrl($bizType,$bizId,$bizName){
        if ($bizType==BizTypeEnum::BIZ_TYPE_CUSTOMER_DISTRIBUTE){
            return $bizName;
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_POPULARIZER){
            return Html::a($bizName,['popularizer/index','PopularizerSearch[id]'=>$bizId]);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_DELIVERY){
            return Html::a($bizName,['delivery/index','DeliverySearch[id]'=>$bizId]);
        }
        else if ($bizType==BizTypeEnum::BIZ_TYPE_AGENT){
            return $bizName;
        }
        else{
            return $bizName;
        }
    }
}