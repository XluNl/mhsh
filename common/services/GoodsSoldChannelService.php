<?php


namespace common\services;


use common\models\Goods;
use common\models\GoodsSoldChannel;
use common\utils\DateTimeUtils;
use yii;

class GoodsSoldChannelService
{
    /**
     *  保存渠道信息
     * @param $soldChannelType
     * @param $soldChannelIds
     * @param $goodsId
     * @param $companyId
     * @return array
     */
    public static function addGoodsSoldChannel($soldChannelType,$soldChannelIds,$goodsId,$companyId){
        if (!in_array($soldChannelType,[Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT,Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY])){
            return [false,'不支持的售卖渠道类型'];
        }
        $updateCount = Goods::updateAll([
            'goods_sold_channel_type'=>$soldChannelType,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())
        ], [
            'id'=>$goodsId,
            'company_id'=>$companyId
        ]);

        GoodsSoldChannel::deleteAll(['company_id'=>$companyId,'goods_id'=>$goodsId]);
        if (!empty($soldChannelIds)&&$soldChannelType==Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY){
            foreach ($soldChannelIds as $soldChannelId){
                $m = new GoodsSoldChannel();
                $m->sold_channel_biz_id = $soldChannelId;
                $m->goods_id =$goodsId;
                $m->company_id = $companyId;
                if (!$m->save()){
                    return [false,'商品售卖渠道列表保存失败'];
                }
            }
        }
        return [true,''];
    }


    /**
     * 团长商品投放新增
     * @param $soldChannelId
     * @param $goodsId
     * @param $companyId
     * @return array
     * @throws yii\db\Exception
     */
    public static function simpleAddGoodsSoldChannel($soldChannelId,$goodsId,$companyId){
        $num = Yii::$app->db->createCommand()->upsert(
            GoodsSoldChannel::tableName(),
            [
                'sold_channel_biz_id'=>$soldChannelId,
                'goods_id'=>$goodsId,
                'company_id'=>$companyId,
                'created_at'=>DateTimeUtils::parseStandardWLongDate(time()),
            ],
            false
        )->execute();
        return [true,''];
    }
}