<?php


namespace console\services;


use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsSchedule;
use common\models\GoodsSku;
use common\models\GoodsSoldChannel;
use common\services\GoodsDisplayDomainService;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\HttpClientUtils;
use common\utils\PathUtils;
use console\utils\exceptions\BusinessException;
use console\utils\response\StarBaseResponseAssert;
use yii\db\Query;
use Yii;
use yii\helpers\Json;

class StarService
{
    /**
     * @param $nowTime
     * @return array
     */
    public static function synchronizeGoods($nowTime){
        $nowDateStr = DateTimeUtils::parseStandardWLongDate($nowTime);
        $query = self::getScheduleUp($nowDateStr,GoodsConstantEnum::OWNER_SELF,GoodsConstantEnum::SCHEDULE_DISPLAY_CHANNEL_STAR,GoodsSchedule::DISPLAY_SALE);
        $successCount = 0;
        $failedCount = 0;
        try {
            self::allDownStarGoods();
            list($successCount,$failedCount) = self::batchNotify($query, 20);
            return [true,$successCount,$failedCount,''];
        }
        catch (\Exception $e){
            return [false,$successCount,$failedCount,$e->getMessage()];
        }
    }

    /**
     * @param $nowDateStr
     * @param $ownerType
     * @param $displayChannel
     * @param int $displayModel
     * @return Query
     */
    private static function getScheduleUp($nowDateStr, $ownerType,$displayChannel=null ,  $displayModel = GoodsSchedule::DISPLAY_NONE){
        $goodsTable = Goods::tableName();
        $skuTable = GoodsSku::tableName();
        $scheduleTable = GoodsSchedule::tableName();
        $goodsSoldTable = GoodsSoldChannel::tableName();
        $select = "{$scheduleTable}.*,{$skuTable}.*,{$goodsTable}.*,{$scheduleTable}.id schedule_id,{$scheduleTable}.goods_id as goods_id,(CASE WHEN({$goodsSoldTable}.sold_channel_biz_id IS NULL) THEN 0 ELSE {$goodsSoldTable}.sold_channel_biz_id END) as delivery_id";
        $onSkuTableCondition = [
            "AND",
            [
                "{$scheduleTable}.schedule_status"=>GoodsConstantEnum::STATUS_UP,
                "{$skuTable}.sku_status"=>GoodsConstantEnum::STATUS_UP,
                "{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP,

            ],
        ];
        if ($displayChannel!==null){
            $onSkuTableCondition[] = ["{$scheduleTable}.schedule_display_channel"=>$displayChannel];
        }
        if ($displayModel==GoodsSchedule::DISPLAY_DISPLAY||$displayModel== GoodsSchedule::DISPLAY_NONE){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.display_start",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.display_end",$nowDateStr];
        }
        else if ($displayModel==GoodsSchedule::DISPLAY_SALE){
            $onSkuTableCondition[] = ["<=","{$scheduleTable}.online_time",$nowDateStr];
            $onSkuTableCondition[] = [">=","{$scheduleTable}.offline_time",$nowDateStr];
        }

        $onGoodsTableCondition = [
            "AND",
            ["{$goodsTable}.goods_status"=>GoodsConstantEnum::STATUS_UP],
        ];
        if ($ownerType!==null){
            $onGoodsTableCondition[]=["{$goodsTable}.goods_owner"=>$ownerType];
        }
        $conditions =  ["{$goodsTable}.goods_sold_channel_type"=>Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT];

