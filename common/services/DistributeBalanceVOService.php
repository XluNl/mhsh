<?php


namespace common\services;


use common\models\Common;
use common\models\DistributeBalanceItem;
use common\utils\ArrayUtils;
use common\utils\StringUtils;

class DistributeBalanceVOService
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
        $vo['in_out_text'] = ArrayUtils::getArrayValue($model['in_out'],DistributeBalanceItem::$inOutArr);
        $vo['action'] = $model['action'];
        $vo['action_text'] = ArrayUtils::getArrayValue($model['action'],DistributeBalanceItem::$actionArr);
        if ($model['type'] == DistributeBalanceItem::TYPE_ORDER_DISTRIBUTE){
            $vo['title'] = '订单佣金';
            $vo['sub_title'] = $model['order_no'];
        }
        else if (in_array($model['type'],array_keys(DistributeBalanceItem::$typeArr))){
            $vo['title'] = DistributeBalanceItem::$typeArr[$model['type']];
            $vo['sub_title'] = StringUtils::isBlank($model['remark'])?"":$model['remark'];
        }
        else {
            $vo['title'] = '未知';
            $vo['sub_title'] = "";
        }
        return $vo;
    }
}