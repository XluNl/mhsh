<?php


namespace frontend\services;


use common\models\CouponBatch;
use common\utils\DateTimeUtils;
use yii\helpers\ArrayHelper;

class IndexService
{

    public static function assembleGoodsSkuAndSort($sortList,$skuList){
        if (!empty($sortList)){
            $sortList = ArrayHelper::index($sortList,'id');
            self::assembleStatusAndImageAndExceptTime($skuList);
            foreach ($skuList as $k=>$v){
                $sortId = $v['sort_1'];
                if (key_exists($sortId,$sortList)){
                    if (!key_exists('skuList',$sortList[$sortId])){
                        $sortList[$sortId]['skuList'] =[];
                    }

                    $sortList[$sortId]['skuList'][] = $v;
                }
            }
            foreach ($sortList as $k=>$v){
                if (!key_exists('skuList',$v)){
                    unset($sortList[$k]);
                }
            }
            $sortList = GoodsDisplayDomainService::batchRenameImageUrl($sortList,'pic_name');
            // $sortList = GoodsDisplayDomainService::batchRenameImageUrl($sortList,'pic_icon');
            $sortList = array_values($sortList);
            return $sortList;
        }
        return [];
    }

    public static function assembleStatusAndImageAndExceptTime($scheduleList){
        $scheduleList = GoodsDisplayDomainService::assembleStatusAndImageAndExceptTime($scheduleList);
        return $scheduleList;
    }

    public static function sortByOnlineTime($scheduleList){
        if (empty($scheduleList)){
            return [];
        }
        ArrayHelper::multisort($scheduleList,'online_time');
        return $scheduleList;
    }

    /**
     * 过滤过弹窗的优惠券活动
     * @param $couponBatchList array
     * @return array
     */
    public static function filterPopCouponList($couponBatchList){
        if (empty($couponBatchList)){
            return [];
        }
        $popList = [];
        foreach ($couponBatchList as $v){
            if ($v['is_pop']==CouponBatch::IS_POP_TRUE){
                $popList[] = $v;
            }
        }
        return $popList;
    }

    /**
     * 对秒杀商品进行分类
     * @param $skuList
     * @return array
     */
    public static function classifyByOnlineTime($skuList){
        if (empty($skuList)){
            return [];
        }
        $nowTime = time();
        $nowTimeStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $onLineTimeList = [
            $nowTimeStr=>[
                'time'=>$nowTimeStr,
                'time_text'=>'进行中',
                'future'=>false,
                'skuList'=>[],
            ],
        ];

        foreach ($skuList as $sku){
            if ($nowTime<strtotime($sku['online_time'])){
                if (!key_exists($sku['online_time'],$onLineTimeList)){
                    $newOnLineTime = [
                        'time'=>$sku['online_time'],
                        'time_text'=>self::getOnTimeText($sku['online_time']),
                        'future'=>true,
                        'skuList'=>[],
                    ];
                    $onLineTimeList[$sku['online_time']]= $newOnLineTime;
                }
                $onLineTimeList[$sku['online_time']]['skuList'][] = $sku;
            }
            else{
                $onLineTimeList[$nowTimeStr]['skuList'][] = $sku;
            }
        }
        if (empty($onLineTimeList[$nowTimeStr]['skuList'])){
            unset($onLineTimeList[$nowTimeStr]);
        }
        sort($onLineTimeList);
        return array_values($onLineTimeList);
    }

    /**
     * 生成秒杀时间
     * @param $online_time
     * @return false|string
     */
    private static function getOnTimeText($online_time){
        $dateTime = strtotime($online_time);
        $second= date('s',$dateTime);
        if ($second==0){
            return date('H:i',$dateTime);
        }
        else{
            return date('H:i:s',$dateTime);
        }
    }






}