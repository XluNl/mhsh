<?php


namespace common\services;


use common\models\CouponBatch;
use common\models\CouponBatchDrawLog;
use common\utils\DateTimeUtils;
use yii\db\Query;

class CouponBatchDrawLogService
{
    public static function calc($type,$customerId,$batchId){
        $conditions = ['and',['customer_id'=>$customerId,'batch_id'=>$batchId]];
        $nowTime = time();
        if ($type==CouponBatch::DRAW_TYPE_LIMIT_ALL){

        }
        else if ($type==CouponBatch::DRAW_TYPE_LIMIT_DAY){
            $start = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfDayLong($nowTime,false)) ;
            $end   = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfDayLong($nowTime,false));
            $conditions[] = ['between','created_at',$start,$end];
        }
        else if ($type==CouponBatch::DRAW_TYPE_LIMIT_WEEK){
            $start = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfWeekLong($nowTime,false));
            $end   = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfWeekLong($nowTime,false));
            $conditions[] = ['between','created_at',$start,$end];
        }
        else if ($type==CouponBatch::DRAW_TYPE_LIMIT_MONTH){
            $start = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfMonthLong($nowTime,false));
            $end   = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfMonthLong($nowTime,false));
            $conditions[] = ['between','created_at',$start,$end];
        }
        else if ($type==CouponBatch::DRAW_TYPE_LIMIT_YEAR){
            $start = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::startOfYearLong($nowTime,false));
            $end   = DateTimeUtils::parseStandardWLongDate(DateTimeUtils::endOfYearLong($nowTime,false));
            $conditions[] = ['between','created_at',$start,$end];
        }
        $count = (new Query())->from(CouponBatchDrawLog::tableName())->where($conditions)->count();
        return $count;
    }
}