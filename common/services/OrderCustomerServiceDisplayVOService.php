<?php


namespace common\services;


use business\services\OrderCustomerServiceService;
use common\models\Common;
use common\models\OrderCustomerService;
use common\models\OrderCustomerServiceLog;
use common\utils\ArrayUtils;

class OrderCustomerServiceDisplayVOService
{
    /**
     * 设置文本
     * @param $customerServiceModel
     * @return mixed
     */
    public static function setDisplayVO($customerServiceModel){
        if (empty($customerServiceModel)){
            return $customerServiceModel;
        }
        $customerServiceModel = GoodsDisplayDomainService::renameImageUrl($customerServiceModel,'images');
        $customerServiceModel['status_text'] = ArrayUtils::getArrayValue($customerServiceModel['audit_level'],OrderCustomerService::$auditLevelArr,'').ArrayUtils::getArrayValue($customerServiceModel['status'],OrderCustomerService::$statusArr,'');
        $customerServiceModel['type_text'] = ArrayUtils::getArrayValue($customerServiceModel['type'],OrderCustomerService::$typeArr,'');
        $customerServiceModel['can_audit_delivery'] = $customerServiceModel['status']==OrderCustomerService::STATUS_UN_DEAL&&$customerServiceModel['audit_level']==OrderCustomerService::AUDIT_LEVEL_DELIVERY_OR_ALLIANCE?1:0;
        return $customerServiceModel;
    }


    /**
     * 批量设置显示文本
     * @param $list
     * @return array
     */
    public static function batchSetDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setDisplayVO($v);
            $v['goods'] = OrderDisplayDomainService::batchDefineDeliveryStatusText($v['goods']);
            $v['goods'] = OrderDisplayDomainService::batchDefineExpectArriveTimeText($v['goods']);
            $v['goods'] = GoodsDisplayDomainService::assembleImage($v['goods']);
            $v['goods'] = self::setRemarkText($v['type'],$v['goods']);
            foreach ($v['goods'] as $kk=>$vv){
                $vv = self::calcClaimAmount($v['type'],$vv);
                $v['goods'][$kk] = $vv;
            }
            $list[$k] = $v;
        }
        return $list;
    }

    public static function setRemarkText($type,$goods){
        foreach ($goods as $k=>$v){
            if ($type==OrderCustomerService::TYPE_REFUND_CLAIM){
                $v['remark'] = "预计赔付".Common::showAmount(OrderCustomerServiceService::calcClaimAmount($v['amount'],$v['order_goods_num'],$v['num']));
            }
            else {
                $v['remark'] = "";
            }
            $goods[$k] = $v;
        }
        return $goods;
    }



    public static function batchSetLogsVO(&$list){
        if (empty($list)){
            return;
        }
        foreach ($list as $k=>$v){
            if (key_exists('logs',$v)){
                foreach ($v['logs'] as $kk=>$vv){
                    $vv = self::setLogsVO($vv);
                    $v['logs'][$kk] = $vv;
                }
            }
            $list[$k] = $v;
        }
    }

    public static function setLogsVO($arr){
        if (empty($arr)){
            return [];
        }
        $arr['action_text'] = ArrayUtils::getArrayValue($arr['action'],OrderCustomerServiceLog::$actionArr);
        return $arr;
    }

    public static function batchSetDisplayVOB($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setDisplayVO($v);
            $v['order'] = OrderDisplayDomainService::defineOrderDisplayData($v['order'],false);
            RegionService::setProvinceAndCityAndCountyForOrder($v['order']);
            foreach ($v['goods'] as $kk=>$vv){
                $vv['orderGoods'] = OrderDisplayDomainService::defineDeliveryStatusText($vv['orderGoods']);
                $vv['orderGoods'] = OrderDisplayDomainService::defineExpectArriveTimeText($vv['orderGoods']);
                $vv['orderGoods'] = GoodsDisplayDomainService::assembleImageOne($vv['orderGoods']);
                $vv = self::calcClaimAmount($v['type'],$vv);
                $v['goods'][$kk] =  $vv;
            }
            $list[$k] = $v;
        }
        return $list;
    }

    public static function calcClaimAmount($type, $customerServiceGoods){
        $customerServiceGoods['remark'] = "预计赔付".Common::showAmountWithYuan($customerServiceGoods['order_goods_order_amount']-$customerServiceGoods['order_goods_ac_amount']);
        $customerServiceGoods['claimAmount'] = $customerServiceGoods['order_goods_order_amount']-$customerServiceGoods['order_goods_ac_amount'];
        return $customerServiceGoods;
    }

}