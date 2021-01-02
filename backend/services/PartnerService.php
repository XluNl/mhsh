<?php


namespace backend\services;

class PartnerService extends \common\services\PartnerService
{
    /**
     * 整理合伙人订单数据
     * @param $data
     * @param $companyId
     * @param $startDate
     * @param $endDate
     */
    public static function getPartnerOrderClearData(&$data, $companyId, $startDate, $endDate){
        $PartnerOrderData = parent::getPartnerOrderData($companyId, $startDate, $endDate);
        foreach ($data as $k => $v){
            // 默认值
            $data[$k]['order_count'] = 0;
            foreach ($PartnerOrderData as $partnerOrder){
                if ($data[$k]['id'] === $partnerOrder['order_owner_id']){
                    $data[$k]['order_count'] = $partnerOrder['count'];
                }
            }
        }
    }
}