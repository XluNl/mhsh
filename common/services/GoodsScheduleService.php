<?php


namespace common\services;


use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use common\models\GoodsSoldChannel;
use common\models\GroupActive;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class GoodsScheduleService
{
    /**
     * 获取商品排期
     * @param $scheduleId
     * @param $company_id
     * @param bool $model
     * @return array|bool|GoodsSchedule|\yii\db\ActiveRecord|null
     */
    public static function getActiveGoodsSchedule($scheduleId,$company_id,$model = false){
        $conditions = ['id' => $scheduleId, 'schedule_status' =>GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if ($model){
            return GoodsSchedule::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(GoodsSchedule::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }


    public static function getModels($scheduleIds,$collectionId=null,$companyId=null){
        $conditions = [];
        if (StringUtils::isNotBlank($scheduleIds)){
            $conditions['id'] = $scheduleIds;
        }
        if (StringUtils::isNotBlank($collectionId)){
            $conditions['collection_id'] = $collectionId;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $result = (new Query())->from(GoodsSchedule::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 修改预计送达时间时间
     * @param $scheduleId
     * @param $expectArriveTime
     * @param $companyId
     */
    public static function modifyExpectArriveTime($scheduleId,$expectArriveTime,$companyId){
        $updateCount = GoodsSchedule::updateAll(['expect_arrive_time'=>$expectArriveTime],['id'=>$scheduleId,'company_id'=>$companyId]);
    }

    public static function getDisplayUp($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$goodsName,$pageNo=null, $pageSize=null){
        return self::getScheduleUp($ownerType,$company_id,$goodsName,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize);
    }

    public static function getSoldUp($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$pageNo=null, $pageSize=null){
        return self::getScheduleUp($ownerType,$company_id,null,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_SALE, $pageNo, $pageSize);
    }

    public static function getNoneUp($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$pageNo=null, $pageSize=null){
        return self::getScheduleUp($ownerType,$company_id,null,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_NONE, $pageNo, $pageSize);
    }

    public static function getSoldUpByIds($ownerType,$company_id, $scheduleIds, $displayChannel, $deliveryId){
        return self::getScheduleUp($ownerType,$company_id,null,null,null,$scheduleIds,$displayChannel, null, null, $deliveryId,GoodsSchedule::DISPLAY_SALE, null, null);
    }

    public static function getDisplayUpByIds($ownerType,$company_id,$scheduleIds,$displayChannel,$deliveryId){
        return self::getScheduleUp($ownerType,$company_id,null,null,null,$scheduleIds,$displayChannel, null, null, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, null, null);
    }

    public static function getDisplayDetail($ownerType,$company_id,$goodsIds,$displayChannel=null){
        return self::getScheduleUp($ownerType,$company_id,null,$goodsIds,null, null,$displayChannel, null, null,null,GoodsSchedule::DISPLAY_DISPLAY, null, null);
    }

    public static function getDisplayUpToday($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$goodsName,$pageNo=null, $pageSize=null){
        $todayLastDateStr = date('Y-m-d 23:59:59',time());
        $scheduleTable = GoodsSchedule::tableName();
        $otherConditions = [];
        $otherConditions[] = ["<=","{$scheduleTable}.online_time",$todayLastDateStr];
        return self::getScheduleUp($ownerType,$company_id,$goodsName,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize,$otherConditions);
    }


    public static function getRecommendDisplayUpToday($ownerType, $companyId, $displayChannel, $bigSort, $smallSort, $deliveryId, $goodsName, $pageNo=null, $pageSize=null){
        $todayLastDateStr = date('Y-m-d 23:59:59',time());
        $scheduleTable = GoodsSchedule::tableName();
        $otherConditions = [];
        $otherConditions[] = ["<=","{$scheduleTable}.online_time",$todayLastDateStr];
        $otherConditions[] = ["{$scheduleTable}.recommend"=>GoodsSchedule::IS_RECOMMEND_TRUE];
        return self::getScheduleUp($ownerType,$companyId,$goodsName,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize,$otherConditions);
    }

    public static function getDisplayUpTomorrow($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$goodsName,$pageNo=null, $pageSize=null){
        $todayLastDateStr = date('Y-m-d 23:59:59',time());
        $tomorrowLastDateStr = date('Y-m-d 23:59:59',time()+86400);
        $scheduleTable = GoodsSchedule::tableName();
        $otherConditions = [];
        $otherConditions[] = [">","{$scheduleTable}.online_time",$todayLastDateStr];
        $otherConditions[] = ["<=","{$scheduleTable}.online_time",$tomorrowLastDateStr];
        return self::getScheduleUp($ownerType,$company_id,$goodsName,null,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize,$otherConditions);
    }


    public static function getDisplayUpAll($ownerType,$company_id,$displayChannel,$bigSort, $smallSort,$deliveryId,$goodsName,$goodsId,$pageNo=null, $pageSize=null){
        return self::getScheduleUp($ownerType,$company_id,$goodsName,$goodsId,null,null,$displayChannel, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize);
    }

    public static function getScheduleUp($ownerType,$company_id, $goodsName, $goodsIds, $skuIds,$scheduleIds, $displayChannel, $bigSort, $smallSort, $deliveryId, $displayModel = GoodsSchedule::DISPLAY_NONE, $pageNo=null, $pageSize=null, $otherConditions=[]){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $scheduleTable = GoodsSchedule::tableName();
        $goodsSoldTable = GoodsSoldChannel::tableName();
        $select = "{$scheduleTable}.*,{$skuTable}.*,{$goodsTable}.*,{$scheduleTable}.id schedule_id,{$scheduleTable}.goods_id as goods_id,(CASE WHEN({$goodsSoldTable}.sold_channel_biz_id IS NULL) THEN 0 ELSE {$goodsSoldTable}.sold_channel_biz_id END) as delivery_id";
        $onSkuTableCondition = [
            "AND",
            ["{$scheduleTable}.company_id"=>$company_id],
            [
                "{$scheduleTable}.schedule_status"=>GoodsConstantEnum::STATUS_UP,
                "{$skuTable}.sku_status"=>GoodsConstantEnum::STATUS_UP,
                "{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP,

            ],
        ];
        if (!empty($goodsIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.goods_id"=>$goodsIds];
        }

        if (!empty($skuIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.sku_id"=>$skuIds];
        }

        if (!empty($scheduleIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.id"=>$scheduleIds];
        }
        if ($displayChannel!==null){
            $onSkuTableCondition[] = ["{$scheduleTable}.schedule_display_channel"=>$displayChannel];
        }
        $nowDateStr = DateTimeUtils::parseStandardWLongDate();
        if ($displayModel==GoodsSchedule::DISPLAY_DISPLAY){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.display_start",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.display_end",$nowDateStr];
        }
        else if ($displayModel==GoodsSchedule::DISPLAY_SALE){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.online_time",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.offline_time",$nowDateStr];
        }

        if (!empty($otherConditions)){
            foreach ($otherConditions as $c){
                $onSkuTableCondition[] = $c;
            }
        }

        $onGoodsTableCondition = [
            "AND",
            ["{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP],
        ];
        if ($bigSort!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.sort_1"=>$bigSort];
        }
        if ($smallSort!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.sort_2"=>$smallSort];
        }
        if (!StringUtils::isBlank($goodsName)){
            $onGoodsTableCondition[] =  ["like","{$goodsTable}.goods_name",$goodsName];
        }
        if ($ownerType!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.goods_owner"=>$ownerType];
        }
        $conditions = [];
        if ($deliveryId!==null){
            $conditions = [
                'OR',
                ["{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT],
                [
                    "{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY,
                    "{$goodsSoldTable}.sold_channel_biz_id"=>$deliveryId,
                ]
            ];
        }
        $goodsUpQuery = (new Query())->from($scheduleTable)->select($select)
            ->innerJoin($skuTable,"{$scheduleTable}.sku_id={$skuTable}.id")
            ->innerJoin($goodsTable,"{$scheduleTable}.goods_id={$goodsTable}.id")
            ->leftJoin($goodsSoldTable,"{$scheduleTable}.goods_id={$goodsSoldTable}.goods_id and {$goodsTable}.goods_sold_channel_type = ".Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY)
            ->where([
                'and',
                $onSkuTableCondition,
                $onGoodsTableCondition,
                $conditions,
            ]);
        if ($pageNo!=null&&$pageSize!=null){
            $goodsUpQuery = $goodsUpQuery->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        // $query = $goodsUpQuery->createCommand()->getRawSql();
        // var_dump($query);die;
        $goodsUpQuery = $goodsUpQuery->orderBy("{$scheduleTable}.display_order desc,{$scheduleTable}.online_time asc");
        return $goodsUpQuery->all();
    }





    public static function statusOperationP($companyId,$goodsOwnerType,$goodsOwnerId,$id,$status){
        if (!key_exists($status,GoodsConstantEnum::$statusArr)){
            return [false,'不支持的操作'];
        }
        $count = GoodsSchedule::updateAll(['schedule_status'=>$status,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],[
            'id'=>$id,
            'company_id'=>$companyId,
            'owner_type'=>$goodsOwnerType,
            'owner_id'=>$goodsOwnerId
        ]);
        if ($count<1){
            return [false,"更新失败"];
        }
        return [true,""];
    }

    public static function assembleSkuInfoList($goodsScheduleSku){
        $goods = [];
        $newGoods = [];
        if (!empty($goodsScheduleSku)){
            foreach ($goodsScheduleSku as $sku){
                $goods_id = $sku['goods_id'];
                $schedule_id = $sku['schedule_id'];
                if (!ArrayHelper::keyExists($goods_id,$goods)){
                    $goods[$goods_id] = [];
                    $goods[$goods_id]['goods_id'] = $sku['goods_id'];
                    $goods[$goods_id]['goods_name'] = $sku['goods_name'];
                    $goods[$goods_id]['goods_img'] = $sku['goods_img'];
                    $goods[$goods_id]['goods_describe'] = $sku['goods_describe'];
                    $goods[$goods_id]['goods_owner'] = $sku['goods_owner'];
                    if (key_exists('alliance',$sku)){
                        $goods[$goods_id]['alliance'] = $sku['alliance'];
                    }
                    $goods[$goods_id]['skus'] = [];
                }
                $goods[$goods_id]['skus'][$schedule_id] = $sku;
            }
            foreach ($goods as $goods_id=>$g){
                $newSku = [];
                foreach ($goods[$goods_id]['skus'] as $skuId=>$s ){
                    $newSku[] = $s;
                }
                $goods[$goods_id]['skus'] = $newSku;
                $newGoods[] = $goods[$goods_id];
            }
        }
        return $newGoods;
    }


    public static function batchSetDisplayVO($list){
        if (empty($list)){
            return [];
        }
        foreach ($list as $k=>$v){
            $v = self::setDisplayVO($v);
            $list[$k] = $v;
        }
        return $list;
    }

    public static function setDisplayVO($model){
        if (empty($model)){
            return null;
        }
        $nowTime = time();
        $onlineTime = strtotime($model['online_time']);
        $offlineTime = strtotime($model['offline_time']);
        if ($nowTime>$offlineTime){
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_END;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_END];
        }
        else if ($model['schedule_status']!=GoodsConstantEnum::STATUS_UP){
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_SUSPEND;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_SUSPEND];
        }
        else if ($nowTime<$onlineTime) {
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_WAITING;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_WAITING];
        }
        else{
            $model['display_status'] = GoodsSchedule::DISPLAY_STATUS_IN_SALE;
            $model['display_status_text'] =  GoodsSchedule::$displayStatusTextArr[GoodsSchedule::DISPLAY_STATUS_IN_SALE];
        }
        $model['schedule_display_channel_text'] = ArrayUtils::getArrayValue($model['schedule_display_channel'],GoodsSchedule::$displayStatusTextArr);
        return $model;
    }

    /**
     * 根据排期id批量获取（带商品&属性）
     * @param $scheduleIds
     * @param $company_id
     * @param $ownerType
     * @param $ownerId
     * @return array
     */
    public static function getActiveGoodsScheduleWithGoodsAndSku( $scheduleIds, $company_id,$ownerType, $ownerId){
        $scheduleTable = GoodsSchedule::tableName();
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $conditions = [
            "{$scheduleTable}.id" => $scheduleIds,
            "{$scheduleTable}.schedule_status" =>GoodsConstantEnum::$activeStatusArr,
            "{$scheduleTable}.owner_type" =>$ownerType,
            "{$scheduleTable}.company_id"=>$company_id
        ];
        if (StringUtils::isNotBlank($ownerId)){
            $conditions["{$scheduleTable}.owner_id"] = $ownerId;
        }
        $result = (new Query())->from($scheduleTable)
            ->leftJoin($goodsTable,"{$goodsTable}.id={$scheduleTable}.goods_id")
            ->leftJoin($skuTable,"{$skuTable}.id={$scheduleTable}.sku_id")
            ->select("{$scheduleTable}.*,{$goodsTable}.*,{$skuTable}.*,{$scheduleTable}.id as schedule_id")
            ->where($conditions)->all();
        return $result;
    }


    /*
        --------------------------拼团相关-----------------------------
     */



    public static function getGroupActiveDisplayUpToday($ownerType,$company_id,$bigSort, $smallSort,$deliveryId,$goodsName,$pageNo=null, $pageSize=null){
        $todayLastDateStr = date('Y-m-d 23:59:59',time());
        $scheduleTable = GoodsSchedule::tableName();
        $otherConditions = [];
        $otherConditions[] = ["<=","{$scheduleTable}.online_time",$todayLastDateStr];
        return self::getGroupActiveScheduleUp(null,$ownerType,$company_id,$goodsName,null,null,null,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_GROUP, $bigSort, $smallSort, $deliveryId,GoodsSchedule::DISPLAY_DISPLAY, $pageNo, $pageSize,$otherConditions);
    }

    public static function getGroupActiveSoldUpByIds($activeNos,$ownerType,$company_id, $deliveryId){
        return self::getGroupActiveScheduleUp($activeNos,$ownerType,$company_id,null,null,null,null,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_GROUP, null, null, $deliveryId,GoodsSchedule::DISPLAY_SALE, null, null);
    }

    /**
     * 可下单的拼团详情
     * @param $activeNos
     * @param $ownerType
     * @param $company_id
     * @return array
     */
    public static function getGroupActiveDisplayDetailUp($activeNos,$ownerType,$company_id){
        return self::getGroupActiveScheduleUp($activeNos,$ownerType,$company_id,null,null,null, null,null, null, null,null,GoodsSchedule::DISPLAY_DISPLAY, null, null);
    }

    /**
     * 展示的拼团详情
     * @param $activeNos
     * @param $ownerType
     * @param $companyId
     * @return array
     */
    public static function getGroupActiveDisplayDetail($activeNos, $ownerType, $companyId){
        return self::getGroupActiveSchedule(false,$activeNos,$ownerType,$companyId,null,null,null, null,null, null, null,null,null, null, null);
    }

    public static function getGroupActiveScheduleUp($activeNos, $ownerType, $companyId, $goodsName, $goodsIds, $skuIds, $scheduleIds, $displayChannel, $bigSort, $smallSort, $deliveryId, $displayModel = GoodsSchedule::DISPLAY_NONE, $pageNo=null, $pageSize=null, $otherConditions=[]){
        return self::getGroupActiveSchedule(true,$activeNos,$ownerType,$companyId, $goodsName, $goodsIds, $skuIds,$scheduleIds, $displayChannel, $bigSort, $smallSort, $deliveryId, $displayModel, $pageNo, $pageSize, $otherConditions);
    }

    public static function getGroupActiveSchedule($up, $activeNos, $ownerType, $companyId, $goodsName, $goodsIds, $skuIds, $scheduleIds, $displayChannel, $bigSort, $smallSort, $deliveryId, $displayModel = GoodsSchedule::DISPLAY_NONE, $pageNo=null, $pageSize=null, $otherConditions=[]){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $scheduleTable = GoodsSchedule::tableName();
        $goodsSoldTable = GoodsSoldChannel::tableName();
        $groupActiveTable = GroupActive::tableName();
        $select = "{$groupActiveTable}.*,{$scheduleTable}.*,{$skuTable}.*,{$goodsTable}.*,{$groupActiveTable}.id active_id,{$scheduleTable}.id schedule_id,{$scheduleTable}.goods_id as goods_id,(CASE WHEN({$goodsSoldTable}.sold_channel_biz_id IS NULL) THEN 0 ELSE {$goodsSoldTable}.sold_channel_biz_id END) as delivery_id";

        $onSkuTableCondition = [
            "AND",
            ["{$scheduleTable}.company_id"=>$companyId],
        ];
        if ($up){
            $onSkuTableCondition[] = [
                "{$scheduleTable}.schedule_status"=>GoodsConstantEnum::STATUS_UP,
                "{$skuTable}.sku_status"=>GoodsConstantEnum::STATUS_UP,
                "{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP,
            ];
        }
        if (!empty($goodsIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.goods_id"=>$goodsIds];
        }

        if (!empty($skuIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.sku_id"=>$skuIds];
        }

        if (!empty($scheduleIds)){
            $onSkuTableCondition[] =  ["{$scheduleTable}.id"=>$scheduleIds];
        }
        if ($displayChannel!==null){
            $onSkuTableCondition[] = ["{$scheduleTable}.schedule_display_channel"=>$displayChannel];
        }
        $nowDateStr = DateTimeUtils::parseStandardWLongDate();
        if ($displayModel==GoodsSchedule::DISPLAY_DISPLAY){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.display_start",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.display_end",$nowDateStr];
        }
        else if ($displayModel==GoodsSchedule::DISPLAY_SALE){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.online_time",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.offline_time",$nowDateStr];
        }

        if (!empty($otherConditions)){
            foreach ($otherConditions as $c){
                $onSkuTableCondition[] = $c;
            }
        }

        $onGoodsTableCondition = [
            "AND",
            ["{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP],
        ];
        if ($bigSort!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.sort_1"=>$bigSort];
        }
        if ($smallSort!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.sort_2"=>$smallSort];
        }
        if (!StringUtils::isBlank($goodsName)){
            $onGoodsTableCondition[] =  ["like","{$goodsTable}.goods_name",$goodsName];
        }
        if ($ownerType!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.goods_owner"=>$ownerType];
        }
        $soldChannelConditions = [];
        if ($deliveryId!==null){
            $soldChannelConditions = [
                'OR',
                ["{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT],
                [
                    "{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY,
                    "{$goodsSoldTable}.sold_channel_biz_id"=>$deliveryId,
                ]
            ];
        }

        $groupActiveConditions = [
            "AND",
        ];
        if ($up){
            $groupActiveConditions[] =  ["{$groupActiveTable}.status"=>GroupActive::STATUS_UP];
        }
        if (StringUtils::isNotBlank($ownerType)){
            $groupActiveConditions[]=["{$groupActiveTable}.owner_type"=>$ownerType];
        }
        if (StringUtils::isNotBlankAndNotEmpty($activeNos)){
            $groupActiveConditions[]=["{$groupActiveTable}.active_no"=>$activeNos];
        }

        $goodsUpQuery = (new Query())->from($scheduleTable)->select($select)
            ->innerJoin($skuTable,"{$scheduleTable}.sku_id={$skuTable}.id")
            ->innerJoin($goodsTable,"{$scheduleTable}.goods_id={$goodsTable}.id")
            ->leftJoin($goodsSoldTable,"{$scheduleTable}.goods_id={$goodsSoldTable}.goods_id and {$goodsTable}.goods_sold_channel_type = ".Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY)
            ->leftJoin($groupActiveTable,"{$scheduleTable}.id={$groupActiveTable}.schedule_id")
            ->where([
                'and',
                $onSkuTableCondition,
                $onGoodsTableCondition,
                $soldChannelConditions,
                $groupActiveConditions
            ]);
        if ($pageNo!=null&&$pageSize!=null){
            $goodsUpQuery = $goodsUpQuery->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $goodsUpQuery = $goodsUpQuery->orderBy("{$scheduleTable}.display_order desc,{$scheduleTable}.online_time asc");
        return $goodsUpQuery->all();
    }
}