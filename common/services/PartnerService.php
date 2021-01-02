<?php


namespace common\services;


use common\models\Delivery;
use common\models\Order;
use yii\db\Query;

class PartnerService
{
    /**
     * 获得合伙人订单数据
     * @param $companyId
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public static function getPartnerOrderData($companyId, $startDate, $endDate){
//        $startTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($startDate));
//        $endTime = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($endDate));

        $deliveryData = (new Query())->from(Delivery::tableName().' d')
            ->join('LEFT JOIN', '(
                select 
                    order_owner_id,
                    COALESCE(COUNT(*), 0) as count
                from '.Order::tableName().' 
                where order_owner = 3
                group by order_owner_id 
            ) as s', 'd.id=s.order_owner_id')
            ->where(['d.auth'=>Delivery::AUTH_STATUS_AUTH])
            ->andWhere(['d.company_id'=>$companyId])
            ->all();

        return $deliveryData;
    }
}