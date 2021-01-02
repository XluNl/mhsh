<?php
/**
 * Created by PhpStorm.
 * User: hzg
 * Date: 2019/03/30/030
 * Time: 1:55
 */

namespace business\services;


use business\models\BusinessCommon;
use business\utils\ExceptionAssert;
use business\utils\exceptions\BusinessException;
use business\utils\StatusCode;
use common\models\Common;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\models\GoodsDetail;
use common\models\GoodsSku;
use common\models\GoodsSkuAlliance;
use common\services\GoodsDetailService;
use common\services\GoodsService;
use common\services\GoodsSoldChannelService;
use common\utils\ArrayUtils;
use common\utils\CopyUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use Yii;

class GoodsSkuAllianceService extends \common\services\GoodsSkuAllianceService
{
    /**
     * 单个查询
     * @param $id
     * @param $deliveryId
     * @param $companyId
     * @param bool $model
     * @return array|bool|\common\services\GoodsSkuAllianceService|mixed|null
     */
    public static function getGoodsInfo($id, $deliveryId, $companyId, $model=false){
        $model = parent::getModel($id,GoodsConstantEnum::OWNER_DELIVERY,$deliveryId,$companyId,$model);
        ExceptionAssert::assertNotNull($model,StatusCode::createExp(StatusCode::GOODS_SKU_ALLIANCE_NOT_EXIST));
        parent::showPrice($model);
        parent::showStatusText($model);
        return $model;
    }

    /**
     * 列表
     * @param $deliveryId
     * @param $companyId
     * @param $auditStatus
     * @param $pageNo
     * @param $pageSize
     * @return array
     */
    public static function getGoodsInfoList($deliveryId, $companyId, $auditStatus, $pageNo, $pageSize){
        $models = parent::getModels(GoodsConstantEnum::OWNER_DELIVERY,$deliveryId,$companyId,$auditStatus,$pageNo,$pageSize);
        CopyUtils::batchCopyAttr($models,'sku_img','sku_img_text');
        $models = GoodsDisplayDomainService::batchRenameImageUrl($models,'sku_img_text');
        CopyUtils::batchCopyAttr($models,'goods_img','goods_img_text');
        $models = GoodsDisplayDomainService::batchRenameImageUrl($models,'goods_img_text');
        parent::batchShowPrice($models);
        parent::batchShowStatusText($models);
        return $models;
    }

