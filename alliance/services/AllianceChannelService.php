<?php


namespace alliance\services;



use alliance\utils\ExceptionAssert;
use alliance\utils\exceptions\BusinessException;
use alliance\utils\StatusCode;
use common\models\AllianceChannel;
use common\models\Goods;
use common\services\DeliveryService;
use common\utils\ArrayUtils;
use Yii;

class AllianceChannelService  extends \common\services\AllianceChannelService
{
    /**
     * 获取投放详情
     * @param $allianceId
     * @param $companyId
     * @return array|bool|\common\models\AllianceChannel|\yii\db\ActiveRecord|null
     */
    public static function getChannel($allianceId,$companyId){
        $channelModel = parent::getChannelByAlliance($allianceId,$companyId);
        if (!empty($channelModel)){
            $channelModel['delivery_list'] = [];
            if ($channelModel['channel_type']==Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY){
                $deliveryIds = explode(',',$channelModel['channel_ids']);
                $deliveryIds=array_filter($deliveryIds);
                if (!empty($deliveryIds)){
                    $deliveryModels = DeliveryService::getAllActiveModel($deliveryIds,$companyId);
                    $channelModel['delivery_list'] = ArrayUtils::subArray($deliveryModels,'id','nickname');
                }
            }
        }
        return $channelModel;
    }

    /**
     * 非空
     * @param $id
     * @param $allianceId
     * @param $companyId
     * @return array|bool|\common\models\AllianceChannel|\yii\db\ActiveRecord|null
     */
    public static function requiredChannel($id,$allianceId,$companyId){
        $model = self::getChannelByAlliance($allianceId,$companyId,$id,true);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::ALLIANCE_CHANNEL_NOT_EXIST));
        return $model;
    }

    /**
     * @param $model AllianceChannel
     * @return mixed
     */
    public static function applyChannel(&$model){
        ExceptionAssert::assertTrue(key_exists($model->channel_type,Goods::$goodsSoldChannelTypeArr),StatusCode::createExpWithParams(StatusCode::ALLIANCE_CHANNEL_ERROR,"类型错误"));
        $deliveryIds = explode(',',$model['channel_ids']);
        $deliveryIds=array_filter($deliveryIds);
        if ($model->channel_type==Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY){
            if (!empty($deliveryIds)){
                $existDelivery = DeliveryService::getAllActiveModel($deliveryIds,$model['company_id']);
                ExceptionAssert::assertTrue(count($existDelivery)==count($deliveryIds),StatusCode::createExpWithParams(StatusCode::ALLIANCE_CHANNEL_ERROR,"部分配送点已移除，请刷新后重试"));
            }
        }
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //todo 不再保存更新原有的商品渠道
           /* $goodsModels = GoodsService::getActiveOwnerGoodsList($model['company_id'],$model['alliance_id']);
            if (!empty($goodsModels)){
                foreach ($goodsModels as $goodsModel){
                    list($result,$error) = GoodsSoldChannelService::addGoodsSoldChannel($model->channel_type,$deliveryIds,$goodsModel['id'],$model['company_id']);
                    ExceptionAssert::assertTrue($result,BusinessException::create($error));
                }
            }*/
            ExceptionAssert::assertTrue($model->save(),BusinessException::create('保存失败'));
            $transaction->commit();
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::ALLIANCE_CHANNEL_ERROR,$e->getMessage()));
        }
        return;
    }

}