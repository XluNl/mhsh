<?php


namespace common\services;


use common\models\Common;
use common\models\CommonStatus;
use common\models\CustomerBalanceItem;
use common\models\DistributeBalanceItem;
use common\utils\ArrayUtils;
use common\utils\StringUtils;

class CustomerBalanceVOService
{
    public static function batchSetVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $list[$k] = self::setVO($v);
        }
        return $list;
    }


    public static function setVO($model){
        $vo = [];
        $vo['amount'] = $model['amount'];
        $vo['remain_amount'] = $model['remain_amount'];
        $vo['time'] = $model['created_at'];
        $vo['in_out'] = $model['in_out'];
        $vo['in_out_text'] = ArrayUtils::getArrayValue($model['in_out'],CustomerBalanceItem::$inOutArr);
        $vo['action'] = $model['action'];
        $vo['action_text'] = ArrayUtils::getArrayValue($model['action'],CustomerBalanceItem::$actionArr);
        $vo['title'] = ArrayUtils::getArrayValue($model['biz_type'],CustomerBalanceItem::$bizTypeArr);
        $vo['sub_title'] = '';
        if ($model['biz_type'] == CustomerBalanceItem::BIZ_TYPE_ORDER_PAY){
            $vo['sub_title'] = $model['biz_code'];
        }
        else if ($model['biz_type'] == CustomerBalanceItem::BIZ_TYPE_ORDER_REFUND){
            $vo['sub_title'] = $model['biz_code'];
        }
        return $vo;
    }
}