    /**
     * 提交审核
     * @param $id
     * @param $deliveryId
     * @param $companyId
     */
    public static function submitAudit($id, $deliveryId, $companyId){
        $count = GoodsSkuAlliance::updateAll(['audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_WAITING,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'goods_owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,'goods_owner_id'=>$deliveryId,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_EDIT]);
        ExceptionAssert::assertTrue($count>0,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"当前状态不支持发起审核"));
    }

    /**
     * 撤回
     * @param $id
     * @param $deliveryId
     * @param $companyId
     */
    public static function withdraw($id, $deliveryId, $companyId){
        $count = GoodsSkuAlliance::updateAll(['audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_EDIT,'updated_at'=>DateTimeUtils::parseStandardWLongDate(time())],['id'=>$id,'goods_owner_type'=>GoodsConstantEnum::OWNER_DELIVERY,'goods_owner_id'=>$deliveryId,'company_id'=>$companyId,'audit_status'=>GoodsSkuAlliance::AUDIT_STATUS_WAITING]);
        ExceptionAssert::assertTrue($count>0,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,"当前状态不支持撤回"));
    }

    /**
     * 发布
     * @param $id
     * @param $delivery
     * @param $companyId
     * @throws BusinessException
     */
    public static function publish($id, $delivery, $companyId){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //校验是否还能发布商品
            $model = self::getModel($id,GoodsConstantEnum::OWNER_DELIVERY,$delivery['id'],$companyId,false);
            ExceptionAssert::assertNotNull($model, StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,'商品审核条目不存在'));
            //list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateGoods($delivery,$model['goods_id']);
            //ExceptionAssert::assertTrue($checkError,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$checkErrorMsg));
            list($result,$error) = self::publishGoodsAndSkuB($id,$model['goods_owner_type'],$delivery['id'],$model,$companyId);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$error));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$e->getMessage()));
        }
    }





    public static function completeStock(&$model){
        if (empty($model)){
            return;
        }
        $model['sku_sold'] = 0;
        if (StringUtils::isNotBlank($model['sku_id'])){
            $sku = GoodsSkuService::getSkuInfo($model['sku_id'],$model['goods_owner_id'],$model['company_id'],GoodsConstantEnum::OWNER_DELIVERY);
            if (!empty($sku)){
                $model['sku_sold'] = $sku['sku_sold'];
                $model['sku_stock'] = $sku['sku_stock'];
            }
        }
    }

    /**
     *
     * @param $goodsSkuAllianceModel  GoodsSkuAlliance
     * @param $delivery
     * @param $companyId
     * @throws BusinessException
     */
    public static function saveAndPublish($goodsSkuAllianceModel, $delivery, $companyId){
        $transaction = Yii::$app->db->beginTransaction();
        try{
            ExceptionAssert::assertTrue($goodsSkuAllianceModel->save(),StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,BusinessCommon::getModelErrors($goodsSkuAllianceModel)));
            //校验是否还能发布商品
            $id = $goodsSkuAllianceModel->id;
            $model = self::getModel($id,GoodsConstantEnum::OWNER_DELIVERY,$delivery['id'],$companyId,false);
            ExceptionAssert::assertNotNull($model, StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,'商品审核条目不存在'));
            //list($checkError,$checkErrorMsg) = AllianceAuthService::checkCreateGoods($delivery,$model['goods_id']);
            //ExceptionAssert::assertTrue($checkError,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$checkErrorMsg));
            list($result,$error) = self::publishGoodsAndSkuB($id,$model['goods_owner_type'],$delivery['id'],$model,$companyId);
            ExceptionAssert::assertTrue($result,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_PUBLISH_ERROR,$error));
            $transaction->commit();
        }
        catch (BusinessException $e){
            $transaction->rollBack();
            throw $e;
        }
        catch (\Exception $e){
            $transaction->rollBack();
            Yii::error($e);
            ExceptionAssert::assertTrue(false,StatusCode::createExpWithParams(StatusCode::GOODS_SKU_ALLIANCE_MODIFY,$e->getMessage()));
        }
    }

    /**
     * 发布商品
     * @param $id
     * @param $goodsOwnerType
     * @param $goodsOwnerId
     * @param $model
     * @param $companyId
     * @return array
     */
    public static function publishGoodsAndSkuB($id,$goodsOwnerType,$goodsOwnerId,$model,$companyId){
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
            $goods->display_order = 0;
            $goods->supplier_id = 0;
            $goods->goods_sold_channel_type = Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY;
            $goods->goods_cart =  Goods::GOODS_CART_FALSE;
            $goods->goods_status =  GoodsConstantEnum::STATUS_UP;
        }
        $goods->sort_1 = $model['sort_1'];
        $goods->sort_2 = $model['sort_2'];
        $goods->goods_name = $model['goods_name'];
        $goods->goods_img = $model['goods_img'];
        $goods->goods_describe = $model['sku_describe'];
        $goods->goods_type =  $model['goods_type'];

        if (!$goods->save()){
            return [false,BusinessCommon::getModelErrors($goods)];
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
            $goodsSku->sku_standard = GoodsSku::SKU_STANDARD_TRUE;
            $goodsSku->sku_unit_factor = 1;
            $goodsSku->display_order = 0;
            $goodsSku->agent_rate= 0;
            $goodsSku->sku_status = GoodsConstantEnum::STATUS_UP;
        }
        $goodsSku->sale_price = $model['sale_price'];
        $goodsSku->one_level_rate = $model['one_level_rate'];
        $goodsSku->two_level_rate = $model['two_level_rate'];
        $goodsSku->company_rate= $model['company_rate'];
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
            return [false,BusinessCommon::getModelErrors($goodsSku)];
        }

        if (StringUtils::isBlank($model['goods_id'])){
            list($res,$error) = GoodsSoldChannelService::addGoodsSoldChannel(Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY,[$goodsOwnerId],$goods['id'],$companyId);
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
}