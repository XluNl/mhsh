<?php


namespace common\services;

use alliance\models\AllianceCommon;
use backend\services\GoodsDetailService;
use backend\services\GoodsService;
use common\models\AllianceChannel;
use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsDetail;
use common\models\GoodsSku;
use common\models\GoodsSkuAlliance;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;
use yii\db\Query;

class GoodsSkuAllianceService
{
    public static function getModel($id,$goodsOwnerType, $goodsOwnerId=null, $companyId=null, $model=false){
        $conditions = ['id'=>$id];
        if (StringUtils::isNotBlank($goodsOwnerType)){
            $conditions['goods_owner_type']= $goodsOwnerType;
        }
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['goods_owner_id']= $goodsOwnerId;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id']= $companyId;
        }
        if ($model){
            $goodsSkuAlliance = GoodsSkuAlliance::findOne($conditions);
        }
        else{
            $goodsSkuAlliance = (new Query())->from(GoodsSkuAlliance::tableName())->where($conditions)->one();
            $goodsSkuAlliance = $goodsSkuAlliance===false?null:$goodsSkuAlliance;
        }
        if ($goodsSkuAlliance!==null){
            $goodsSkuAlliance = self::renamePic($goodsSkuAlliance);
        }
        return $goodsSkuAlliance;
    }

    public static function getModels($goodsOwnerType,$goodsOwnerId=null,$companyId=null,$auditStatus=null,$pageNo=1,$pageSize=20){
        $conditions = [];
        if (StringUtils::isNotBlank($goodsOwnerType)){
            $conditions['goods_owner_type']= $goodsOwnerType;
        }
        if (StringUtils::isNotBlank($goodsOwnerId)){
            $conditions['goods_owner_id']= $goodsOwnerId;
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions['company_id']= $companyId;
        }
        if (!empty($auditStatus)){
            $conditions['audit_status']= $auditStatus;
        }
        $goodsSkuAllianceQuery = (new Query())->from(GoodsSkuAlliance::tableName())->where($conditions)->orderBy('updated_at desc');
        if ($pageNo!=null&&$pageSize!=null){
            $goodsSkuAllianceQuery = $goodsSkuAllianceQuery->offset($pageSize*($pageNo-1))->limit($pageSize);
        }
        $goodsSkuAllianceModels = $goodsSkuAllianceQuery->all();
        $goodsSkuAllianceModels = self::batchRenamePic($goodsSkuAllianceModels);
        return $goodsSkuAllianceModels;
    }

    /**
     * @param array $goodsSkuAllianceModels
     * @return array
     */
    public static function batchRenamePic(array $goodsSkuAllianceModels)
    {
        if (!empty($goodsSkuAllianceModels)) {
            foreach ($goodsSkuAllianceModels as $k => $v) {
                $goodsSkuAllianceModels[$k] = self::renamePic($v);
            }
        }
        return $goodsSkuAllianceModels;
    }

    /**
     * @param $goodsSkuAlliance GoodsSkuAlliance
     * @return mixed
     */
    public static function renamePic($goodsSkuAlliance)
    {
        $goodsSkuAlliance = GoodsDisplayDomainService::renameImageUrl($goodsSkuAlliance, 'goods_img','goods_img_text');
        $goodsSkuAlliance = GoodsDisplayDomainService::renameImageUrl($goodsSkuAlliance, 'sku_img','sku_img_text');
        $goodsSkuAlliance = GoodsDisplayDomainService::renameImageUrl($goodsSkuAlliance, 'goods_detail','goods_detail_text');
        return $goodsSkuAlliance;
    }

    /**
     * 发布商品
     * @param $id
     * @param $goodsOwnerType
     * @param $goodsOwnerId
     * @param $model GoodsSkuAlliance
     * @param $companyId
     * @return array
     */
    public static function publishAllianceGoodsAndSku($id, $goodsOwnerType, $goodsOwnerId, $model, $companyId){
        if ($model['audit_status']!=GoodsSkuAlliance::AUDIT_STATUS_ACCEPT){
            return [false,'商品审核条目处于'.ArrayUtils::getArrayValue($model['audit_status'],GoodsSkuAlliance::$auditStatusArr).'状态'];
        }
        if (StringUtils::isNotBlank($model['goods_id'])) {
            $goods = GoodsService::getActiveOwnerGoods($model['goods_id'], $companyId,$goodsOwnerType, $goodsOwnerId, true);
            if ($goods===null){
                return [false,'原商品不存在'];
            }
            $displaySchedules = GoodsScheduleService::getDisplayUpAll(
                $goodsOwnerType,
                $companyId,
                null,
                null,
                null,
                null,null,$model['goods_id']
            );
            if (!empty($displaySchedules)){
                return [false,'已有正在上架中的商品，暂不支持修改'];
            }
        }
        else{
            $goods = new Goods();
            $goods->loadDefaultValues();
            $goods->company_id = $companyId;
            $goods->goods_owner = $model['goods_owner_type'];
            $goods->goods_owner_id = $goodsOwnerId;
            $goods->sort_1 = $model['sort_1'];
            $goods->sort_2 = $model['sort_2'];
            $goods->display_order = 0;
            $goods->supplier_id = 0;
            $goods->goods_sold_channel_type = Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY;
            $goods->goods_cart =  Goods::GOODS_CART_FALSE;
            $goods->goods_status =  GoodsConstantEnum::STATUS_DOWN;
        }
        $goods->goods_name = $model['goods_name'];
        $goods->goods_img = $model['goods_img'];
        $goods->goods_describe = $model['sku_describe'];
        $goods->goods_type =  $model['goods_type'];

        if (!$goods->save()){
            return [false,'商品保存失败。'.Common::getModelErrors($goods)];
        }

        $goodsDetail = GoodsDetailService::getById($goods['id'],$companyId,true);
        if ($goodsDetail===null){
            $goodsDetail = new GoodsDetail();
            $goodsDetail->company_id = $companyId;
            $goodsDetail->goods_id = $goods['id'];
        }
        if (StringUtils::isNotBlank($model['goods_detail'])){
            $images = explode(",",$model['goods_detail']);
            $details = "";
            foreach ($images as $image){
                $details = $details."<p><img src=\"".Common::generateAbsoluteUrl($image)."\"></p>";
            }
            $goodsDetail->goods_detail = $details;
        }
        else{
            $goodsDetail->goods_detail = "";
        }
        if (!$goodsDetail->save()){
            return [false,'商品详情页保存失败'];
        }

        if (StringUtils::isNotBlank($model['goods_id'])&&StringUtils::isNotBlank($model['sku_id'])) {
            $goodsSku = GoodsSkuService::getActiveGoodsSku($model['sku_id'],$model['goods_id'], $companyId, true);
            if ($goodsSku===null){
                return [false,'原商品属性不存在'];
            }
        }
        else{
            $goodsSku = new GoodsSku();
            $goodsSku->company_id = $companyId;
            $goodsSku->goods_id = $goods['id'];
            $goodsSku->sku_sold = 0;
            $goodsSku->share_rate_1 = 0;
            $goodsSku->share_rate_2 = 0;
            $goodsSku->delivery_rate = 0;
            $goodsSku->features = '';
            $goodsSku->one_level_rate = 0;
            $goodsSku->two_level_rate = 0;
            $goodsSku->agent_rate= 0;
            $goodsSku->company_rate= 0;
            $goodsSku->sku_standard = GoodsSku::SKU_STANDARD_TRUE;
            $goodsSku->sku_unit_factor = 1;
            $goodsSku->display_order = 0;
            $goodsSku->sku_status = GoodsConstantEnum::STATUS_DOWN;

        }
        $goodsSku->sku_name = $model['sku_name'];
        $goodsSku->sku_img = $model['sku_img'];
        $goodsSku->sku_unit = $model['sku_unit'];
        $goodsSku->sku_describe = $model['sku_describe'];

        $goodsSku->sku_stock =$model['sku_stock'];
        $goodsSku->purchase_price = $model['purchase_price'];
        $goodsSku->reference_price = $model['reference_price'];
        $goodsSku->production_date = $model['production_date'];
        $goodsSku->expired_date = $model['expired_date'];
        $goodsSku->start_sale_num = $model['start_sale_num'];
        if (!$goodsSku->save()){
            return [false,'商品属性保存失败。'.AllianceCommon::getModelErrors($goodsSku)];
        }

        //todo 不再保存更新原有的商品渠道
        /*$channel = AllianceChannelService::getChannelByAlliance($goodsOwnerId,$companyId);
        if (!empty($channel)){
            $deliveryIds = explode(',',$channel['channel_ids']);
            $deliveryIds=array_filter($deliveryIds);
            list($res,$error) = GoodsSoldChannelService::addGoodsSoldChannel($channel['channel_type'],$deliveryIds,$goods['id'],$companyId);
            if (!$res){
                return [false,$error];
            }
        }*/
        if ($model->goods_owner_type == GoodsConstantEnum::OWNER_HA){
            list($res,$error) = GoodsSoldChannelService::addGoodsSoldChannel(Goods::GOODS_SOLD_CHANNEL_TYPE_AGENT,[],$goods['id'],$companyId);
            if (!$res){
                return [false,$error];
            }
        }




        $count = GoodsSkuAlliance::updateAll([
            'goods_id'=>$goods['id'],
            'sku_id'=>$goodsSku['id'],
            'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_PUBLISH,
            'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())
        ],['id'=>$id,'goods_owner_type'=>$goodsOwnerType,'goods_owner_id'=>$goodsOwnerId,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_ACCEPT]);
        if ($count<1){
            return [false,'商品审核保存失败'];
        }
        return [true,''];
    }


    public static function batchShowPrice(&$models){
        if (empty($models)){
            return;
        }
        foreach ($models as $k=>$v){
            self::showPrice($v);
            $models[$k]= $v;
        }
    }

    public static function batchShowStatusText(&$models){
        if (empty($models)){
            return;
        }
        foreach ($models as $k=>$v){
            self::showStatusText($v);
            $models[$k]= $v;
        }
    }


    public static function showPrice(&$model){
        if (!empty($model)){
            $model['purchase_price'] = Common::showAmount($model['purchase_price']);
            $model['reference_price'] = Common::showAmount($model['reference_price']);
        }
    }

    public static function showStatusText(&$model){
        if (!empty($model)){
            $model['audit_status_text'] = ArrayUtils::getArrayValue($model['audit_status'],GoodsSkuAlliance::$auditStatusArr);
        }
    }
}