        $goodsUpQuery = (new Query())->from($scheduleTable)->select($select)
            ->innerJoin($skuTable,"{$scheduleTable}.sku_id={$skuTable}.id")
            ->innerJoin($goodsTable,"{$scheduleTable}.goods_id={$goodsTable}.id")
            ->leftJoin($goodsSoldTable,"{$scheduleTable}.goods_id={$goodsSoldTable}.goods_id and {$goodsTable}.goods_sold_channel_type = ".Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY)
            ->where([
                'and',
                $onSkuTableCondition,
                $onGoodsTableCondition,
                $conditions
            ]);
        return $goodsUpQuery;
    }

    /**
     * @param Query $query
     * @param $batchSize
     * @return array|int[]
     */
    private static function batchNotify(Query $query, $batchSize)
    {
        $goodsArr = [];
        $successCount = 0;
        $failedCount = 0;
        foreach ($query->each($batchSize) as $goods) {
            $goodsArr[] = $goods;
            if (count($goodsArr) >= $batchSize) {
                list($res,$c) = self::tryTwiceNotifyStarGoods($goodsArr);
                $res?$successCount += $c:$failedCount += $c;
                $goodsArr = [];
            }
        }
        if (count($goodsArr) >= 1) {
            list($res,$c) = self::tryTwiceNotifyStarGoods($goodsArr);
            $res?$successCount += $c:$failedCount += $c;
            $goodsArr = [];
        }
        return [$successCount,$failedCount];
    }

    private static function tryTwiceNotifyStarGoods($goodsList){
        $c = count($goodsList);
        try {
            self::notifyStarGoods($goodsList);
            return [true,$c];
        }
        catch (\Exception $e){
            try {
                self::notifyStarGoods($goodsList);
                return [true,$c];
            }
            catch (\Exception $e){
                $scheduleIds = ArrayUtils::getColumnWithoutNull('schedule_id',$goodsList);
                echo "通知两次失败 notifyStarGoods error req:".Json::encode($scheduleIds).'error:'.$e->getMessage().PHP_EOL;
                return [false,$c];
            }
        }
    }


    private static function notifyStarGoods($goodsList){
        $request['goodslist'] = self::transformRequestGoodsList($goodsList);
        $url = PathUtils::join(Yii::getAlias("@starUrl"),"/third/Goods/addOrUpdateGoodsList");
        try {
            $response = HttpClientUtils::postJson($url,$request);
            StarBaseResponseAssert::assertSuccessData($response);
        }
        catch (\Exception $e){
            throw BusinessException::create($e->getMessage());
        }
    }

    private static function allDownStarGoods(){
        $request['appid'] = Yii::$app->params['star.appid'];
        $url = PathUtils::join(Yii::getAlias("@starUrl"),"/third/Goods/allDown");
        try {
            $response = HttpClientUtils::postJson($url,$request);
            StarBaseResponseAssert::assertSuccessData($response);
        }
        catch (\Exception $e){
            echo "allDownStarGoods error:".$e->getMessage().PHP_EOL;
            throw BusinessException::create($e->getMessage());
        }
    }

    /**
     * @param $goodsList
     * @return array
     */
    private static function transformRequestGoodsList($goodsList)
    {
        $goodsArr = [];
        $goodsList = GoodsDisplayDomainService::assembleImage($goodsList);
        foreach ($goodsList as $v) {
            $t = [];
            $t['goodsId'] = $v['goods_id'];
            $t['skuId'] = $v['sku_id'];
            $t['skuUnit'] = $v['sku_unit'];
            $t['weight'] = $v['sku_unit_factor'];
            $t['goodsName'] = $v['goods_name'];
            $t['bannerUrl'] = $v['sku_img'];
            $t['dumpUrl'] = "/pages/product/detail?id={$v['goods_id']}&display_channel={$v['schedule_display_channel']}";
            $t['crossedPrice'] = Common::showAmount($v['reference_price']);
            $t['purchasePrice'] = Common::showAmount($v['price']);
            $t['thirdPlatFormAgentId'] = $v['company_id'];
            $t['status'] = 'ON_SHELF';
            $t['startTime'] = $v['online_time'];
            $t['endTime'] = $v['offline_time'];
            $t['stock'] = $v['schedule_stock'];
            $goodsArr[] = $t;
        }
        return $goodsArr;
    }

}